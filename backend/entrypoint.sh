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

echo "APP_URL=${APP_URL:-https://vendor.basileia.global}" >> .env

# Gerar APP_KEY
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

echo ".env gerado!"

# Aguardar banco de dados
echo "Aguardando banco de dados..."
MAX_RETRIES=30
RETRY=0
until php -r "try { new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-basileia_vendas}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD:-secret}'); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  RETRY=$((RETRY+1))
  if [ $RETRY -ge $MAX_RETRIES ]; then
    echo "ERRO: Banco não disponível após ${MAX_RETRIES} tentativas."
    exit 1
  fi
  echo "Banco não disponível, tentativa $RETRY/$MAX_RETRIES..."
  sleep 2
done
echo "Banco disponível!"

# Limpar caches antigos
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Verificar se a tabela users existe (indicador de migrations já rodaram)
TABLE_EXISTS=$(php -r "
try {
  \$pdo = new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-basileia_vendas}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD:-secret}');
  \$stmt = \$pdo->query(\"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name='users')\");
  echo \$stmt->fetchColumn() ? '1' : '0';
} catch (Exception \$e) { echo '0'; }
" 2>/dev/null)

if [ "$TABLE_EXISTS" = "1" ]; then
  echo "Tabelas existem. Rodando migrations pendentes..."
  php artisan migrate --force --graceful 2>&1 || echo "AVISO: Algumas migrations falharam, continuando..."
else
  echo "Primeiro deploy. Criando todas as tabelas..."
  php artisan migrate --force 2>&1
  if [ $? -ne 0 ]; then
    echo "Migration falhou. Tentando migrate:fresh..."
    php artisan migrate:fresh --force 2>&1
  fi
fi

# Criar link de storage
php artisan storage:link 2>/dev/null || true

# SEMPRE rodar o seeder para garantir o admin
echo "Rodando seeder do admin..."
php artisan db:seed --class=DatabaseSeeder --force 2>&1 || echo "AVISO: Seeder falhou"

# Verificar admin
echo "Verificando admin..."
php artisan tinker --execute="try { \$u = \App\Models\User::where('email', 'basileia.vendas@basileia.com')->first(); echo \$u ? 'Admin OK: ' . \$u->email : 'Admin NAO ENCONTRADO'; } catch (\Exception \$e) { echo 'Erro: ' . \$e->getMessage(); }" 2>&1 || true

# Caches de produção
echo "Cachear configurações..."
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

echo "=== Iniciando servidor na porta 8000 ==="
echo "=== Login: basileia.vendas@basileia.com ==="
exec php artisan serve --host=0.0.0.0 --port=8000
