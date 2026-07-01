<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset automático de comissões de clientes legados no dia 2 de cada mês à meia-noite
Schedule::command('legacy:reset-commissions')->monthlyOn(2, '00:00');

// Expirar vendas automaticamente quando entrarem na última hora (72h)
Schedule::command('vendas:expirar')->hourly();

// Sincronizar vendas pendentes com Asaas a cada 15 minutos
Schedule::command('vendas:sync-pendentes')->everyFifteenMinutes();

// Verificar inadimplência de assinaturas a cada hora
Schedule::command('vendas:verificar-inadimplencia')->hourly();

// Gerar renovações automáticas (Mensais e Anuais) diariamente à meia-noite
Schedule::command('vendas:gerar-renovacoes')->dailyAt('00:00');

// Sincronizar status dos clientes com API Asaas a cada 4 horas
// Consulta diretamente a API do Asaas para determinar status real (ativo/inadimplente) mês a mês
// Edit: Agora roda de madrugada servindo de rede de segurança para clientes sem webhook recente (últimas 24h)
Schedule::command('clientes:sync-asaas --limit=200')->dailyAt('03:00')->withoutOverlapping();

// Processador assíncrono de Webhooks Asaas (Idempotência)
// Roda a cada minuto processando eventos PENDING
Schedule::command('asaas:process-events')->everyMinute()->withoutOverlapping();

