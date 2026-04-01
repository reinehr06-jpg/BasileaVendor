#!/bin/bash
# ==========================================
# Ngrok Tunnel - Simplificado
# Gera URL pública para localhost:8000
# ==========================================

echo "Iniciando ngrok na porta 8000..."

# Verificar se ngrok está instalado
if ! command -v ngrok &> /dev/null; then
    echo "ERRO: ngrok não está instalado."
    echo "Instale com: brew install ngrok"
    echo "Ou baixe em: https://ngrok.com/download"
    exit 1
fi

# Iniciar ngrok
ngrok http 8000 --host-header="localhost:8000"