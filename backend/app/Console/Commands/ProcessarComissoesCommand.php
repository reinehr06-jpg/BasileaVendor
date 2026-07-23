<?php

namespace App\Console\Commands;

use App\Models\Pagamento;
use App\Services\Commission\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Motor NOTURNO de comissão.
 *
 * Roda de madrugada (após a sincronização com o Asaas) e varre todos os
 * pagamentos JÁ CONFIRMADOS que ainda não geraram comissão, gerando cada uma
 * pelo motor único. Como a geração é idempotente (trava por pagamento_id),
 * rodar quantas vezes for necessário é seguro.
 *
 * A regra do "fim do mês" (recorrência só conta se paga até o último dia do
 * mês do vencimento) é aplicada dentro do motor único.
 */
class ProcessarComissoesCommand extends Command
{
    protected $signature = 'comissoes:processar {--dias=45 : Janela de dias para trás a considerar} {--dry-run : Só simula, não grava}';

    protected $description = 'Gera comissões (inicial/recorrência/antecipada) para pagamentos confirmados que ainda não têm comissão.';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');
        $dryRun = (bool) $this->option('dry-run');

        // Pagamentos confirmados, com data de pagamento, dentro da janela,
        // vinculados a uma venda com vendedor, e SEM comissão ainda.
        $query = Pagamento::query()
            ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO'])
            ->whereNotNull('data_pagamento')
            ->whereNotNull('venda_id')
            ->when($dias > 0, fn ($q) => $q->where('data_pagamento', '>=', now()->subDays($dias)))
            ->whereDoesntHave('comissoes')
            // Vendas legadas do Asaas têm o retroativo gerado pelo importador
            // (gerarComissoesHistoricas). Excluímos aqui para não contar em dobro.
            ->where(function ($q) {
                $q->whereNull('asaas_payment_id')
                  ->orWhere('asaas_payment_id', 'not like', 'legacy\_%');
            })
            ->whereDoesntHave('venda', fn ($q) => $q->where('origem', 'asaas_legado'))
            ->orderBy('data_pagamento', 'asc');

        $total = 0;
        $geradas = 0;
        $puladas = 0;

        $query->chunkById(200, function ($pagamentos) use (&$total, &$geradas, &$puladas, $dryRun) {
            foreach ($pagamentos as $pagamento) {
                $total++;

                if ($dryRun) {
                    $this->line("[dry-run] Pagamento #{$pagamento->id} (venda {$pagamento->venda_id}) seria processado.");
                    continue;
                }

                try {
                    $res = CommissionService::gerarParaPagamento($pagamento);
                    if (! empty($res['gerou'])) {
                        $geradas++;
                        $this->info("Pagamento #{$pagamento->id}: {$res['tipo']} vend R$ {$res['valor_vendedor']} / gestor R$ {$res['valor_gestor']}");
                    } else {
                        $puladas++;
                    }
                } catch (\Throwable $e) {
                    $puladas++;
                    Log::error('[comissoes:processar] Erro ao processar pagamento', [
                        'pagamento_id' => $pagamento->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Concluído. Analisados: {$total} | Geradas: {$geradas} | Puladas: {$puladas}");

        return Command::SUCCESS;
    }
}
