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

# Garante APP_KEY: só gera se não estiver definida via variável de ambiente.
# ATENÇÃO: se APP_KEY for regenerada, todos os tokens de criptografia (2FA,
# recovery codes) ficam inválidos. Em produção sempre defina APP_KEY como
# variável de ambiente fixa no painel de deploy.
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
  if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERRO FATAL: APP_KEY não configurada em produção. Configure a variável de ambiente APP_KEY."
    echo "Gerar uma nova APP_KEY invalidaria todos os tokens de criptografia (2FA, recovery codes, sessões)."
    exit 1
  fi
  echo "APP_KEY configurada para produção."
else
  if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "APP_KEY não definida — gerando uma nova (ambiente: ${APP_ENV:-local})."
    php artisan key:generate --force 2>/dev/null || true
  else
    echo "APP_KEY já definida via ambiente — pulando key:generate."
  fi
fi

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

# Cache de produção: compila config e rotas em arquivo único. Sem isto o Laravel
# reprocessa toda a config e as ~140 rotas a cada request (CPU/memória extras).
# Em dev deixamos sem cache para o hot-reload funcionar.
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
  echo "Gerando caches de produção (config/route/view/event)..."
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
  php artisan event:cache || true
fi

echo "Iniciando Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
