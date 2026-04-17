<?php

namespace App\Console\Commands;

use App\Models\ChatConversa;
use App\Services\Chat\ChatDistributionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChatInatividadeCommand extends Command
{
    protected $signature = 'chat:inatividade {--dry-run : Simular sem executar}';
    protected $description = 'Verifica conversas inativas e repassa: 30min se nunca teve contato, 60min se já teve contato';

    protected $distributionService;

    public function __construct()
    {
        parent::__construct();
        $this->distributionService = new ChatDistributionService();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Iniciando verificação de inatividade...');
        
        $conversasInativas = ChatConversa::where('status', 'aberta')
            ->whereNotNull('vendedor_id')
            ->whereNotNull('last_inbound_at')
            ->where(function ($query) {
                $query->whereNull('last_outbound_at')
                    ->orWhereColumn('last_outbound_at', '<=', 'last_inbound_at');
            })
            ->get();

        $this->info("Encontradas {$conversasInativas->count()} conversas com mensagens sem resposta");

        $transferidas = 0;
        $falhas = 0;

        foreach ($conversasInativas as $conversa) {
            try {
                $primeiroContato = $conversa->first_response_at === null;
                $limiteMinutos = $primeiroContato ? 30 : 60;
                
                $minutosSemResposta = now()->diffInMinutes($conversa->last_inbound_at);
                
                if ($minutosSemResposta < $limiteMinutos) {
                    continue;
                }

                if ($dryRun) {
                    $tipo = $primeiroContato ? 'primeiro contato (>30min)' : 'já atendidas (>60min)';
                    $this->line("DRY RUN: Transferiria conversa #{$conversa->id} por inatividade ({$tipo})");
                    $transferidas++;
                    continue;
                }

                $result = $this->distributionService->handleInatividade($conversa);
                
                if ($result) {
                    $tipo = $primeiroContato ? 'primeiro contato' : 'já atendidas';
                    $this->line("✓ Conversa #{$conversa->id} transferida por inatividade ({$tipo})");
                    $transferidas++;
                } else {
                    $this->line("✗ Conversa #{$conversa->id} não transferida");
                    $falhas++;
                }

            } catch (\Exception $e) {
                $this->error("Erro ao processar conversa #{$conversa->id}: {$e->getMessage()}");
                $falhas++;
            }
        }

        $this->info("Resumo: {$transferidas} transferidas, {$falhas} falhas");

        return $falhas > 0 ? 1 : 0;
    }
}