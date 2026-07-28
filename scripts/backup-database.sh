#!/bin/bash
# ==============================================================================
# Script de Backup Automático - Basiléia Vendor OS
# ==============================================================================

set -e

# Configurações
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${PROJECT_DIR}/backups"
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
FILE_NAME="basileia_vendas_${DATE}.sql.gz"
BACKUP_PATH="${BACKUP_DIR}/${FILE_NAME}"
RETENTION_DAYS=7

# Garante que o diretório de backups existe
mkdir -p "$BACKUP_DIR"

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Iniciando backup do banco de dados..."

# Nome do container pode variar, no nosso docker-compose é "postgres" mas
# o prefixo do projeto é importante. O comando abaixo pega o container em execução
CONTAINER_NAME=$(docker ps --format '{{.Names}}' | grep postgres | head -n 1)

if [ -z "$CONTAINER_NAME" ]; then
    echo "ERRO: Nenhum container do PostgreSQL encontrado rodando."
    exit 1
fi

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Container encontrado: $CONTAINER_NAME"

# Realiza o dump e compacta (assumindo usuário postgres e db basileia_vendas, fallback laravel)
DB_USER=${DB_USERNAME:-postgres}
DB_NAME=${DB_DATABASE:-basileia_vendas}

docker exec "$CONTAINER_NAME" pg_dump -U "$DB_USER" -d "$DB_NAME" | gzip > "$BACKUP_PATH"

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Backup concluído com sucesso: $BACKUP_PATH"

# Limpeza de backups antigos
echo "[$(date +'%Y-%m-%d %H:%M:%S')] Removendo backups mais velhos que $RETENTION_DAYS dias..."
find "$BACKUP_DIR" -name "*.sql.gz" -type f -mtime +$RETENTION_DAYS -exec rm -f {} \;

echo "[$(date +'%Y-%m-%d %H:%M:%S')] Processo finalizado."
