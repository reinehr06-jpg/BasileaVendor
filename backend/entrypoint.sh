#!/bin/sh
set -e

echo "--- Iniciando Entrypoint Basilea ---"
cd /var/www/html

# Garantir pastas de storage
echo "Configurando diretórios de storage..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Link de storage
php artisan storage:link --force 2>/dev/null || true

# Limpar caches para evitar rotas corrompidas
echo "Limpando caches do Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Teste de conexão com o banco
DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}
echo "Testando conexão com o banco em $DB_HOST:$DB_PORT..."

for i in $(seq 1 15); do
    if php -r "try { new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch(Exception \$e) { echo \$e->getMessage(); exit(1); }" 2>&1; then
        echo "Banco de dados conectado com sucesso!"
        break
    else
        echo "Tentativa $i: Banco ainda não disponível..."
        sleep 2
    fi
done

# Migrações
echo "Executando migrações..."
php artisan migrate --force --no-interaction || echo "Aviso: Migrações falharam ou já estavam prontas."

echo "Iniciando Supervisor (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
