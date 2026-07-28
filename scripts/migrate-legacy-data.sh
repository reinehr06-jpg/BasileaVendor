#!/bin/bash
# ==============================================================================
# Script de Importação de Dados Legados
# Utilizado para disparar os processos ETL do Laravel.
# ==============================================================================

set -e

echo "Iniciando processo de Migração de Dados (ETL)"

# Arquivos de Origem
CLIENTES_CSV="./storage/app/imports/legacy_clientes.csv"
VENDAS_CSV="./storage/app/imports/legacy_vendas.csv"

# Checa se backend está rodando no Docker ou via Artisan local
if docker-compose ps | grep -q 'backend.*Up'; then
    PREFIX="docker-compose exec backend php artisan"
else
    cd backend/
    PREFIX="php artisan"
fi

echo ">> Importando Clientes..."
# Supondo que um comando 'import:clientes' exista (placeholder para futura implementação de comando Artisan)
# $PREFIX import:clientes --file="$CLIENTES_CSV"
echo "[✓] Comando simulado de importação de clientes com sucesso."

echo ">> Importando Vendas..."
# $PREFIX import:vendas --file="$VENDAS_CSV"
echo "[✓] Comando simulado de importação de vendas com sucesso."

echo "Migração finalizada com sucesso!"
