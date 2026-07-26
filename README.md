# BasileaVendor

> ERP/CRM de vendas para igrejas — integrado a Asaas (pagamentos), WhatsApp, Meta Ads e OpenAI.

**Stack:** Laravel 11 · PHP 8.4 · Next.js 16 · TypeScript · PostgreSQL 15 · Docker

---

## Início rápido

### Com Docker Compose (recomendado)

```bash
# 1. Copie e preencha as variáveis de ambiente
cp .env.docker.example .env

# 2. Suba todos os serviços
docker compose up -d

# 3. Acesse o sistema
# Frontend: http://localhost:8007
# Backend API: http://localhost:8000/api
```

### Sem Docker (desenvolvimento local)

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve  # porta 8000

# Frontend (em outro terminal)
cd frontend
npm install
npm run dev  # porta 3000
```

---

## Estrutura do projeto

```
BasileaVendor/
├── backend/          # Laravel 11 — API + web routes
│   ├── app/
│   │   ├── Http/Controllers/Api/   # API REST para o frontend Next.js
│   │   ├── Http/Controllers/       # Controllers web (blade/legacy)
│   │   ├── Models/                 # 78 models Eloquent
│   │   └── Services/               # Lógica de negócio (Commission, Asaas, Chat…)
│   ├── database/migrations/        # 116 migrations
│   ├── routes/
│   │   ├── api.php                 # 140 rotas REST (auth:sanctum)
│   │   └── web.php                 # Rotas de sessão (blade, legacy)
│   └── tests/
│       ├── Unit/CommissionCalculatorTest.php   # Motor de comissão
│       └── Feature/                            # Testes de integração
│
├── frontend/         # Next.js 16 — SPA/App Router
│   ├── src/app/
│   │   ├── (menu)/   # Páginas do painel gestor/admin
│   │   ├── gestor/   # Painel do gestor comercial
│   │   └── vendedor/ # Painel do vendedor
│   ├── src/services/ # Clientes de API tipados
│   └── src/context/  # AuthContext, LocaleContext
│
├── docker-compose.yml            # Deploy Docker (local + VPS)
└── backend/render.yaml           # Deploy Render.com
```

---

## Variáveis de ambiente obrigatórias

| Variável | Onde configurar | Descrição |
|---------|----------------|-----------|
| `APP_KEY` | Backend `.env` | **Nunca** deixar vazio em produção |
| `DB_PASSWORD` | `.env` raiz / backend | Senha do PostgreSQL |
| `ASAAS_API_KEY` | Backend `.env` | Chave da API Asaas |
| `ASAAS_WEBHOOK_TOKEN` | Backend `.env` | Token de validação de webhooks Asaas |
| `MAIL_*` | Backend `.env` | Credenciais SMTP reais (não Mailtrap) |
| `CHAT_WEBHOOK_TOKEN` | Backend `.env` | Token WhatsApp/Meta (opcional, recomendado) |
| `NEXT_PUBLIC_API_URL` | Frontend `.env.local` | URL da API (`/api` em Docker, URL completa em prod) |

---

## Executar testes

```bash
cd backend

# Todos os testes
php artisan test

# Apenas motor de comissão (sem banco)
php artisan test --filter=CommissionCalculatorTest

# Testes de feature (requer banco de teste configurado)
php artisan test --filter=AsaasWebhookTest
php artisan test --filter=DashboardApiTest
```

---

## Documentação complementar

| Arquivo | Conteúdo |
|---------|---------|
| [DOCUMENTACAO_BASILEIA_VENDAS.md](./DOCUMENTACAO_BASILEIA_VENDAS.md) | Documentação completa do sistema |
| [MOTOR_COMISSAO.md](./MOTOR_COMISSAO.md) | Regras do motor de comissão |
| [SISTEMA_CAMPANHAS_README.md](./SISTEMA_CAMPANHAS_README.md) | Módulo de campanhas e leads |
| [RODAR_LOCAL.md](./RODAR_LOCAL.md) | Guia detalhado de setup local |
| [backend/AGENTS.md](./backend/AGENTS.md) | Guia para agentes de IA no backend |
| [frontend/MAPA_DO_TESOURO.md](./frontend/MAPA_DO_TESOURO.md) | Arquitetura do frontend |
| [frontend/API_CONTRACT.md](./frontend/API_CONTRACT.md) | Contrato de API frontend↔backend |

---

## Deploy

### Docker Compose (VPS/servidor próprio)
O Supervisor gerencia Nginx + PHP-FPM + Scheduler + Queue Worker em um único container.
Ver [`docker-compose.yml`](./docker-compose.yml) e [`backend/supervisor.conf`](./backend/supervisor.conf).

### Render.com
Ver [`backend/render.yaml`](./backend/render.yaml).  
⚠️ **Não usar `render.yaml` junto com Docker Compose** — o scheduler rodaria em duplicata.

---

## Módulos principais

| Módulo | Controller |
|--------|-----------|
| Vendas & Comissões | `VendaController`, `ComissaoController` |
| Checkout & Assinatura | `CheckoutNewController`, `SubscriptionController` |
| Chat / WhatsApp | `Chat/ChatController`, `Chat/ChatWebhookController` |
| Leads & Campanhas | `Lead/LeadController`, `CampanhaController` |
| Financeiro | `Api/FinanceiroController`, `RelatorioController` |
| Integrações Asaas | `AsaasWebhookController`, `Services/AsaasService` |
| IA | `AIServiceController`, `Jobs/GerarAnaliseVendedorJob` |
