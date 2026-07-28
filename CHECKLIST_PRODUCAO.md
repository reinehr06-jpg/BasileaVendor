# ✅ Checklist de Prontidão para Produção — BasileaVendor

> Marque cada item `[x]` **somente** quando o critério de conclusão for atendido.
> Fases 0–8 são **caminho crítico / bloqueadores**. Fases 9–13 blindam produção.
>
> Legenda de status: `[ ]` pendente · `[~]` em andamento · `[x]` concluído

---

## Stack 0 — Congelar e versionar

- [ ] **0.1** Commitar todo o trabalho `untracked`/`modified` (AuditLog, HealthController, ProductionSeeder, notifications, páginas de termos/privacidade/configurações) em commits pequenos e descritivos.
  - ✅ Concluir quando: `git status` estiver limpo (sem arquivos modificados ou untracked relevantes).
- [ ] **0.2** Rodar a suíte de testes do backend.
  - ✅ Concluir quando: `php artisan test` passar 100% localmente.
- [ ] **0.3** Rodar o build do frontend.
  - ✅ Concluir quando: `npm run build` concluir sem erros nem warnings de tipo.
- [ ] **0.4** Confirmar o pipeline de CI.
  - ✅ Concluir quando: o workflow `.github/workflows/ci.yml` estiver verde no commit da 0.1.

---

## Stack 1 — Remover mock e dados chumbados

- [ ] **1.1** Corrigir valor chumbado na nova venda.
  - ✅ Concluir quando: `frontend/src/app/vendas/nova/page.tsx:62` não tiver mais `valor: 1500` e o valor vier da API.
- [ ] **1.2** Ligar tela de despesas a dados reais.
  - ✅ Concluir quando: `financeiro/despesas` (e `gestao-financeira/despesas`) não tiverem mais arrays `MOCK` e os gráficos consumirem a API.
- [ ] **1.3** Ligar tela de transferências a dados reais.
  - ✅ Concluir quando: `financeiro/transferencias` e `gestao-financeira/transferencias` não usarem "Mock Data".
- [ ] **1.4** Ligar tela de importação (OFX) a dados reais.
  - ✅ Concluir quando: `importacao/page.tsx` não usar `mockOfx` nem `new File([], 'mock.ofx')`.
- [ ] **1.5** Resolver "qtd originada" das comissões.
  - ✅ Concluir quando: as telas de comissões não mostrarem mais o `1` fixo (`{/* TODO: Qtd originada */}`) e sim o valor da métrica real.
- [ ] **1.6** Consolidar telas duplicadas.
  - ✅ Concluir quando: existir uma única árvore (`financeiro/*` OU `gestao-financeira/*`), sem duplicação de páginas.

---

## Stack 2 — Higiene de segurança de código

- [ ] **2.1** Remover log do corpo da requisição.
  - ✅ Concluir quando: `frontend/src/lib/api.ts:27` não tiver mais `console.log("Body:", ...)`.
- [ ] **2.2** Remover/condicionar logs de service worker.
  - ✅ Concluir quando: os `console.log` de `layout.tsx` não rodarem em produção.
- [ ] **2.3** Parar de logar o token do webhook Asaas.
  - ✅ Concluir quando: `AsaasWebhookController` logar apenas IP e "token inválido", sem o valor do token.
- [ ] **2.4** Reforçar política de senha.
  - ✅ Concluir quando: `auth.schema.ts` exigir mínimo 8+ caracteres e o comentário do mock `123456` for removido.

---

## Stack 3 — Blindar infraestrutura Docker

- [ ] **3.1** Fechar Redis para a rede externa.
  - ✅ Concluir quando: `docker-compose.yml` não publicar `6379:6379` e `REDIS_PASSWORD` tiver senha real.
- [ ] **3.2** Proteger Grafana.
  - ✅ Concluir quando: Grafana não subir com `admin/admin` default (senha obrigatória via `:?`) e não ficar exposto publicamente.
- [ ] **3.3** Fechar Prometheus.
  - ✅ Concluir quando: a porta `9090` não estiver acessível externamente (só rede interna/proxy).

---

## Stack 4 — Coerência de autenticação e CORS

- [ ] **4.1** Definir o modelo de auth final.
  - ✅ Concluir quando: estiver documentado se é token Sanctum (header) OU cookie HttpOnly cross-origin.
- [ ] **4.2** Alinhar CORS ao modelo escolhido.
  - ✅ Concluir quando: se cookie cross-origin, `cors.php` tiver `supports_credentials => true`, origins explícitos (sem `*`) e `SANCTUM_STATEFUL_DOMAINS`/`SESSION_DOMAIN` corretos.
- [ ] **4.3** Validar 2FA ponta a ponta.
  - ✅ Concluir quando: login → desafio 2FA → sessão funcionar num teste manual e/ou automatizado.

---

## Stack 5 — Configuração de ambiente

- [ ] **5.1** Corrigir `.env` local enganoso.
  - ✅ Concluir quando: `backend/.env` local estiver com `APP_ENV=local` (não `production` com `sqlite`).
- [ ] **5.2** Padronizar ambiente de produção.
  - ✅ Concluir quando: produção usar `APP_ENV=production`, `APP_DEBUG=false`, `DB_CONNECTION=pgsql`.
