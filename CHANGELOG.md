# Changelog — Basiléia Vendor OS

Todas as mudanças notáveis neste projeto serão documentadas aqui.
O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

---

## [1.0.0] — 2026-07-27

### Adicionado
- **Fase 1–5:** Fundação do sistema — autenticação JWT com 2FA (Google Authenticator), RBAC (Master/Gestor/Vendedor), CSRF/XSS/CORS, migrations PostgreSQL, seeders, CI/CD com GitHub Actions.
- **Fase 6:** Configuração de produção — Docker Compose multi-serviço (backend, frontend, db, redis), variáveis de ambiente seguras, healthchecks.
- **Fase 7:** Segurança de produção — rate limiting, CSP headers, proteção contra brute-force, sanitização de inputs.
- **Fase 8:** Monitoramento — integração com Sentry (backend + frontend), logging estruturado, endpoint `/api/health`.
- **Fase 9:** Backup & Recovery — scripts automatizados de backup/restore PostgreSQL, cron job de backup diário, plano de Disaster Recovery.
- **Fase 10:** Performance — cache Redis para queries pesadas, otimização de consultas N+1, lazy loading de componentes React.
- **Fase 11:** LGPD — anonimização de dados de clientes, página de Termos de Uso e Política de Privacidade, middleware de auditoria (`AuditLogMiddleware`), trait `Auditable`.
- **Fase 12:** Documentação operacional — guia de deploy, runbooks de incidentes, script de rollback, contatos de suporte.
- **Fase 13:** Testes de produção — scripts k6 de carga, checklist UAT, smoke tests automatizados.
- **Fase 14:** Infraestrutura avançada — Prometheus + Grafana para métricas, volumes persistentes.
- **Fase 15:** Dados e migração — plano ETL para dados legados, script de validação de integridade, ProductionSeeder (500 clientes, 1500 vendas).
- **Fase 16:** UX — modal de Onboarding interativo (3 passos), Tour Guiado com `react-joyride`, guia do usuário final.
- **Fase 17:** Otimizações — PWA (manifest.json + Service Worker), modo offline (banner + hook `useOffline`), infraestrutura de Push Notifications (Web Push API + `PushNotification.php`).

### Segurança
- Senhas hasheadas com bcrypt (cost 12).
- Tokens Sanctum com expiração configurável.
- 2FA obrigatório para perfil Master.
- Headers de segurança (CSP, HSTS, X-Frame-Options, X-Content-Type-Options).
- Rate limiting por IP em rotas de autenticação.
- Audit trail completo de ações sensíveis.

### Infraestrutura
- Docker Compose com 6 serviços: `backend`, `frontend`, `db`, `redis`, `prometheus`, `grafana`.
- Backup automático via cron (diário às 02:00).
- Monitoramento de erros via Sentry.
- Métricas de infraestrutura via Prometheus/Grafana.
