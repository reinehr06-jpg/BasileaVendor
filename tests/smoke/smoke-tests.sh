#!/bin/bash
# ==============================================================================
# Script de Smoke Tests Automatizados - Basiléia Vendor OS
# Objetivo: Garantir que a aplicação recém-deployada está de pé e respondendo.
# ==============================================================================

set -e

# Pega o domínio via argumento, ou assume localhost:8000 se vazio
API_URL=${1:-"http://localhost:8000/api"}

echo "=============================================================================="
echo "🚀 Iniciando Smoke Tests contra: $API_URL"
echo "=============================================================================="

# Cores para o output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

FAILS=0

function run_test() {
    local ENDPOINT=$1
    local EXPECTED_STATUS=$2
    local METHOD=${3:-"GET"}
    local PAYLOAD=$4
    
    echo -n "Testando [$METHOD] $ENDPOINT ... "
    
    if [ "$METHOD" == "GET" ]; then
        HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL$ENDPOINT")
    else
        HTTP_STATUS=$(curl -s -X "$METHOD" -H "Content-Type: application/json" -d "$PAYLOAD" -o /dev/null -w "%{http_code}" "$API_URL$ENDPOINT")
    fi

    if [ "$HTTP_STATUS" == "$EXPECTED_STATUS" ]; then
        echo -e "${GREEN}PASSOU${NC} (Status: $HTTP_STATUS)"
    else
        echo -e "${RED}FALHOU${NC} (Esperado: $EXPECTED_STATUS | Recebido: $HTTP_STATUS)"
        FAILS=$((FAILS+1))
    fi
}

# 1. Healthcheck Geral (deve voltar 200 se Banco e Redis estiverem de pé)
run_test "/health" "200"

# 2. Rota que não existe (deve voltar 404)
run_test "/rota-nao-existe" "404"

# 3. Tentativa de Login Inválido (deve voltar 401 Unauthorized)
run_test "/login" "401" "POST" '{"email":"invalid@test.com","password":"invalidpass"}'

# 4. Acessar Rota Protegida sem Token (deve voltar 401)
run_test "/dashboard" "401" "GET"

echo "=============================================================================="
if [ $FAILS -eq 0 ]; then
    echo -e "${GREEN}✅ Todos os Smoke Tests passaram! A aplicação parece saudável.${NC}"
    exit 0
else
    echo -e "${RED}❌ $FAILS Smoke Test(s) falharam. Verifique os logs da aplicação antes de abrir o tráfego.${NC}"
    exit 1
fi
