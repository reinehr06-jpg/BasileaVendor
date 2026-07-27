# FASES 6-17: Roadmap Completo para Produção

## FASE 6: Configuração de Produção Real 🔴

### Objetivo
Configurar ambiente de produção seguro e estável.

### Pré-requisito
Fases 1-5 completas.

---

### Tarefa 6.1 — Variáveis de Ambiente de Produção

**Arquivos:**
- `backend/.env.production` (criar)
- `docker-compose.yml` (atualizar)

**Ação:**

1. Criar `.env.production`:
```env
APP_NAME="BasileaVendor"
APP_ENV=production
APP_KEY=base64:GERAR_CHAVE_SEGURA_AQUI
APP_DEBUG=false
APP_URL=https://vendor.basileia.global

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=basileia_vendas
DB_USERNAME=postgres
DB_PASSWORD=SENHA_SEGURA_AQUI

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=database

ASAAS_ENVIRONMENT=production
ASAAS_API_KEY=$aact_prod_SEU_TOKEN_AQUI
ASAAS_WEBHOOK_TOKEN=TOKEN_WEBHOOK_SEGURO

SENTRY_LARAVEL_DSN=https://SEU_DSN_AQUI

MAIL_MAILER=smtp
MAIL_HOST=smtp.seuprovedor.com
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@basileia.global
MAIL_FROM_NAME="BasileaVendor"

SESSION_LIFETIME=120
SANCTUM_STATEFUL_DOMAINS=vendor.basileia.global
```

2. Gerar APP_KEY:
```bash
cd backend
php artisan key:generate --show
```

**Validação:**
- `APP_ENV=production` configurado
- `APP_DEBUG=false` em produção
- `APP_KEY` gerada e fixa
- Todas as variáveis sensíveis configuradas

---

### Tarefa 6.2 — SSL/HTTPS

**Arquivos:**
- `backend/nginx.conf` (atualizar)
- `backend/Dockerfile.prod` (atualizar)

**Ação:**

1. Configurar SSL no Nginx:
```nginx
server {
    listen 443 ssl http2;
    server_name vendor.basileia.global;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
}

server {
    listen 80;
    server_name vendor.basileia.global;
    return 301 https://$server_name$request_uri;
}
```

