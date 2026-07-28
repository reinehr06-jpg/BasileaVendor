# UAT Checklist (User Acceptance Testing) - Basiléia Vendor OS

Este documento é o checklist oficial de aceite. Antes de declarar a versão como "Estável" e abrir para usuários reais, um humano deve executar manualmente estes testes na interface de produção.

## 1. Módulo de Autenticação e Acesso
- [ ] O Login funciona com credenciais corretas.
- [ ] O sistema bloqueia múltiplas tentativas falhas de login (Rate Limiter deve disparar 429 Too Many Requests após algumas falhas).
- [ ] Usuários de perfil Master e Gestor são **obrigados** a configurar o Google Authenticator (2FA) no primeiro acesso.
- [ ] O código 2FA é exigido e validado com sucesso a cada login de Master/Gestor.
- [ ] O Token JWT é armazenado de forma invisível via cookies (HttpOnly) e não fica exposto no LocalStorage.

## 2. Módulo de Vendas e Checkout
- [ ] Um Vendedor consegue cadastrar um Cliente novo sem erros.
- [ ] O Vendedor consegue registrar uma venda (plano mensal/anual) para esse cliente.
- [ ] O Asaas cria a assinatura/cobrança no Sandbox/Produção e retorna o ID para o sistema.
- [ ] O painel principal do Vendedor reflete a nova venda nos gráficos de "Faturamento" imediatamente ou após recarregar a página (com lazy loading funcionando rápido).

## 3. Conformidade Legal (LGPD)
- [ ] Navegar até Configurações -> Privacidade.
- [ ] Inserir o ID numérico de um cliente de teste.
- [ ] O sistema deve exibir a mensagem de sucesso verde.
- [ ] Verificando no banco de dados (ou listagem de clientes), os dados do cliente foram convertidos para "Cliente Removido LGPD" e o e-mail para "deleted_X@anon.com".
- [ ] As vendas vinculadas a esse cliente continuam contando no Faturamento Geral da empresa.

## 4. Auditoria (Audit Trails)
- [ ] Logar como Master e alterar o status de uma venda ou editar um cliente.
- [ ] Acessar o banco de dados via DBeaver/PGAdmin e checar a tabela `audit_logs`.
- [ ] O banco deve registrar quem fez a alteração, a rota usada, e os payloads antigos e novos.

## 5. Resiliência a Falhas
- [ ] Derrubar o container do Redis (`docker stop basilea-redis`).
- [ ] Acessar `/api/health`. O sistema deve retornar status `503 Service Unavailable` com a flag `redis: false`.
- [ ] Ligar o Redis novamente (`docker start basilea-redis`), o endpoint deve voltar para `200 OK`.
