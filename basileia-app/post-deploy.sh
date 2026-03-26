#!/bin/bash
# Script de Post-Deploy para Render
# ==========================================
# Este script é executado automaticamente após cada deploy
# Execute: chmod +x post-deploy.sh antes de commitrar

set -e

echo "=========================================="
echo "POST-DEPLOY: Basileia Vendas"
echo "=========================================="

# cd para o diretório da aplicação
cd /var/www/html

# ==========================================
# 1. Migrar banco de dados
# ==========================================
echo "🔄 Rodando migrations..."
php artisan migrate --force --no-interaction

# ==========================================
# 2. Limpar cache
# ==========================================
echo "🧹 Limpando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# ==========================================
# 3. Verificar Queue Worker
# ==========================================
echo "✅ Verificando Queue Worker..."
# O worker já está configurado no render.yaml para iniciar automaticamente

echo "=========================================="
echo "POST-DEPLOY FINALIZADO COM SUCESSO!"
echo "=========================================="