# Auditoria Técnica — BasileaVendor

Revisão profunda de código (backend Laravel 11 + frontend Next.js). Cada item traz arquivo:linha, o problema concreto e a correção sugerida. Ordenado por severidade.

---

## 🔴 CRÍTICO

### C1. CRUD de vendedores na API sem trava de papel (broken access control / escalonamento)
**Onde:** `backend/app/Http/Controllers/Api/VendedorController.php` (`store`, `update`, `destroy`) + rotas `backend/routes/api.php:116-120` (só `auth:sanctum`, sem middleware de papel).

Qualquer usuário autenticado — inclusive um `vendedor` comum — pode:
- **Criar novos usuários e definir o papel** (`is_gestor` → grava perfil `'Gestor'`). Escalonamento de privilégio.
- **Alterar os próprios percentuais de comissão** (`comissao_inicial`, `comissao_recorrencia`, `comissao_gestor_*`) via `update`. Fraude financeira direta.
- **Listar todos os vendedores** e seus percentuais (`index` sem filtro).

**Correção:** aplicar middleware `master`/`gestor` nessas rotas (ou uma Policy/Gate). Campos de percentual só devem ser aceitos de quem é `master`.

### C2. Inconsistência de maiúsculas no campo `perfil`
**Onde:** `VendedorController.php` grava `'Vendedor'` / `'Gestor'` (capitalizado). Já os middleware e checagens comparam com minúsculo: `CheckMaster.php:20` (`perfil !== 'master'`), `CheckGestor.php:14`, `VendasController.php` (`in_array($user->perfil, ['gestor','admin','master'])`).

Usuário criado pela API fica com um papel que **nenhuma checagem reconhece** — um "Gestor" não passa nas telas de gestor, e a divergência facilita brechas onde uma verificação falha de forma inesperada.

**Correção:** padronizar em um enum único (minúsculo), migrar os dados já gravados e centralizar a constante.

### C3. Webhook do Asaas é *fail-open* (aceita qualquer requisição sem token)
**Onde:** `backend/app/Http/Controllers/AsaasWebhookController.php:18-25`.

```php
$webhookToken = Setting::get('asaas_webhook_token', ... env('ASAAS_WEBHOOK_TOKEN', ''));
if ($webhookToken) {           // <- se estiver vazio, NÃO valida nada
    if ($headerToken !== $webhookToken) return 403;
}
```

Se o Setting e a env estiverem vazios (default `''`), o bloco de validação é pulado e **qualquer um pode postar eventos de pagamento falsos**, que entram na fila e geram comissões/estados. Contraste: `CheckoutWebhookController.php:27-29` é *fail-closed* (retorna 500 sem secret).

**Correção:** fail-closed — sem token configurado, rejeitar (500/403). Idealmente validar HMAC sobre o corpo bruto.

### C4. Comissão sem idempotência a nível de banco → risco de comissão duplicada
**Onde:** `backend/app/Services/Commission/CommissionService.php:40` (`exists()`) e `:78` (`count()===0`); migration `database/migrations/2024_03_25_300000_create_comissoes_table.php` **não tem índice único em `pagamento_id`** (confirmado).

A trava de idempotência é apenas aplicacional. Existem **três gatilhos** chamando `gerarParaPagamento`: webhook (`PagamentoService.php:282`), cron noturno (`ProcessarComissoesCommand.php:63`) e a sincronização. Se dois rodarem para o mesmo pagamento quase ao mesmo tempo, ambos passam pelo `exists()` e **inserem comissão em dobro**. O mesmo vale para `primeira` (`count()===0`): uma corrida faz dois pagamentos contarem como "inicial" (percentual maior).

**Correção:** índice único (ex.: `pagamento_id` + `gerente_id`) + envolver a checagem e o insert numa transação com `lockForUpdate` na linha do pagamento.

### C5. Token de sessão exposto a XSS (localStorage + cookie não-HttpOnly)
**Onde:** `frontend/src/context/AuthContext.tsx:57-58`, `frontend/src/lib/api.ts:25`.

```js
localStorage.setItem("auth_token", res.token);
document.cookie = `auth_token=${res.token}; path=/; max-age=86400`; // sem HttpOnly/Secure/SameSite
```

O token Sanctum fica acessível a qualquer JavaScript; qualquer XSS o exfiltra. O próprio comentário no código reconhece a alternativa correta ("ou usa cookies via backend HttpOnly") que não foi feita. Agravado pela CSP com `'unsafe-inline'` (ver A4).

**Correção:** emitir o token em cookie `HttpOnly; Secure; SameSite=Lax` pelo backend; não guardar em localStorage.

---

## 🟠 ALTO

### A1. 2FA não é aplicado no fluxo da API
**Onde:** `backend/app/Http/Controllers/Api/AuthController.php:13-45`.

O fluxo web tem middleware `2fa`, mas o login Sanctum emite o token direto após conferir a senha, **sem etapa de 2FA**. Quem usa o app Next.js pula o segundo fator inteiro. Se 2FA é requisito, isto é um bypass completo.

### A2. `bootstrap/app.php` gera APP_KEY em runtime e grava no `.env`
**Onde:** `backend/bootstrap/app.php:8-38`.

Gera uma `APP_KEY` nova se não achar uma e escreve no arquivo `.env`. Problemas em produção:
- **Múltiplos workers/containers** podem gerar chaves diferentes → sessões, tokens e `two_factor_secret` criptografados ficam ilegíveis entre instâncias.
- **Filesystem read-only** (comum em PaaS) faz `file_put_contents` falhar no boot.
- Se o `.env` for recriado, a chave muda e **todos os dados criptografados tornam-se irrecuperáveis** (ex.: segredos de 2FA).

