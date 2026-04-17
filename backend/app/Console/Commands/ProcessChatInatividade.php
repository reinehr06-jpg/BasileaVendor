<?php

namespace App\Console\Commands;

use App\Services\Chat\ChatDistributionService;
use Illuminate\Console\Command;

class ProcessChatInatividade extends Command
{
    protected $signature = 'chat:process-inatividade {--sla=60 : Minutos SLA para transferência}';
    protected $description = 'Processa conversas que estão há mais de X minutos sem resposta';

    public function __construct(
        protected ChatDistributionService $distributionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $slaMinutes = (int) $this->option('sla');
        
        $this->info("Processando inatividade com SLA de {$slaMinutes} minutos...");
        
        $processed = $this->distributionService->processInatividade($slaMinutes);
        
        $this->info("Processadas {$processed} conversas por inatividade.");
        
        return Command::SUCCESS;
    }
}