#!/bin/bash
set -e

echo "=== Basileia Vendas - Iniciando ==="

# Gerar .env a partir das variáveis de ambiente do Docker
echo "Gerando .env..."
cat > .env <<EOF
APP_NAME=${APP_NAME:-BasileiaVendas}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-basileia_vendas}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD:-secret}

SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}

LOG_CHANNEL=${LOG_CHANNEL:-errorlog}
LOG_LEVEL=${LOG_LEVEL:-error}
EOF

# Gerar APP_KEY se não foi fornecida
if [ -z "$APP_KEY" ]; then
  echo "Gerando APP_KEY..."
  php artisan key:generate --force
fi

# Aguardar banco de dados
echo "Aguardando banco de dados..."
MAX_RETRIES=30
RETRY=0
until php -r "try { new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-basileia_vendas}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD:-secret}'); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  RETRY=$((RETRY+1))
  if [ $RETRY -ge $MAX_RETRIES ]; then
    echo "ERRO: Banco de dados não disponível após ${MAX_RETRIES} tentativas."
    exit 1
  fi
  echo "Banco não disponível, tentativa $RETRY/$MAX_RETRIES..."
  sleep 2
done
echo "Banco disponível!"

# Rodar migrations
echo "Rodando migrations..."
php artisan migrate --force

# Criar link de storage
php artisan storage:link 2>/dev/null || true

# Limpar e cachear config para produção
echo "Cachear configurações..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Iniciando servidor na porta 8000 ==="
php artisan serve --host=0.0.0.0 --port=8000
