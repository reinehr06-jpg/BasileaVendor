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

# Limpar caches (inclusive de pacotes dev)
echo "Limpando caches..."
php artisan optimize:clear || true
php artisan package:discover || true

echo "Verificando variáveis de ambiente..."
echo "APP_ENV: $APP_ENV"
echo "DB_HOST: $DB_HOST"

# Garante APP_KEY (se estiver vazio, gera uma).
php artisan key:generate --force 2>/dev/null || true

# Aguarda o banco ficar pronto e roda as migrations (idempotente).
echo "Aguardando banco e rodando migrations..."
for i in $(seq 1 30); do
  if php artisan migrate --force 2>&1; then
    echo "Migrations aplicadas."
    # Cria o usuário master (seeder idempotente).
    php artisan db:seed --class=CreateAdminUserSeeder --force 2>&1 || true
    break
  fi
  echo "Banco ainda não pronto (tentativa $i/30). Aguardando 2s..."
  sleep 2
done

echo "Iniciando Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
