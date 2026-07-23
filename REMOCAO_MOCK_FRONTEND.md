# Remoção de mock do frontend — status

## Feito nesta rodada (verificado: tsc ✅, build de produção ✅, PHP lint ✅)

### Bugs de produção encontrados e corrigidos (bônus)
- `$user->role` era usado em `MetricasVendasController`, `FinanceiroController` e `VendasController` — mas o campo real é **`perfil`**. Efeito: **gestor/master eram tratados como vendedor** e viam escopo errado (ou nada). Corrigido para `in_array($user->perfil, [...])`.
- `MetricasVendasController` usava `DATE_FORMAT` (MySQL) — o banco é **PostgreSQL**, isso quebra. Reescrito DB-agnóstico (agrupa em PHP).
- Join em `vendedores.user_id` estava errado (a coluna é `usuario_id`). Corrigido.

### Métricas de vendas (as piores telas mock — tinham nomes fake)
- Endpoint `/metricas-vendas` corrigido + agora retorna churn real.
- `gestor/metricas-vendas`: reescrita para dados reais (removidos "Bruno Santana da Hora", "Carolina de Souza" etc., gráfico fake, churn chumbado). Filtros de vendedor/equipe agora carregam de verdade e filtram.
- `gestao-comercial/metricas-vendas`: churn ligado ao real; filtros populados com vendedores/equipes reais.

### KPIs (cards com números chumbados "da imagem")
Backend `FinanceiroController` agora retorna `resumo` com totais reais. Ligados:
- `vendedor/comissoes`, `financeiro/comissoes`, `gestor/comissoes` — nº, total comissão, total vendas, média.
- `vendedor/pagamentos`, `financeiro/pagamentos` — total, pagos, pendentes, recebido.

### Limpeza
- Removidos comentários mortos `// MOCK DATA` e rótulos "Gráfico Fake/Simulado" nas telas vivas.
- MSW (mock service worker) confirmado **inativo** em runtime (não é ligado em nenhum lugar).

## Ainda pendente (precisa de sua priorização)

Estas telas seguem com mock e **precisam de endpoints de backend** ou estão em **rotas mortas** (não aparecem no menu):

1. **Rotas mortas** (não estão no menu — candidatas a remoção): `contabilidade/*`, `analises-e-contabil/*` (dashboards, DRE, relatórios), e formulários da igreja (`UsuarioForm`, `RedeForm`, `MembroForm`, etc. com "Mocked data load for Edit Mode"). Confirmar se podem ser deletadas.
2. **`gestao-financeira/*`** (despesas/nova, receitas/nova, importação, transferências): parcialmente usadas (as telas vivas de `financeiro` linkam para os "nova" aqui). A importação usa `mockOfx` e as telas de despesas/transferências têm gráficos com dados fixos. Precisam de endpoints reais (OFX, séries de despesas/transferências).
3. **`configuracoes/modelos`**: templates mock (svc=0).

## Recomendação
As telas de **dinheiro e comercial** (vendas, comissões, pagamentos, métricas, clientes) estão agora com dados reais. O que resta é sobretudo módulo financeiro-contábil e de igreja. Sugiro: (a) decidir quais rotas duplicadas/mortas deletar, depois (b) eu ligo importação OFX + gráficos de despesas/transferências aos endpoints reais.

> Validação: como não rodo PHP aqui, rode os endpoints (`/metricas-vendas`, `/financeiro/comissoes`, `/financeiro/pagamentos`) uma vez em staging e confira os números antes de produção.
