<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset automático de comissões de clientes legados no dia 2 de cada mês à meia-noite
Schedule::command('legacy:reset-commissions')->monthlyOn(2, '00:00');

// Expirar vendas automaticamente quando entrarem na última hora (71h)
Schedule::command('vendas:expirar')->hourly();

