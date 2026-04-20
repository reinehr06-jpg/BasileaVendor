# BasileaVendor - Projeto de CRM/Vendas

## Visão Geral do Sistema

Sistema de CRM e gerenciamento de vendas desenvolvido em Laravel (backend) e Next.js (frontend). O projeto inclui gerenciamento de campanhas, leads, contatos, e integração com IA.

## Funcionalidades Implementadas

### Módulo de崔 Campaigns

- Gerenciamento de campanhas de marketing
- Visualização em kanban e lista
- Sistema de filtros avançados
- Gráficos de desempenho
- Editor de campanhas via drawer

### Módulo de Leads

- Captura de leads via webhook (CampanhasController)
- Pipeline kanban para acompanhamento
- Integração com IA para sugestões
- Workflow de aprovação (gestor → vendedor)
- Histórico de interações

### Módulo de Contatos

- Cadastro e gerenciamento de contatos
- Associação com campanhas
- Calendário compartilhado
- Registro de atividades

### Integração com IA

- Painel de IA no menu ADM
- Sugestões para primeira mensagem
- Configuração de IA local
- Fluxo de aprovação de mensagens

### Autenticação e Segurança

- Login com autenticação Laravel
- Suporte a two-factor authentication
- Permissões por papéis (ADM, Gestor, Vendedor)

## Estrutura de Diretórios

```
backend/
├── app/
│   ├── Http/Controllers/
│   │   ├── CampanhaController.php
│   │   ├── ContatoController.php
│   │   ├── WebhookController.php
│   │   ├── PrimeiraMensagemController.php
│   │   ├── CalendarioController.php
│   │   └── IaController.php
│   ├── Models/
│   └── Services/IA/
├── database/migrations/
├── resources/views/
└── routes/
```

## Comandos Úteis

```bash
# Iniciar servidor development
php artisan serve

# Rodar migrations
php artisan migrate

# Executar seeders
php artisan db:seed

# Limpar cache
php artisan cache:clear
```

## Conventions de Código

- Controllers:命名ação PascalCase (ex: CampanhaController)
- Models:命名ação PascalCase (ex: Campanha)
- Migrations:命名ação timestamp_nome.php
- Views:命名ação kebab-case.blade.php
- Rotas:命名ação resourceful quando possível