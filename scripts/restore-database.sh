#!/bin/bash
# ==============================================================================
# Script de Restauração de Banco de Dados - Basiléia Vendor OS
# ==============================================================================

set -e

if [ -z "$1" ]; then
    echo "ERRO: Caminho do arquivo de backup (.sql.gz) não fornecido."
    echo "Uso: ./restore-database.sh caminho/do/arquivo/backup.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERRO: O arquivo '$BACKUP_FILE' não foi encontrado."
    exit 1
fi

echo "=============================================================================="
echo "⚠️  ATENÇÃO: Você está prestes a sobrescrever o banco de dados atual!"
echo "Isso destruirá todos os dados atuais e os substituirá pelos dados do backup."
echo "Backup selecionado: $BACKUP_FILE"
echo "=============================================================================="
read -p "Tem certeza absoluta? Digite 'SIM' para confirmar: " CONFIRM

if [ "$CONFIRM" != "SIM" ]; then
    echo "Operação abortada."
    exit 0
fi

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Iniciando restauração do banco de dados..."

# Nome do container pode variar, no nosso docker-compose é "postgres" mas
# o prefixo do projeto é importante. O comando abaixo pega o container em execução
CONTAINER_NAME=$(docker ps --format '{{.Names}}' | grep postgres | head -n 1)

if [ -z "$CONTAINER_NAME" ]; then
    echo "ERRO: Nenhum container do PostgreSQL encontrado rodando."
    exit 1
fi

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Container encontrado: $CONTAINER_NAME"

DB_USER=${DB_USERNAME:-postgres}
DB_NAME=${DB_DATABASE:-basileia_vendas}

# Extrai e injeta no container
# O drop db e create db previne resíduos
echo "Recriando banco de dados para evitar conflitos..."
docker exec "$CONTAINER_NAME" dropdb -U "$DB_USER" --if-exists "$DB_NAME" || true
docker exec "$CONTAINER_NAME" createdb -U "$DB_USER" "$DB_NAME"

echo "Aplicando os dados do backup..."
gunzip -c "$BACKUP_FILE" | docker exec -i "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME"

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Restauração concluída com sucesso!"
