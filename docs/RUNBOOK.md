# Runbook de Incidentes — Basiléia Vendor OS

Este documento contém procedimentos operacionais para resolver incidentes comuns em produção.

---

## Severidades

| Nível | Descrição | SLA de Resposta | Exemplo |
|-------|-----------|-----------------|---------|
| **SEV-1** | Sistema fora do ar (Down) | 15 minutos | Backend não responde, banco corrompido |
| **SEV-2** | Funcionalidade crítica quebrada | 1 hora | Login não funciona, pagamentos falhando |
| **SEV-3** | Bug não-bloqueante | 4 horas | Gráfico renderizando errado, layout quebrado |
| **SEV-4** | Melhoria / Cosmético | Próximo sprint | Texto com typo, cor desalinhada |

---

## Incidente: Sistema Completamente Fora do Ar (SEV-1)

### Diagnóstico Rápido
```bash
# 1. Verificar se os containers estão rodando
docker-compose ps

# 2. Verificar logs do backend
docker-compose logs --tail=50 backend

# 3. Verificar conectividade com o banco
docker-compose exec backend php artisan tinker --execute="DB::connection()->getPdo()"

# 4. Verificar uso de disco (pode ter lotado)
df -h

# 5. Verificar uso de memória
free -m
```

### Ações de Recuperação
```bash
# Reiniciar todos os serviços
docker-compose down && docker-compose up -d

# Se o banco corrompeu, restaurar backup
bash scripts/restore-database.sh

# Se o disco lotou, limpar logs antigos
docker-compose exec backend find storage/logs -name "*.log" -mtime +7 -delete
```

---

## Incidente: Pagamentos Não Processando (SEV-2)

### Diagnóstico
```bash
# Verificar webhook do Asaas
docker-compose logs backend | grep -i "webhook" | tail -20

# Verificar se a fila está processando
docker-compose exec backend php artisan queue:monitor

# Testar conectividade com Asaas
docker-compose exec backend php artisan tinker --execute="Http::get('https://api.asaas.com/v3/finance/balance')"
```

### Ações
1. Verificar se a `ASAAS_API_KEY` está correta no `.env`.
2. Verificar se o webhook URL no painel Asaas aponta para `https://seudominio.com.br/api/webhooks/asaas`.
3. Reiniciar o worker de filas: `docker-compose exec backend php artisan queue:restart`.

---

## Incidente: Login Não Funciona (SEV-2)

### Diagnóstico
```bash
# Verificar se o Redis está acessível (sessions)
docker-compose exec backend php artisan tinker --execute="Redis::ping()"

# Verificar logs de autenticação
docker-compose logs backend | grep -i "auth\|login\|token" | tail -20
```

### Ações
1. Limpar cache de sessão: `docker-compose exec backend php artisan cache:clear`.
2. Reiniciar Redis: `docker-compose restart redis`.
3. Verificar se `SESSION_DRIVER=redis` está correto no `.env`.

---

## Incidente: Performance Degradada (SEV-3)

### Diagnóstico
```bash
# Verificar queries lentas no Telescope (se habilitado)
docker-compose exec backend php artisan telescope:prune --hours=24

# Verificar uso de CPU/Memória dos containers
docker stats --no-stream

# Verificar conexões ativas no banco
docker-compose exec db psql -U basileia_user -d basileia_prod -c "SELECT count(*) FROM pg_stat_activity;"
```

### Ações
1. Limpar caches: `php artisan config:cache && php artisan route:cache`.
2. Reiniciar workers: `php artisan queue:restart`.
3. Se o banco estiver sobrecarregado, analisar queries com `EXPLAIN ANALYZE`.

---

## Template de Post-Mortem

```markdown
# Post-Mortem: [Título do Incidente]

**Data:** YYYY-MM-DD
**Severidade:** SEV-X
**Duração do Impacto:** XX minutos/horas
**Responsável:** [Nome]

## Resumo
[Breve descrição do que aconteceu]

## Timeline
| Horário | Evento |
|---------|--------|
| HH:MM | Primeiro alerta detectado |
| HH:MM | Equipe acionada |
| HH:MM | Causa raiz identificada |
| HH:MM | Fix aplicado |
| HH:MM | Sistema restaurado |

## Causa Raiz
[Explicação técnica detalhada]

## Impacto
- Usuários afetados: X
- Dados perdidos: Sim/Não
- Receita impactada: R$ X

## Ações Corretivas
- [ ] [Ação 1 — Responsável — Prazo]
- [ ] [Ação 2 — Responsável — Prazo]

## Lições Aprendidas
- [O que funcionou bem]
- [O que pode melhorar]
```
