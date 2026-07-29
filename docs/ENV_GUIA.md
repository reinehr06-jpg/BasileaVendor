# Guia de Variáveis de Ambiente — BasileaVendor

Sistema em 3 partes que precisam de env: **backend (Laravel)**, **frontend (Next.js)** e os
**serviços de apoio** (PostgreSQL, Redis). Abaixo estão os blocos prontos para colar no
EasyPanel (aba *Environment* de cada serviço) e, no fim, a explicação de onde cada coisa
sai no código e por quê.

> Convenção: `<PREENCHER>` = você substitui pelo valor real. Nunca comite valores reais no Git.

---

## 1. BACKEND (serviço `backend`)

```env
# --- Aplicação ---
APP_NAME=BasileiaVendas
APP_ENV=production
APP_KEY=base64:tvATlb+mMr6flErfod80Vnv3oDqVYaPVv5xeVD2wX14=
APP_DEBUG=false
APP_URL=https://srvendor.basileia.global

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

# --- Logs ---
LOG_CHANNEL=errorlog
LOG_LEVEL=error

# --- Banco de dados (PostgreSQL) ---
DB_CONNECTION=pgsql
DB_HOST=<PREENCHER: host interno do Postgres no EasyPanel>
DB_PORT=5432
DB_DATABASE=<PREENCHER>
DB_USERNAME=<PREENCHER>
DB_PASSWORD=<PREENCHER>

# --- Sessão / Cache / Fila ---
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=redis
QUEUE_CONNECTION=database

# --- Redis ---
REDIS_HOST=<PREENCHER: host interno do Redis no EasyPanel>
REDIS_PORT=6379
REDIS_PASSWORD=<PREENCHER ou deixe vazio se o Redis não tiver senha>

# --- Segurança de cookie/sessão (login) ---
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.basileia.global
SANCTUM_STATEFUL_DOMAINS=vendor.basileia.global,srvendor.basileia.global
CORS_ALLOWED_ORIGINS=https://vendor.basileia.global

# --- Asaas (pagamentos) — OBRIGATÓRIO para vendas ---
ASAAS_API_KEY=<PREENCHER: chave do painel Asaas>
ASAAS_AMBIENTE=production
ASAAS_WEBHOOK_TOKEN=<PREENCHER: token que você define e cadastra no webhook Asaas>

# --- E-mail (SMTP) ---
MAIL_MAILER=smtp
MAIL_HOST=<PREENCHER>
MAIL_PORT=587
MAIL_USERNAME=<PREENCHER>
MAIL_PASSWORD=<PREENCHER>
MAIL_FROM_ADDRESS=noreply@basileia.global
MAIL_FROM_NAME="Basiléia Vendas"

# --- IA (opcional; só se usar sugestões/chat com IA) ---
IA_PROVIDER=openai
OPENAI_API_KEY=<PREENCHER ou deixe vazio>
OPENAI_MODEL=gpt-3.5-turbo

# --- Webhook de chat/WhatsApp (opcional) ---
CHAT_WEBHOOK_TOKEN=<PREENCHER se usar WhatsApp/Meta; vazio = modo permissivo>

# --- Monitoramento de erros (opcional) ---
SENTRY_LARAVEL_DSN=<PREENCHER ou deixe vazio>
```

---

## 2. FRONTEND (serviço `front_end`)

```env
# Origem do backend para o proxy /api (rede interna do EasyPanel).
BACKEND_ORIGIN=http://backend:80

# Base da API usada pelo cliente. /api porque o Next faz proxy pro backend.
NEXT_PUBLIC_API_URL=/api
```

> Observação: `SENTRY_AUTH_TOKEN` só é usado no BUILD (CI), não em runtime — não precisa aqui.
> `NEXT_PUBLIC_VAPID_PUBLIC_KEY` só é necessária se você ligar push notifications.

---

## 3. SERVIÇOS DE APOIO

### PostgreSQL (serviço `postgres`)
```env
POSTGRES_DB=<mesmo valor de DB_DATABASE>
POSTGRES_USER=<mesmo valor de DB_USERNAME>
POSTGRES_PASSWORD=<mesmo valor de DB_PASSWORD>
```

### Redis (serviço `redis`)
```env
# Se quiser proteger com senha (recomendado), configure aqui e replique em REDIS_PASSWORD no backend.
REDIS_PASSWORD=<PREENCHER opcional>
```

---

## 4. Onde cada coisa aparece no código e por quê

