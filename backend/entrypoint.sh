#!/bin/sh
set -e

# LOG IMEDIATO PARA TESTE
echo "###########################################"
echo "### CONTAINER STARTING - BASILEA VENDOR ###"
echo "###########################################"

cd /var/www/html

# Testar se o nginx está ok
echo "Testando configuração do Nginx..."
nginx -t || echo "AVISO: Erro na configuração do Nginx"

# Garantir pastas de storage
echo "Configurando diretórios..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Link de storage
php artisan storage:link --force 2>/dev/null || true

# Limpar caches
echo "Limpando caches..."
php artisan config:clear || true
php artisan route:clear || true

# Pular espera do banco se quiser testar apenas o boot
echo "Verificando variáveis de ambiente..."
echo "APP_ENV: $APP_ENV"
echo "DB_HOST: $DB_HOST"

echo "Iniciando Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
