#!/bin/bash
set -e

echo "=== Basileia Vendas - Iniciando ==="

# Criar diretórios necessários
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Garantir que diretórios de sessão existem
mkdir -p storage/framework/sessions
chmod -R 777 storage/framework/sessions

# === APP_KEY PERSISTENTE ===
APP_KEY_FILE="/var/www/html/storage/app/.app_key"
if [ -f "$APP_KEY_FILE" ]; then
  APP_KEY=$(cat "$APP_KEY_FILE")
else
  APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
  mkdir -p /var/www/html/storage/app
  echo "$APP_KEY" > "$APP_KEY_FILE"
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
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
LOG_CHANNEL=errorlog
LOG_LEVEL=debug
BCRYPT_ROUNDS=12
EOF

# === Aguardar banco ===
echo "Aguardando banco..."
RETRY=0
until php -r "try { new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-basileia_vendas}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD:-secret}'); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  RETRY=$((RETRY+1))
  if [ $RETRY -ge 30 ]; then exit 1; fi
  sleep 2
done
echo "Banco OK"

# === Limpar caches SEMPRE (auto-healing, sem necessidade de restart) ===
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# === Migrations ===
echo "Migrations..."
php artisan migrate --force --graceful 2>&1 || true

# === SEM route:cache - resolve routes dinamicamente para auto-healing ===
# route:cache congela as rotas e exige restart para atualizar
# Para app deste porte, o overhead é insignificante

echo "=== Servidor na porta 8000 ==="
exec php -d max_execution_time=600 -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8000
