#!/bin/bash
# ==============================================================================
# Script de Rollback — Basiléia Vendor OS
# Reverte o deploy para a versão anterior em caso de falha crítica.
# ==============================================================================

set -e

echo "🔄 Iniciando processo de Rollback..."

# Diretório do projeto
PROJECT_DIR="${PROJECT_DIR:-/opt/basileia}"
cd "$PROJECT_DIR"

# 1. Identificar a versão atual e a anterior
CURRENT_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "sem-tag")
PREVIOUS_COMMIT=$(git log --oneline -2 --format="%H" | tail -1)

echo "  Versão atual: $CURRENT_TAG"
echo "  Revertendo para commit: $PREVIOUS_COMMIT"

# 2. Confirmar com o operador
read -p "  Deseja continuar com o rollback? (s/N): " CONFIRM
if [[ "$CONFIRM" != "s" && "$CONFIRM" != "S" ]]; then
    echo "  Rollback cancelado pelo operador."
    exit 0
fi

# 3. Reverter código para o commit anterior
echo "  >> Revertendo código-fonte..."
git checkout "$PREVIOUS_COMMIT"

# 4. Rebuild dos containers
echo "  >> Reconstruindo containers Docker..."
docker-compose down
docker-compose up -d --build

# 5. Aguardar inicialização (30s)
echo "  >> Aguardando serviços subirem..."
sleep 30

# 6. Rodar migrações reversas (se necessário)
echo "  >> Revertendo última migração do banco..."
docker-compose exec -T backend php artisan migrate:rollback --step=1 --force

# 7. Limpar caches
echo "  >> Limpando caches..."
docker-compose exec -T backend php artisan config:cache
docker-compose exec -T backend php artisan route:cache
docker-compose exec -T backend php artisan cache:clear

# 8. Verificar saúde
echo "  >> Verificando saúde do sistema..."
HEALTH_RESPONSE=$(docker-compose exec -T backend php artisan tinker --execute="echo 'OK';" 2>&1)

if echo "$HEALTH_RESPONSE" | grep -q "OK"; then
    echo "✅ Rollback concluído com sucesso!"
    echo "   Sistema restaurado para o commit: $PREVIOUS_COMMIT"
else
    echo "❌ ATENÇÃO: O sistema pode estar instável após o rollback."
    echo "   Verifique os logs: docker-compose logs --tail=50 backend"
    exit 1
fi
