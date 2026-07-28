# Guia de Deploy — Basiléia Vendor OS

Este guia detalha o processo passo-a-passo para fazer o deploy do sistema em produção.

---

## Pré-requisitos

| Componente | Versão Mínima |
|------------|---------------|
| Docker | 24.x |
| Docker Compose | 2.20+ |
| Node.js | 20.x LTS |
| PHP | 8.2+ |
| PostgreSQL | 15+ |
| Redis | 7+ |

## 1. Preparação do Servidor

```bash
# Clonar o repositório
git clone git@github.com:reinehr06-jpg/BasileaVendor.git /opt/basileia
cd /opt/basileia

# Criar arquivo de ambiente a partir do exemplo
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env.local
```

## 2. Configurar Variáveis de Ambiente

Editar `backend/.env` com os valores de produção:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=  # Gerar com: php artisan key:generate
APP_URL=https://seudominio.com.br

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=basileia_prod
DB_USERNAME=basileia_user
DB_PASSWORD=<SENHA_FORTE_AQUI>

REDIS_CLIENT=predis
REDIS_HOST=redis

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

ASAAS_API_KEY=<SUA_CHAVE_ASAAS>
ASAAS_ENVIRONMENT=production

SENTRY_DSN=<SEU_SENTRY_DSN>
```

Editar `frontend/.env.local`:

```env
BACKEND_ORIGIN=http://backend:8000
NEXT_PUBLIC_SENTRY_DSN=<SEU_SENTRY_DSN_FRONTEND>
```

## 3. Build e Start com Docker Compose

```bash
# Subir todos os serviços
docker-compose up -d --build

# Aguardar o banco inicializar (≈30s) e rodar migrações
docker-compose exec backend php artisan migrate --force

# Criar o usuário admin master
docker-compose exec backend php artisan db:seed --class=CreateAdminUserSeeder --force

# Limpar caches
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache
```

## 4. Configurar Nginx (Reverse Proxy)

```nginx
server {
    listen 80;
    server_name seudominio.com.br;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seudominio.com.br;

    ssl_certificate     /etc/letsencrypt/live/seudominio.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seudominio.com.br/privkey.pem;

    # Frontend (Next.js)
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # API Backend (Laravel)
    location /api {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## 5. Verificação Pós-Deploy

```bash
# Checar saúde do backend
curl -s https://seudominio.com.br/api/health | jq .

# Checar logs por erros
docker-compose logs --tail=50 backend
docker-compose logs --tail=50 frontend

# Rodar smoke tests
bash scripts/smoke-tests.sh
```

---

## Troubleshooting

### "502 Bad Gateway"
**Causa:** Backend não iniciou ou caiu.
```bash
docker-compose restart backend
docker-compose logs backend --tail=20
```

### "SQLSTATE Connection refused"
**Causa:** PostgreSQL não está pronto ou credenciais erradas.
```bash
docker-compose exec db pg_isready
docker-compose exec backend php artisan tinker --execute="DB::connection()->getPdo()"
```

### "Class Redis not found"
**Causa:** Extensão phpredis não instalada no container.
**Solução:** Garantir `REDIS_CLIENT=predis` no `.env`.

### "419 CSRF Token Mismatch"
**Causa:** Domínio do frontend diferente do backend sem CORS configurado.
```bash
# Verificar CORS
docker-compose exec backend php artisan config:show cors
```

### Assets estáticos não carregam (CSS/JS)
```bash
docker-compose exec frontend npm run build
docker-compose restart frontend
```