- [ ] **5.3** Preencher todos os segredos obrigatórios.
  - ✅ Concluir quando: `APP_KEY`, `DB_PASSWORD`, `ASAAS_API_KEY`, `ASAAS_WEBHOOK_TOKEN`, `MAIL_*`, `REDIS_PASSWORD`, `GRAFANA_PASSWORD` estiverem definidos no ambiente de produção.

---

## Stack 6 — Testes e qualidade

- [ ] **6.1** Cobrir fluxos financeiros críticos.
  - ✅ Concluir quando: houver testes para motor de comissão, criação/cancelamento de venda e webhook Asaas (pago/estornado), todos passando.
- [ ] **6.2** Cobrir autorização por perfil.
  - ✅ Concluir quando: existirem testes garantindo que vendedor não acessa dados de outro vendedor/gestor.
- [ ] **6.3** Impor cobertura mínima no CI.
  - ✅ Concluir quando: o CI falhar se a cobertura cair abaixo do mínimo definido (ex.: 60%).

---

## Stack 7 — Validação final pré-go-live

- [ ] **7.1** Executar o UAT.
  - ✅ Concluir quando: todos os itens de `docs/UAT_CHECKLIST.md` passarem.
- [ ] **7.2** Testar restore de backup.
  - ✅ Concluir quando: `restore-database.sh` restaurar um backup num ambiente limpo com sucesso.
- [ ] **7.3** Simular rollback.
  - ✅ Concluir quando: `rollback.sh` executar e o sistema voltar à versão anterior sem perda de dados.
- [ ] **7.4** Smoke test em staging.
  - ✅ Concluir quando: `/api/health` retornar 200 com database + redis + asaas OK em staging.

---

## Stack 8 — LGPD / dados pessoais (bloqueador legal)

- [ ] **8.1** Exportação de dados do titular.
  - ✅ Concluir quando: existir endpoint que exporta todos os dados pessoais de um cliente.
- [ ] **8.2** Exclusão/anonimização.
  - ✅ Concluir quando: existir rotina de exclusão ou anonimização de dados do cliente a pedido.
- [ ] **8.3** Consentimento e retenção.
  - ✅ Concluir quando: houver registro de consentimento e política de retenção (limpeza de leads/clientes inativos) definida.
- [ ] **8.4** Publicar política de privacidade e termos.
  - ✅ Concluir quando: as páginas de privacidade/termos estiverem publicadas e linkadas no cadastro.

---

## Stack 9 — Performance e escala

- [ ] **9.1** Eliminar N+1 nas listagens.
  - ✅ Concluir quando: os endpoints de listagem usarem eager loading (`with()`) e não dispararem query por linha.
- [ ] **9.2** Garantir paginação.
  - ✅ Concluir quando: clientes, vendas e financeiro estiverem paginados (sem retornar todos os registros de uma vez).
- [ ] **9.3** Índices de banco.
  - ✅ Concluir quando: colunas de filtro pesado (`status`, datas, `user_id`, `cliente_id`) tiverem índice.

---

## Stack 10 — Resiliência de filas e jobs

- [ ] **10.1** Monitorar jobs falhos.
  - ✅ Concluir quando: houver visibilidade/alerta sobre `failed_jobs` (webhooks Asaas, importações, e-mails).
- [ ] **10.2** Política de retry e alerta de worker.
  - ✅ Concluir quando: jobs tiverem retry configurado e houver alerta quando o worker cair ou a fila empacar.

---

## Stack 11 — Vulnerabilidades de dependências

- [ ] **11.1** Auditar dependências PHP.
  - ✅ Concluir quando: `composer audit` rodar no CI e falhar em vulnerabilidade alta/crítica.
- [ ] **11.2** Auditar dependências JS.
  - ✅ Concluir quando: `npm audit` rodar no CI e falhar em vulnerabilidade alta/crítica.
- [ ] **11.3** Fixar versões novas.
  - ✅ Concluir quando: Next 16 / React 19 estiverem em versão fixa e validados sem quebra.

---

## Stack 12 — Observabilidade

- [ ] **12.1** Dashboards úteis.
  - ✅ Concluir quando: Grafana tiver painéis de taxa de erro, profundidade de fila e uso de disco/memória.
- [ ] **12.2** Alerta de webhook Asaas.
  - ✅ Concluir quando: houver alerta disparando quando webhooks de pagamento Asaas falharem.
- [ ] **12.3** Sentry validado.
  - ✅ Concluir quando: um erro de teste aparecer corretamente no Sentry (backend e frontend).

---

## Stack 13 — Backup e recuperação

- [ ] **13.1** Restore validado (recorrente).
  - ✅ Concluir quando: o restore for testado com sucesso e agendado para validação periódica.
- [ ] **13.2** Criptografia e cópia offsite.
  - ✅ Concluir quando: os backups estiverem criptografados e replicados fora do servidor principal.

---

### Critério de "100% pronto para produção"
Todos os itens das **Stacks 0–8** marcados como `[x]` (caminho crítico + LGPD) e, idealmente, **9–13** concluídos antes do go-live.
