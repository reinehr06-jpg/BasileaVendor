<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vendedor;
use App\Services\Chat\ChatDistributionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PopularFilaChatCommand extends Command
{
    protected $signature = 'chat:popular-fila {--gestor= : ID do gestor específico} {--dry-run : Simular sem executar}';
    protected $description = 'Popula a fila round-robin com vendedores ativos';

    protected $distributionService;

    public function __construct()
    {
        parent::__construct();
        $this->distributionService = new ChatDistributionService();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $gestorId = $this->option('gestor');

        if (!$this->distributionService->isEnabled()) {
            $this->error('Chat módulo está desativado. Ative com: php artisan chat:enable');
            return 1;
        }

        $query = User::where('perfil', 'gestor');
        if ($gestorId) {
            $query->where('id', $gestorId);
        }

        $gestores = $query->get();

        if ($gestores->isEmpty()) {
            $this->error('Nenhum gestor encontrado.');
            return 1;
        }

        $this->info("Encontrados {$gestores->count()} gestores.");

        foreach ($gestores as $gestor) {
            $vendedores = Vendedor::where('gestor_id', $gestor->id)
                ->where('status', 'ativo')
                ->where('chat_enabled', true)
                ->where('chat_disabled', false)
                ->get();

            if ($vendedores->isEmpty()) {
                $this->warn("Gestor {$gestor->name}: nenhum vendedor ativo encontrado.");
                continue;
            }

            if ($dryRun) {
                $this->line("DRY RUN: Gestor {$gestor->name}: {$vendedores->count()} vendedores seriam adicionados à fila.");
                continue;
            }

            foreach ($vendedores as $index => $vendedor) {
                DB::table('chat_distribuicao_fila')->updateOrInsert(
                    ['gestor_id' => $gestor->id, 'vendedor_id' => $vendedor->id],
                    ['ordem' => $index, 'is_active' => true, 'total_atendidos' => 0]
                );
            }

            $this->info("Gestor {$gestor->name}: {$vendedores->count()} vendedores adicionados à fila.");

            Log::info('ChatFila: Popularizada', [
                'gestor_id' => $gestor->id,
                'gestor_nome' => $gestor->name,
                'vendedores' => $vendedores->count()
            ]);
        }

        return 0;
    }
}