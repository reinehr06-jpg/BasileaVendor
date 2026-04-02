#!/bin/bash
set -e

echo "=== Basileia Vendas - Iniciando ==="

# Criar diretórios necessários
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Gerar .env completo
cat > .env <<'ENVEOF'
APP_NAME=BasileiaVendas
APP_ENV=production
APP_DEBUG=true
ENVEOF

# Adicionar APP_URL
echo "APP_URL=${APP_URL:-https://vendor.basileia.global}" >> .env

# Gerar APP_KEY sempre (nova a cada deploy, estável durante vida do container)
echo "Gerando APP_KEY..."
APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
echo "APP_KEY=${APP_KEY}" >> .env

# Configurações do banco
cat >> .env <<DBEOF
DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-basileia_vendas}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD:-secret}
DBEOF

# Configurações de sessão/cache/fila
cat >> .env <<APPEOF
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
LOG_CHANNEL=errorlog
LOG_LEVEL=debug
BCRYPT_ROUNDS=12
APPEOF

echo ".env gerado com sucesso!"

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

# Limpar caches
echo "Limpando caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Rodar migrations
echo "Rodando migrations..."
php artisan migrate --force --graceful 2>&1 || echo "AVISO: Algumas migrations podem ter falhado, continuando..."

# Criar link de storage
php artisan storage:link 2>/dev/null || true

# SEMPRE rodar o seeder para garantir o admin com senha correta
echo "Rodando seeder do admin..."
php artisan db:seed --class=DatabaseSeeder --force 2>&1

# Verificar se o admin existe
echo "Verificando usuário admin..."
php artisan tinker --execute="try { \$u = \App\Models\User::where('email', 'basileia.vendas@basileia.com')->first(); echo \$u ? 'Admin OK: ' . \$u->email . ' (perfil: ' . \$u->perfil . ')' : 'Admin NÃO ENCONTRADO'; } catch (\Exception \$e) { echo 'Erro: ' . \$e->getMessage(); }" 2>&1 || true

# Cachear config e routes para produção
echo "Cachear configurações..."
php artisan config:cache 2>&1 || echo "AVISO: config:cache falhou"
php artisan route:cache 2>&1 || echo "AVISO: route:cache falhou"
php artisan view:cache 2>&1 || echo "AVISO: view:cache falhou"

echo "=== Iniciando servidor na porta 8000 ==="
echo "=== Login: basileia.vendas@basileia.com ==="
exec php artisan serve --host=0.0.0.0 --port=8000
