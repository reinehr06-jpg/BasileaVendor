#!/bin/bash
# ==========================================
# INICIAR TUDO - Basileia Vendas
# ==========================================
# Executa isso e pronto!

echo "=========================================="
echo "INICIANDO BASILEIA VENDAS..."
echo "=========================================="

# Ir para pasta do projeto
cd /Users/viniciusreinehr/.gemini/antigravity/scratch/BasileiaVendas/basileia-app

echo ""
echo "1️⃣  Iniciando servidor Laravel..."
php artisan serve --port=8000 &
SERVER_PID=$!

echo " Servidor iniciado!"
echo " Acesse: http://localhost:8000"
echo ""

# Pequena pausa
sleep 2

echo "2️⃣  Abrindo navegador..."
open http://localhost:8000

echo ""
echo "=========================================="
echo "✅ PRONTO! Sistema rodando:"
echo "=========================================="
echo "🌐 http://localhost:8000"
echo ""
echo "Para PARAR o servidor:"
echo "  kill $SERVER_PID"
echo "=========================================="