2. Obter certificado (Let's Encrypt):
```bash
apt-get install certbot python3-certbot-nginx
certbot --nginx -d vendor.basileia.global
```

3. Configurar cookies seguros em `backend/config/session.php`:
```php
return [
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'lax',
];
```

**Validação:**
- HTTPS funcionando
- HTTP redireciona para HTTPS
- Certificado válido
- Cookies seguros

---

### Tarefa 6.3 — APP_KEY Fixa

**Arquivos:**
- `backend/entrypoint.sh` (atualizar)

**Ação:**

Modificar `entrypoint.sh` para falhar em produção se APP_KEY não estiver configurada:
```bash
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
  if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERRO FATAL: APP_KEY não configurada em produção."
    exit 1
  fi
  echo "APP_KEY configurada para produção."
else
  if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "APP_KEY não definida — gerando uma nova (ambiente: ${APP_ENV:-local})."
    php artisan key:generate --force 2>/dev/null || true
  fi
fi
```

**Validação:**
- Deploy em produção sem APP_KEY falha
- Deploy em produção com APP_KEY funciona
- Deploy em sandbox gera APP_KEY automaticamente

---

### Tarefa 6.4 — PostgreSQL em Produção com Backup

**Arquivos:**
- `docker-compose.yml` (atualizar)

**Ação:**

Configurar PostgreSQL com volumes persistentes:
```yaml
services:
  postgres:
    image: postgres:15-alpine
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-basileia_vendas}
      POSTGRES_USER: ${DB_USERNAME:-postgres}
      POSTGRES_PASSWORD: ${DB_PASSWORD:?defina DB_PASSWORD no .env}
    volumes:
      - basileia_pgdata:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  basileia_pgdata:
```

**Validação:**
- PostgreSQL rodando com health check
- Dados persistidos em volume
- Backup automático configurado (ver Fase 9)

---

### Tarefa 6.5 — Redis Configurado e Testado

**Arquivos:**
- `docker-compose.yml` (atualizar)
- `backend/config/cache.php` (atualizar)
- `backend/config/database.php` (atualizar)

**Ação:**

1. Adicionar Redis no `docker-compose.yml`:
```yaml
services:
  redis:
    image: redis:7-alpine
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  redis-data:
```

2. Configurar `backend/config/cache.php`:
```php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

3. Configurar `backend/config/database.php`:
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
    
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
    ],
],
```

**Validação:**
- Redis rodando no Docker
- Cache usando Redis
- Sessions usando Redis
- Queues usando Redis

---

## Checklist da Fase 6

- [ ] **Tarefa 6.1** — Variáveis de ambiente de produção
- [ ] **Tarefa 6.2** — SSL/HTTPS
- [ ] **Tarefa 6.3** — APP_KEY Fixa
- [ ] **Tarefa 6.4** — PostgreSQL em Produção
- [ ] **Tarefa 6.5** — Redis Configurado

---

## FASE 7: Segurança de Produção 🔴

### Objetivo
Implementar segurança robusta para produção.

### Pré-requisito
Fase 6 completa.

---

### Tarefa 7.1 — Rate Limiting

**Arquivos:**
- `backend/app/Providers/RouteServiceProvider.php` (atualizar)
- `backend/routes/api.php` (atualizar)
- `backend/routes/web.php` (atualizar)

**Ação:**

Configurar rate limiting para APIs públicas, webhooks e login.

**Validação:**
- Rate limiting funcionando
- APIs protegidas contra abuso
- Login protegido contra brute force

---

### Tarefa 7.2 — CORS Restrito

**Arquivos:**
- `backend/config/cors.php` (atualizar)

**Ação:**

Configurar CORS restrito apenas a domínios autorizados.

**Validação:**
- CORS restrito ao domínio autorizado
- Requisições de outros domínios bloqueadas

---

### Tarefa 7.3 — Validação de Webhook Asaas Obrigatória

**Arquivos:**
- `backend/app/Http/Controllers/AsaasWebhookController.php` (atualizar)

**Ação:**

Tornar validação obrigatória em produção.

**Validação:**
- Webhook rejeita requisições sem token em produção
- Webhook aceita apenas com token válido

---

### Tarefa 7.4 — Tokens HttpOnly

**Arquivos:**
- `backend/app/Http/Controllers/Api/AuthController.php` (atualizar)
- `frontend/src/context/AuthContext.tsx` (atualizar)
- `frontend/src/lib/api.ts` (atualizar)

**Ação:**

Configurar cookie HttpOnly e remover localStorage.

**Validação:**
- Cookie HttpOnly configurado
- localStorage não armazena token
- Requisições enviam cookie automaticamente

---

### Tarefa 7.5 — 2FA Obrigatório para Master/Gestor

**Arquivos:**
- `backend/app/Http/Controllers/Api/AuthController.php` (atualizar)
- `frontend/src/app/auth/2fa/page.tsx` (criar)
- `frontend/src/app/auth/login/page.tsx` (atualizar)

**Ação:**

Implementar 2FA obrigatório para perfis master/gestor.

**Validação:**
- Master/gestor com 2FA ativado → retorna `requires_2fa: true`
- Inserir código correto → retorna token
- Inserir código errado → retorna 401

---

## Checklist da Fase 7

- [ ] **Tarefa 7.1** — Rate Limiting
- [ ] **Tarefa 7.2** — CORS Restrito
- [ ] **Tarefa 7.3** — Validação de Webhook Asaas
- [ ] **Tarefa 7.4** — Tokens HttpOnly
- [ ] **Tarefa 7.5** — 2FA Obrigatório

---

## FASE 8: Monitoramento Básico 🔴

### Objetivo
Implementar monitoramento e observabilidade em produção.

### Pré-requisito
Fase 7 completa.

---

### Tarefa 8.1 — Logs Centralizados (Sentry)

**Arquivos:**
- `backend/composer.json` (atualizar)
- `backend/config/sentry.php` (criar)
- `frontend/package.json` (atualizar)
- `frontend/next.config.js` (atualizar)

**Ação:**

Instalar e configurar Sentry no backend e frontend.

**Validação:**
- Erros enviados para Sentry
- Dashboard do Sentry mostra erros
- Performance traces capturados

---

### Tarefa 8.2 — Alertas de Erro Crítico

**Arquivos:**
- Sentry Dashboard (configurar)

**Ação:**

Configurar alertas no Sentry para erros críticos, performance e taxa de erro.

**Validação:**
- Alertas configurados
- Notificações enviadas
- Incidentes detectados

---

### Tarefa 8.3 — Health Checks

**Arquivos:**
- `backend/routes/web.php` (atualizar)

**Ação:**

Adicionar endpoint de health check que verifica database, redis e asaas.

**Validação:**
- Health check respondendo
- Retorna status de cada serviço
- Retorna 503 se algum serviço falhar

---

### Tarefa 8.4 — Monitoramento de Uptime

**Arquivos:**
- Serviço externo (UptimeRobot, Pingdom, etc.)

**Ação:**

Configurar monitoramento de uptime externo.

**Validação:**
- Monitoramento ativo
- Alertas configurados
- Dashboard funcionando

---

## Checklist da Fase 8

- [ ] **Tarefa 8.1** — Logs Centralizados (Sentry)
- [ ] **Tarefa 8.2** — Alertas de Erro Crítico
- [ ] **Tarefa 8.3** — Health Checks
- [ ] **Tarefa 8.4** — Monitoramento de Uptime

---

## FASE 9: Backup e Recovery 🔴

### Objetivo
Implementar backup automático e plano de recuperação.

### Pré-requisito
Fase 8 completa.

---

### Tarefa 9.1 — Backup Automático Diário

**Arquivos:**
- `scripts/backup-database.sh` (criar)
- `/etc/cron.d/basileia-backup` (criar)

**Ação:**

Criar script de backup automático e configurar cron job.

**Validação:**
- Backup automático funcionando
- Backups sendo criados diariamente
- Backups antigos sendo limpos

---

### Tarefa 9.2 — Teste de Restore

**Arquivos:**
- `scripts/restore-database.sh` (criar)

**Ação:**

Criar script de restore e testar em banco de teste.

**Validação:**
- Restore funcionando
- Dados restaurados corretamente
- Integridade verificada

---

### Tarefa 9.3 — Plano de Disaster Recovery

**Arquivos:**
- `docs/DISASTER_RECOVERY.md` (criar)

**Ação:**

Criar documentação de disaster recovery com cenários, procedimentos e contatos.

**Validação:**
- Documentação completa
- Procedimentos testados
- Contatos atualizados

---

## Checklist da Fase 9

- [ ] **Tarefa 9.1** — Backup Automático Diário
- [ ] **Tarefa 9.2** — Teste de Restore
- [ ] **Tarefa 9.3** — Plano de Disaster Recovery

---

## FASE 10: Performance em Produção 🟠

### Objetivo
Otimizar performance para produção.

### Pré-requisito
Fase 9 completa.

---

### Tarefa 10.1 — CDN para Assets Estáticos

**Arquivos:**
- `frontend/next.config.js` (atualizar)
- Serviço CDN (Cloudflare, AWS CloudFront, etc.)

**Ação:**

Configurar CDN para servir assets estáticos.

**Validação:**
- Assets servidos via CDN
- Cache configurado
- Performance melhorada

---

### Tarefa 10.2 — Compressão gzip/brotli

**Arquivos:**
- `backend/nginx.conf` (atualizar)

**Ação:**

Configurar compressão gzip/brotli no Nginx.

**Validação:**
- gzip/brotli funcionando
- Respostas comprimidas
- Performance melhorada

---

### Tarefa 10.3 — Cache de Browser

**Arquivos:**
- `backend/nginx.conf` (atualizar)

**Ação:**

Configurar cache de browser no Nginx.

**Validação:**
- Cache configurado
- Assets estáticos com cache longo
- HTML com cache curto

---

### Tarefa 10.4 — Otimização de Imagens

**Arquivos:**
- `frontend/next.config.js` (atualizar)
- Componentes de imagem (atualizar)

**Ação:**

Configurar Next.js Image Optimization e usar componente Image.

**Validação:**
- Imagens otimizadas
- Formatos modernos (AVIF, WebP)
- Lazy loading funcionando

---

### Tarefa 10.5 — Lazy Loading de Componentes Pesados

**Arquivos:**
- `frontend/src/app/vendedor/page.tsx` (atualizar)
- Outros componentes pesados (atualizar)

**Ação:**

Usar dynamic imports para componentes pesados.

**Validação:**
- Componentes pesados carregados sob demanda
- Bundle size reduzido
- First Contentful Paint melhorado

---

## Checklist da Fase 10

- [ ] **Tarefa 10.1** — CDN para Assets Estáticos
- [ ] **Tarefa 10.2** — Compressão gzip/brotli
- [ ] **Tarefa 10.3** — Cache de Browser
- [ ] **Tarefa 10.4** — Otimização de Imagens
- [ ] **Tarefa 10.5** — Lazy Loading

---

## FASE 11: Conformidade Legal 🟠

### Objetivo
Implementar conformidade com LGPD e políticas legais.

### Pré-requisito
Fase 10 completa.

---

### Tarefa 11.1 — LGPD Compliance

**Arquivos:**
- `backend/app/Http/Controllers/Api/ClienteController.php` (atualizar)
- `backend/routes/api.php` (atualizar)
- `frontend/src/app/configuracoes/privacidade/page.tsx` (criar)

**Ação:**

Implementar direito ao esquecimento, consentimento de dados e tela de privacidade.

**Validação:**
- Direito ao esquecimento funcionando
- Consentimento registrado
- Dados anonimizados corretamente

---

### Tarefa 11.2 — Política de Privacidade

**Arquivos:**
- `frontend/src/app/privacidade/page.tsx` (criar)

**Ação:**

Criar página de política de privacidade.

**Validação:**
- Página acessível
- Conteúdo completo
- Link no rodapé do site

---

### Tarefa 11.3 — Termos de Uso

**Arquivos:**
- `frontend/src/app/termos/page.tsx` (criar)

**Ação:**

Criar página de termos de uso.

**Validação:**
- Página acessível
- Conteúdo revisado por jurídico
- Link no rodapé do site

---

### Tarefa 11.4 — Logs de Auditoria

**Arquivos:**
- `backend/database/migrations/xxxx_create_audit_logs.php` (criar)
- `backend/app/Models/AuditLog.php` (criar)
- `backend/app/Http/Middleware/AuditLog.php` (criar)

**Ação:**

Criar tabela, model e middleware de auditoria.

**Validação:**
- Logs de auditoria sendo criados
- Ações críticas registradas
- Dados completos (user, action, model, values)

---

## Checklist da Fase 11

- [ ] **Tarefa 11.1** — LGPD Compliance
- [ ] **Tarefa 11.2** — Política de Privacidade
- [ ] **Tarefa 11.3** — Termos de Uso
- [ ] **Tarefa 11.4** — Logs de Auditoria

---

## FASE 12: Documentação Operacional 🟠

### Objetivo
Documentar processos operacionais e criar plano de deploy.

### Pré-requisito
Fase 11 completa.

---

### Tarefa 12.1 — Documentação de Deploy

**Arquivos:**
- `docs/DEPLOY.md` (criar)

**Ação:**

Criar guia de deploy passo-a-passo com troubleshooting.

**Validação:**
- Guia completo e claro
- Passos testados
- Troubleshooting documentado

---

### Tarefa 12.2 — Runbooks para Incidentes

**Arquivos:**
- `docs/RUNBOOK.md` (criar)

**Ação:**

Criar runbook de incidentes com procedimentos e template de post-mortem.

**Validação:**
- Runbooks cobrindo incidentes comuns
- Passos claros e testados
- Template de post-mortem disponível

---

### Tarefa 12.3 — Plano de Rollback

**Arquivos:**
- `scripts/rollback.sh` (criar)
- `CHANGELOG.md` (criar)

**Ação:**

Criar script de rollback e changelog.

**Validação:**
- Script de rollback funcionando
- Changelog atualizado
- Rollback testado

---

### Tarefa 12.4 — Contatos de Suporte/On-Call

**Arquivos:**
- `docs/CONTATOS.md` (criar)

**Ação:**

Criar documento de contatos com escala de on-call e procedimento de emergência.

**Validação:**
- Contatos atualizados
- Escala definida
- Procedimento documentado

---

## Checklist da Fase 12

- [ ] **Tarefa 12.1** — Documentação de Deploy
- [ ] **Tarefa 12.2** — Runbooks para Incidentes
- [ ] **Tarefa 12.3** — Plano de Rollback
- [ ] **Tarefa 12.4** — Contatos de Suporte

---

## FASE 13: Testes de Produção 🟡

### Objetivo
Implementar testes de carga, segurança e aceitação.

### Pré-requisito
Fase 12 completa.

---

### Tarefa 13.1 — Testes de Carga

**Arquivos:**
- `tests/load/k6-scripts.js` (criar)

**Ação:**

Instalar k6 e criar script de teste de carga.

**Validação:**
- Teste de carga executado
- Métricas coletadas
- Performance aceitável

---

### Tarefa 13.2 — Testes de Penetração

**Arquivos:**
- Serviço externo (OWASP ZAP, Burp Suite, etc.)

**Ação:**

Executar scan de segurança e corrigir vulnerabilidades.

**Validação:**
- Scan executado
- Vulnerabilidades identificadas
- Correções aplicadas

---

### Tarefa 13.3 — Testes de Aceitação do Usuário

**Arquivos:**
- `docs/UAT_CHECKLIST.md` (criar)

**Ação:**

Criar checklist de aceitação do usuário.

**Validação:**
- Checklist completo
- Testes executados
- Aprovação do usuário

---

### Tarefa 13.4 — Smoke Tests Automatizados

**Arquivos:**
- `tests/smoke/smoke-tests.sh` (criar)

**Ação:**

Criar script de smoke tests automatizados.

**Validação:**
- Smoke tests executados
- Todos os testes passando
- Script automatizado

---

## Checklist da Fase 13

- [ ] **Tarefa 13.1** — Testes de Carga
- [ ] **Tarefa 13.2** — Testes de Penetração
- [ ] **Tarefa 13.3** — Testes de Aceitação
- [ ] **Tarefa 13.4** — Smoke Tests

---

## FASE 14: Infraestrutura Avançada 🟡

### Objetivo
Implementar infraestrutura avançada para escalabilidade.

### Pré-requisito
Fase 13 completa.

---

### Tarefa 14.1 — WAF (Web Application Firewall)

**Arquivos:**
- Serviço externo (Cloudflare WAF, AWS WAF, etc.)

**Ação:**

Configurar WAF com regras de firewall.

**Validação:**
- WAF ativo
- Regras configuradas
- Ataques bloqueados

---

### Tarefa 14.2 — Monitoramento de Recursos

**Arquivos:**
- `docker-compose.yml` (atualizar)
- Serviço de monitoramento (Prometheus + Grafana)

**Ação:**

Instalar Prometheus e Grafana e configurar dashboards.

**Validação:**
- Prometheus coletando métricas
- Grafana exibindo dashboards
- Alertas configurados

---

### Tarefa 14.3 — Auto-scaling

**Arquivos:**
- Serviço de orchestration (Kubernetes, AWS ECS, etc.)

**Ação:**

Configurar auto-scaling com HPA e cluster autoscaler.

**Validação:**
- Auto-scaling configurado
- Pods escalando automaticamente
- Cluster escalando quando necessário

---

### Tarefa 14.4 — Multi-region (Opcional)

**Arquivos:**
- Infraestrutura multi-region (AWS, GCP, etc.)

**Ação:**

Configurar múltiplas regiões com DNS failover e replicação.

**Validação:**
- Multi-region configurado
- Failover funcionando
- Replicação ativa

---

## Checklist da Fase 14

- [ ] **Tarefa 14.1** — WAF
- [ ] **Tarefa 14.2** — Monitoramento de Recursos
- [ ] **Tarefa 14.3** — Auto-scaling
- [ ] **Tarefa 14.4** — Multi-region (Opcional)

---

## FASE 15: Dados e Migração 🟡

### Objetivo
Implementar strategy de migração de dados e validação.

### Pré-requisito
Fase 14 completa.

---

### Tarefa 15.1 — Strategy de Migração de Dados Legados

**Arquivos:**
- `scripts/migrate-legacy-data.sh` (criar)
- `docs/MIGRATION_PLAN.md` (criar)

**Ação:**

Criar plano de migração e scripts ETL.

**Validação:**
- Plano de migração documentado
- Scripts ETL criados
- Migração testada

---

### Tarefa 15.2 — Validação de Integridade Pós-Migração

**Arquivos:**
- `scripts/validate-migration.sh` (criar)

**Ação:**

Criar script de validação de integridade.

**Validação:**
- Script de validação criado
- Integridade verificada
- Dados consistentes

---

### Tarefa 15.3 — Script de Seed para Dados de Teste

**Arquivos:**
- `backend/database/seeders/ProductionSeeder.php` (criar)

**Ação:**

Criar seeder para dados de teste.

**Validação:**
- Seeder criado
- Dados de teste gerados
- Sistema funcional com dados de teste

---

## Checklist da Fase 15

- [ ] **Tarefa 15.1** — Strategy de Migração
- [ ] **Tarefa 15.2** — Validação de Integridade
- [ ] **Tarefa 15.3** — Script de Seed

---

## FASE 16: Melhorias de UX 🟢

### Objetivo
Implementar melhorias de experiência do usuário.

### Pré-requisito
Fase 15 completa.

---

### Tarefa 16.1 — Onboarding Interativo

**Arquivos:**
- `frontend/src/components/Onboarding.tsx` (criar)
- `frontend/src/app/dashboard/page.tsx` (atualizar)

**Ação:**

Criar componente de onboarding e integrar no dashboard.

**Validação:**
- Onboarding exibido para novos usuários
- Passos navegáveis
- Onboarding não exibido após conclusão

---

### Tarefa 16.2 — Tour Guiado das Funcionalidades

**Arquivos:**
- `frontend/src/components/Tour.tsx` (criar)

**Ação:**

Criar componente de tour guiado.

**Validação:**
- Tour guiado funcionando
- Steps destacados
- Navegação entre steps

---

### Tarefa 16.3 — Documentação para Usuários Finais

**Arquivos:**
- `docs/USER_GUIDE.md` (criar)

**Ação:**

Criar guia do usuário.

**Validação:**
- Guia completo
- Passos claros
- Screenshots (se possível)

---

## Checklist da Fase 16

- [ ] **Tarefa 16.1** — Onboarding Interativo
- [ ] **Tarefa 16.2** — Tour Guiado
- [ ] **Tarefa 16.3** — Documentação para Usuários

---

## FASE 17: Otimizações 🟢

### Objetivo
Implementar otimizações avançadas.

### Pré-requisito
Fase 16 completa.

---

### Tarefa 17.1 — PWA (Progressive Web App)

**Arquivos:**
- `frontend/next.config.js` (atualizar)
- `frontend/public/manifest.json` (criar)
- `frontend/public/sw.js` (criar)

**Ação:**

Instalar next-pwa e configurar manifest e service worker.

**Validação:**
- PWA instalado
- Service worker funcionando
- App instalável

---

### Tarefa 17.2 — Offline Mode

**Arquivos:**
- `frontend/src/hooks/useOffline.ts` (criar)

**Ação:**

Criar hook para detectar offline e integrar no app.

**Validação:**
- Hook detectando offline
- Mensagem exibida
- Funcionalidades limitadas em offline

---

### Tarefa 17.3 — Push Notifications

**Arquivos:**
- `frontend/src/hooks/usePushNotifications.ts` (criar)
- `backend/app/Notifications/PushNotification.php` (criar)

**Ação:**

Criar hook para push notifications e notification no backend.

**Validação:**
- Permissão solicitada
- Notificações enviadas
- Notificações recebidas

---

## Checklist da Fase 17

- [ ] **Tarefa 17.1** — PWA
- [ ] **Tarefa 17.2** — Offline Mode
- [ ] **Tarefa 17.3** — Push Notifications

---

# RESUMO FINAL — Roadmap Completo

## Fases Concluídas
- ✅ **Fase 1:** Segurança Crítica
- ✅ **Fase 2:** Estabilidade e Integridade
- ✅ **Fase 3:** Qualidade de Código
- ✅ **Fase 4:** Testes e CI/CD
- ✅ **Fase 5:** Arquitetura e Manutenção

## Próximas Fases

### 🔴 Fases Críticas (Bloqueadores)
- **Fase 6:** Configuração de Produção Real
- **Fase 7:** Segurança de Produção
- **Fase 8:** Monitoramento Básico
- **Fase 9:** Backup e Recovery

### 🟠 Fases Importantes (Alto Risco)
- **Fase 10:** Performance em Produção
- **Fase 11:** Conformidade Legal
- **Fase 12:** Documentação Operacional

### 🟡 Fases Recomendadas (Melhorias)
- **Fase 13:** Testes de Produção
- **Fase 14:** Infraestrutura Avançada
- **Fase 15:** Dados e Migração

### 🟢 Fases Opcionais (Nice-to-Have)
- **Fase 16:** Melhorias de UX
- **Fase 17:** Otimizações

---

## Ordem de Execução Recomendada

1. **Fases 6-9** (2-3 semanas) — Essencial para produção
2. **Fases 10-12** (2 semanas) — Importante para operação
3. **Fases 13-15** (2-3 semanas) — Recomendado para qualidade
4. **Fases 16-17** (1-2 semanas) — Opcional para UX

---

## Checklist Final para Produção

### Antes de ir para produção:
- [ ] Fases 1-5 completas
- [ ] Fases 6-9 completas (produção segura)
- [ ] Fases 10-12 completas (operação estável)

### Validação final:
- [ ] HTTPS funcionando
- [ ] Backup automático testado
- [ ] Monitoramento ativo
- [ ] Alertas configurados
- [ ] Documentação revisada
- [ ] Deploy de teste executado
- [ ] Rollback testado
- [ ] LGPD compliance
- [ ] Testes de carga executados

---

**Próximo passo:** Começar pela **Fase 6, Tarefa 6.1** (Variáveis de ambiente de produção).
