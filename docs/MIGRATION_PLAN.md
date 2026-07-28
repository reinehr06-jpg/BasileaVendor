# Plano de Migração de Dados (Legacy ETL)

Este documento define a estratégia (Extract, Transform, Load) para migrar dados históricos do seu sistema legado ou planilhas para o banco de dados PostgreSQL do Basiléia Vendor OS, sem corromper a estrutura e respeitando a arquitetura Multi-Tenant e restrições da LGPD.

## 1. Extract (Extração)
Os dados devem ser exportados do sistema antigo nos seguintes formatos aceitos:
- `.csv` (Recomendado - UTF-8 separado por vírgula)
- `.json` (Para estruturas aninhadas complexas)

Aconselhamos dividir as exportações em duas planilhas mestre:
1. `legacy_clientes.csv`: Dados de contato e instituição.
2. `legacy_vendas.csv`: Histórico de faturamento.

## 2. Transform (Transformação)
Antes da injeção, os dados precisam ser sanitizados (limpos):
- **Documentos (CPF/CNPJ):** Remover pontuações, traços e espaços, mantendo apenas números (ex: `12345678900`).
- **Telefones:** Formato DDI+DDD+NUMERO (ex: `+5511999998888`).
- **Nulos e Vazios:** Preencher campos obrigatórios que o sistema legado permitia ficar nulos (como "Endereço") com um fallback (ex: "Não Informado").
- **Datas:** Todas as datas devem ser formatadas no padrão ISO 8601 (`YYYY-MM-DD HH:MM:SS`).

## 3. Load (Carga)
O processo de carga deve ocorrer em um ambiente **Staging** primeiro.
A injeção dos dados no banco será feita preferencialmente por **comandos Artisan (Console)** e não via rotas HTTP, para evitar timeouts do servidor Nginx.

### Sequência de Inserção:
1. **Usuários (Vendedores/Gestores):** Migrar a equipe primeiro para obtermos os IDs.
2. **Clientes:** Migrar os clientes (eles precisam do ID do Vendedor que os atendeu).
3. **Vendas/Assinaturas:** Migrar o histórico financeiro amarrando o `cliente_id` e o `vendedor_id`.

## 4. Rollback (Em caso de Falha)
A migração **deve** ocorrer dentro de uma transação de Banco de Dados (`DB::beginTransaction()`). Se na linha 15.000 ocorrer um erro de violação de chave estrangeira, todo o processo sofrerá um `DB::rollBack()`, desfazendo o que foi injetado para não deixar o banco sujo pela metade.

---
*Para executar a migração via linha de comando, utilize o script `scripts/migrate-legacy-data.sh` preparado nesta fase.*
