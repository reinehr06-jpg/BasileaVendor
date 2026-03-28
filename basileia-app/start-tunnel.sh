#!/bin/bash
# ==========================================
# Tunnel Localhost para URL Pública
# Suporta: ngrok, cloudflared, localhost.run
# ==========================================

echo "=========================================="
echo "TUNNEL LOCALHOST - Basileia Vendas"
echo "=========================================="
echo ""
echo "Escolha o serviço de tunnel:"
echo "1) ngrok (Recomendado - melhor estabilidade)"
echo "2) cloudflare (Gratuito - boa opção)"
echo "3) localhost.run (Simples - sem installation)"
echo ""
read -p "Digite o número (1-3): " escolha

case $escolha in
    1) 
        echo ""
        echo "Iniciando ngrok..."
        if command -v ngrok &> /dev/null; then
            ngrok http 8000
        else
            echo "ngrok não instalado. Instalando..."
            brew install ngrok
            ngrok http 8000
        fi
        ;;
    2)
        echo ""
        echo "Iniciando Cloudflare Tunnel..."
        if command -v cloudflared &> /dev/null; then
            cloudflared tunnel --url http://localhost:8000
        else
            echo "cloudflared não instalado. Instalando..."
            brew install cloudflared
            cloudflared tunnel --url http://localhost:8000
        fi
        ;;
    3)
        echo ""
        echo "Iniciando localhost.run..."
        ssh -R 80:localhost:8000 localhost.run
        ;;
    *)
        echo "Opção inválida. Usando ngrok..."
        ngrok http 8000
        ;;
esac