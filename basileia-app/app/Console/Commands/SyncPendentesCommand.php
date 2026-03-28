<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Venda, Pagamento};
use App\Services\PagamentoService;
use Illuminate\Support\Facades\Log;

class SyncPendentesCommand extends Command
{
    protected $signature = 'vendas:sync-pendentes 
                            {--vendedor= : Filtrar por ID do vendedor}
                            {--dias=7 : Quantos dias para trás buscar}';

    protected $description = 'Sincroniza vendas pendentes com o Asaas para detectar pagamentos confirmados';

    public function handle()
    {
        $vendedorId = $this->option('vendedor');
        $dias = (int) $this->option('dias');

        $this->info("Sincronizando vendas pendentes dos últimos {$dias} dias...");

        $query = Venda::whereIn('status', ['Aguardando pagamento', 'Vencido'])
            ->where('created_at', '>', now()->subDays($dias))
            ->whereHas('pagamentos', function ($q) {
                $q->whereNotNull('asaas_payment_id');
            })
            ->with(['pagamentos', 'cliente']);

        if ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $vendas = $query->get();

        if ($vendas->isEmpty()) {
            $this->info('Nenhuma venda pendente encontrada.');
            return 0;
        }

        $this->info("Encontradas {$vendas->count()} vendas pendentes.");

        $pagamentoService = new PagamentoService();
        $confirmadas = 0;
        $erros = 0;

        $bar = $this->output->createProgressBar($vendas->count());

        foreach ($vendas as $venda) {
            $pagamento = $venda->pagamentos->first();
            if (!$pagamento || !$pagamento->asaas_payment_id) {
                $bar->advance();
                continue;
            }

            try {
                $foiPago = $pagamentoService->sync($pagamento);
                if ($foiPago) {
                    $confirmadas++;
                    $this->newLine();
                    $this->info("  ✓ Venda #{$venda->id} confirmada - {$venda->cliente->nome_igreja}");
                }
            } catch (\Exception $e) {
                $erros++;
                $this->newLine();
                $this->error("  ✗ Venda #{$venda->id} erro: " . $e->getMessage());
                Log::error('SyncPendentesCommand: erro ao sincronizar', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Resultado:");
        $this->info("  - Confirmadas: {$confirmadas}");
        $this->info("  - Erros: {$erros}");
        $this->info("  - Total processadas: {$vendas->count()}");

        return 0;
    }
}