### Aplicação
- `APP_KEY` — usada por `config/app.php` (`'key' => env('APP_KEY')`). É a chave de
  criptografia do Laravel; assina cookies, sessões, tokens 2FA. **Sem ela o login não
  funciona** (foi o erro `Your app key is not set!`). Gere com `php artisan key:generate`
  ou `echo "base64:$(openssl rand -base64 32)"`. Nunca troque em produção — invalida sessões/2FA.
- `APP_ENV` / `APP_DEBUG` — `config/app.php`. Em produção, `env=production` e `debug=false`
  (com debug true, erros vazam stack trace e dados sensíveis).
- `APP_URL` — usada para gerar links absolutos (e-mails, PDFs, callbacks).

### Banco de dados
- `DB_*` — `config/database.php`, bloco `'pgsql'`. O default lá ainda é `sqlite`
  (`'default' => env('DB_CONNECTION', 'sqlite')`), por isso é obrigatório setar
  `DB_CONNECTION=pgsql`. SQLite não aguenta a concorrência de fila + scheduler + web.

### Sessão, cache e fila
- `SESSION_DRIVER=database` — `config/session.php`. Guarda sessão no Postgres (persiste
  entre reinícios do container).
- `CACHE_STORE=redis` / `REDIS_*` — `config/cache.php` e `config/database.php` (bloco redis).
  Cache em Redis é mais rápido e compartilhado entre processos.
- `QUEUE_CONNECTION=database` — `config/queue.php`. Os Jobs (e-mails, importação Asaas)
  vão pra tabela `jobs` e o worker (supervisor) processa.

### Segurança do login (o mais importante pro seu caso)
- `SESSION_SECURE_COOKIE=true` — `config/session.php` linha `'secure' => env(...)`. E é lido
  também no `AuthController@login`, que grava o cookie HttpOnly `auth_token`. `true` faz o
  cookie só trafegar por HTTPS. Em produção (HTTPS) tem que ser `true`.
- `SESSION_DOMAIN=.basileia.global` — `config/session.php`. O ponto na frente faz o cookie
  valer para todos os subdomínios (`vendor.` e `srvendor.`).
- `SANCTUM_STATEFUL_DOMAINS` — `config/sanctum.php`. Lista de domínios tratados como
  "confiáveis" para autenticação por cookie.
- `CORS_ALLOWED_ORIGINS` — `config/cors.php`. Só entra em jogo se algo chamar o backend
  direto (fora do proxy do Next). Como o Next faz proxy de `/api`, no fluxo normal é
  same-origin e o CORS nem dispara.

### Integrações de negócio
- `ASAAS_*` — `config/services.php` (bloco `asaas`) e lidas em runtime via `Setting::get`
  no `AsaasWebhookController`/serviços. `ASAAS_WEBHOOK_TOKEN` é conferido a cada webhook de
  pagamento (`asaas-access-token`), por isso é crítico para não aceitar webhook falso.
- `MAIL_*` — `config/mail.php`. Envio de e-mails (notificações, recuperação de senha).
- `OPENAI_API_KEY` / `IA_PROVIDER` — `config/services.php` (bloco openai/ia) e
  `App\Services\AI\Providers\OpenAIProvider`. Só necessário se usar recursos de IA.
- `CHAT_WEBHOOK_TOKEN` — `ChatWebhookController`. Protege os webhooks de WhatsApp/Meta.
- `SENTRY_LARAVEL_DSN` — `config/sentry.php`. Envia erros do backend pro Sentry.

### Frontend
- `BACKEND_ORIGIN` — `next.config.ts` (rewrites). Diz pro Next para onde encaminhar `/api`
  e `/storage`. Na rede interna do EasyPanel o backend responde como `backend` na porta 80.
- `NEXT_PUBLIC_API_URL` — variáveis `NEXT_PUBLIC_*` são **embutidas no bundle em tempo de
  build**. `/api` porque o cliente chama o próprio domínio, que o Next proxia pro backend.

### Como descobrir o que o sistema usa (para o futuro)
No backend, tudo que o app consome vem de `env(...)` dentro de `backend/config/*.php`.
Para listar: procure por `env('` nesses arquivos. No frontend, procure por
`process.env.` em `frontend/src` e `frontend/next.config.ts`. Se uma variável aparece
no código mas não está no ambiente, o Laravel emite aquele aviso
`references the X environment variable, but it is not set` — que é inofensivo para
integrações opcionais (AWS, Slack, Postmark…) que você não usa.