**Correção:** fixar `APP_KEY` como variável de ambiente no deploy e remover a auto-geração.

### A3. HMAC do CheckoutWebhook sobre JSON re-serializado
**Onde:** `backend/app/Http/Controllers/Integration/CheckoutWebhookController.php:38`.

```php
$expected = hash_hmac('sha256', json_encode($request->all(), ...), $secret);
```

Assina o JSON **re-serializado** em vez do corpo bruto. Qualquer diferença de ordem de chaves/escape entre o que o remetente assinou e o `json_encode` do Laravel causa rejeição falsa (ou inconsistência de validação).

**Correção:** usar `$request->getContent()` (raw body) no HMAC.

### A4. CSP fraca anula boa parte da proteção
**Onde:** `backend/app/Http/Middleware/SecurityHeaders.php`.

`script-src` inclui `'unsafe-inline'` e `connect-src` é `'self' https: http: *` — ou seja, permite scripts inline e conexão a qualquer origem. Combinado com o token em localStorage (C5), amplia muito a superfície de XSS.

**Correção:** remover `'unsafe-inline'` (usar nonce/hash) e restringir `connect-src` aos domínios reais.

### A5. Resolução de percentual com `?:` trata 0% como "vazio"
**Onde:** `backend/app/Services/Commission/CommissionService.php:55-56`.

```php
$percIni = (float)($vendedor->comissao_inicial ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0);
```

Como `0` é *falsy*, um percentual **legitimamente 0%** "cai" para o próximo fallback → paga comissão quando deveria ser zero.

**Correção:** usar `??` ou checagem explícita de `null`.

---

## 🟡 MÉDIO

### M1. Catch genérico engole erros do webhook Asaas e responde 200
**Onde:** `backend/app/Http/Controllers/AsaasWebhookController.php:49-54`. O `catch (QueryException)` trata **qualquer** erro de banco como duplicata e retorna 200. Se a inserção falhar por outro motivo (coluna, banco fora), o evento é perdido — o Asaas não reenvia após um 200. Distinguir violação de unique (SQLSTATE 23000) dos demais e retornar 500 nos outros casos.

### M2. Autorização espalhada e ad-hoc nos controllers
`VendasController` checa `perfil` método a método; `ClientesAsaasController` (`index`/`show`/`update`) não checa papel algum. Fácil esquecer numa rota nova = vazamento. Centralizar em Policies/Gates.

### M3. Sem escopo de tenant nos controllers da API
Existe o modelo `Tenant`, mas nenhum controller em `app/Http/Controllers/Api/` referencia `tenant_id`. Se o produto é multi-tenant, gestores/master veem dados de todos os tenants. Confirmar a intenção; se multi-tenant, aplicar *global scope* por tenant.

### M4. Bug latente no cliente HTTP do frontend
**Onde:** `frontend/src/lib/api.ts:33-41`. O objeto do fetch é `{ headers: {…Authorization…}, ...options }`. Se o chamador passar `options.headers`, o spread de `options` **sobrescreve o header `Authorization`** já montado → chamada perde a autenticação. Mesclar `options.headers` explicitamente e remover `headers` de `options` antes do spread.

### M5. `login` apaga todos os tokens anteriores
**Onde:** `AuthController.php:28`. Login em um dispositivo desloga os demais. Pode ser intencional, mas quebra uso multi-dispositivo — revisar.

### M6. Três árvores de rota/tela duplicadas (deriva arquitetural)
`gestao-comercial` vs `gestor`, `financeiro` vs `gestao-financeira`, `cadastros/centros-de-custo` vs `contabilidade/centros-de-custo`; além de `web.php` (312 rotas, sessão) + `api.php` (140 rotas, Sanctum). Manutenção duplicada e risco de a regra divergir entre as cópias. Definir a fonte de verdade e remover o legado.

---

## ⚪ BAIXO / higiene

- **B1.** Comentários enganosos: `api.ts` diz "JWT" onde é token Sanctum; a doc do webhook Asaas cita "unique no banco" (válido para `asaas_events`, mas **não** para `comissoes`).
- **B2.** Rotas-alias para erro de digitação (`/webhook/assas`, `api/webhook/assas` no `bootstrap/app.php`). Melhor corrigir a URL na configuração do Asaas do que manter rotas erradas.
- **B3.** Cobertura de teste ainda baixa para o volume (81 controllers), embora já existam `AsaasWebhookTest`, `CommissionCalculatorTest`, `DashboardApiTest`. Priorizar testes de concorrência do motor de comissão (C4) e de autorização (C1/C2).

---

## Pontos positivos (o que já está bom)

- `CommissionCalculator` é uma função pura, bem documentada e testável — ótima separação de regra de negócio.
- Idempotência de eventos do Asaas via unique em `asaas_events.asaas_event_id` funciona.
- `CheckoutWebhookController` valida assinatura com `hash_equals` e é *fail-closed*.
- `LimparBancoController` confere `master` internamente antes do `TRUNCATE`.
- `SecurityHeaders` cobre HSTS, X-Frame-Options, nosniff, remove headers de versão.
- Higiene do repo já corrigida (node_modules e scripts de debug removidos).

---

### Ordem sugerida de correção
1. C1 + C2 (acesso/perfil na API) — maior risco imediato.
2. C4 (índice único + lock de comissão) — impacto financeiro.
3. C3 (webhook fail-closed) e C5 (token HttpOnly).
4. A2 (APP_KEY) antes do próximo deploy.
5. Demais itens ALTO/MÉDIO conforme sprint.
