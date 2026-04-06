<?php

namespace App\Console\Commands;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class VerificarInadimplenciaCommand extends Command
{
    protected $signature = 'vendas:verificar-inadimplencia';
    protected $description = 'Verifica vendas com vencimento vencido e marca inadimplentes ou reativa';

    public function handle(): int
    {
        $this->info('Verificando inadimplência...');

        $service = new SubscriptionLifecycleService();
        $resultado = $service->verificarInadimplencia();

        $this->info("Verificadas: {$resultado['verificadas']}");
        $this->info("Marcadas inadimplentes: {$resultado['marcadas_inadimplentes']}");
        $this->info("Reativadas: {$resultado['reativadas']}");

        return Command::SUCCESS;
    }
}
