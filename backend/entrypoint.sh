#!/bin/bash
set -e

echo "=== Basileia Vendas - Iniciando ==="

# Criar diretórios necessários
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# === APP_KEY PERSISTENTE ===
APP_KEY_FILE="/var/www/html/storage/app/.app_key"
if [ -f "$APP_KEY_FILE" ]; then
  APP_KEY=$(cat "$APP_KEY_FILE")
  echo "APP_KEY reutilizada."
else
  APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
  echo "$APP_KEY" > "$APP_KEY_FILE"
  echo "APP_KEY gerada e salva."
fi

# === Gerar .env ===
cat > .env <<EOF
APP_NAME=BasileiaVendas
APP_ENV=production
APP_DEBUG=true
APP_URL=${APP_URL:-https://vendor.basileia.global}
APP_KEY=${APP_KEY}
DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-basileia_vendas}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD:-secret}
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
LOG_CHANNEL=errorlog
LOG_LEVEL=debug
BCRYPT_ROUNDS=12
EOF

echo ".env gerado."

# === Aguardar banco ===
echo "Aguardando banco..."
MAX_RETRIES=30
RETRY=0
until php -r "try { new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-basileia_vendas}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD:-secret}'); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  RETRY=$((RETRY+1))
  if [ $RETRY -ge $MAX_RETRIES ]; then
    echo "ERRO: Banco não disponível."
    exit 1
  fi
  sleep 2
done
echo "Banco disponível!"

# === Limpar caches ===
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# === Migrations ===
echo "Rodando migrations..."
php artisan migrate --force --graceful 2>&1 || true

# === Storage link ===
php artisan storage:link 2>/dev/null || true

# === ADMIN - Script PHP puro (sem shell expansion) ===
echo "Garantindo admin..."
php /var/www/html/ensure_admin.php 2>&1

# === Caches ===
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

echo "=== Servidor na porta 8000 ==="
echo "=== Login: basileia.vendas@basileia.com ==="
exec php artisan serve --host=0.0.0.0 --port=8000
