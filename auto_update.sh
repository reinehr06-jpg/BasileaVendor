#!/bin/bash
# Script para automação total: Pull, Update e Teste
echo "🚀 Iniciando Ciclo de Atualização Automática..."

# 1. Pull do GitHub
echo "📥 Buscando alterações no GitHub..."
git pull origin main

# 2. Atualização do Ambiente Local
echo "⚙️ Rodando script de atualização local..."
./basileia-app/update_local.sh

# 3. Testes Automatizados (Laravel)
echo "🧪 Executando testes do sistema..."
cd basileia-app
php artisan test --stop-on-failure
TEST_RESULT=$?
cd ..

if [ $TEST_RESULT -ne 0 ]; then
    echo "❌ FALHA NOS TESTES: O sistema pode estar instável!"
    exit 1
fi

# 4. Health Check (HTTP)
echo "🔍 Verificando integridade da URL local..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/login)

if [ "$STATUS_CODE" -eq 200 ] || [ "$STATUS_CODE" -eq 302 ]; then
    echo "✅ SUCESSO: Sistema atualizado e respondendo corretamente!"
else
    echo "⚠️ AVISO: O servidor local não respondeu como esperado (Status: $STATUS_CODE)."
    echo "Verifique se o processo 'php artisan serve' está rodando."
fi

echo "✨ Ciclo concluído!"
