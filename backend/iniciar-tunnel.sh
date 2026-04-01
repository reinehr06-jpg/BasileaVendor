#!/bin/bash
# ==========================================
# INICIAR TUNNEL (NGROK)
# ==========================================
# Para ter URL pública do localhost

echo "=========================================="
echo "INICIANDO TUNNEL PÚBLICO..."
echo "=========================================="

ngrok http 8000 --host-header=localhost:8000