<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Venda, Pagamento};
use App\Services\AsaasService;
use Illuminate\Support\Facades\Log;

class ExpirarVendasCommand extends Command
{
    protected $signature = 'vendas:expirar 
                            {--dry-run : Apenas simula sem cancelar no Asaas}';

    protected $description = 'Expira vendas com mais de 71h (última hora) e cancela cobranças no Asaas automaticamente';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limite = now()->subHours(71);

        $this->info("Buscando vendas 'Aguardando pagamento' com mais de 71h...");

        $vendas = Venda::where('status', 'Aguardando pagamento')
            ->where('created_at', '<', $limite)
            ->with(['pagamentos', 'cliente'])
            ->get();

        if ($vendas->isEmpty()) {
            $this->info('Nenhuma venda para expirar.');
            return 0;
        }

        $this->warn("Encontradas {$vendas->count()} vendas para expirar.");

        $expiradas = 0;
        $canceladasAsaas = 0;
        $erros = 0;

        $bar = $this->output->createProgressBar($vendas->count());

        foreach ($vendas as $venda) {
            try {
                if (!$dryRun) {
                    $this->cancelarNoAsaas($venda);
                    $canceladasAsaas++;
                }

                $venda->update([
                    'status' => 'Expirado',
                    'checkout_status' => 'EXPIRADO',
                ]);

                Pagamento::where('venda_id', $venda->id)
                    ->where('status', 'pendente')
                    ->update(['status' => 'vencido']);

                Log::info("Venda #{$venda->id} expirada automaticamente (71h+)", [
                    'cliente' => $venda->cliente?->nome_igreja ?? 'N/A',
                    'valor' => $venda->valor,
                    'dry_run' => $dryRun,
                ]);

                $expiradas++;
            } catch (\Exception $e) {
                $erros++;
                $this->newLine();
                $this->error("  ✗ Venda #{$venda->id} erro: " . $e->getMessage());
                Log::error('ExpirarVendasCommand: erro ao expirar', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Resultado:");
        $this->info("  - Expiradas: {$expiradas}");
        $this->info("  - Canceladas no Asaas: {$canceladasAsaas}");
        $this->info("  - Erros: {$erros}");

        if ($dryRun) {
            $this->warn('DRY RUN: Nenhuma ação real foi executada no Asaas.');
        }

        return 0;
    }

    private function cancelarNoAsaas(Venda $venda): void
    {
        try {
            $asaas = new AsaasService;

            $installmentId = $venda->asaas_installment_id;

            if (!$installmentId && $venda->modo_cobranca_asaas === 'INSTALLMENT') {
                $primeiroPagamento = $venda->pagamentos->first();
                if ($primeiroPagamento && $primeiroPagamento->asaas_payment_id) {
                    try {
                        $paymentInfo = $asaas->getPayment($primeiroPagamento->asaas_payment_id);
                        if (!empty($paymentInfo['installment'])) {
                            $installmentId = $paymentInfo['installment'];
                            $venda->update(['asaas_installment_id' => $installmentId]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Falha ao buscar installment ID', ['venda_id' => $venda->id, 'error' => $e->getMessage()]);
                    }
                }
            }

            if ($installmentId) {
                try {
                    $asaas->requestAsaas('DELETE', "/installments/{$installmentId}");
                    Log::info('Parcelas canceladas no Asaas', ['installment_id' => $installmentId]);
                } catch (\Exception $e) {
                    Log::warning('Falha ao cancelar installment', ['installment_id' => $installmentId, 'error' => $e->getMessage()]);
                }
            }

            if ($venda->asaas_subscription_id) {
                try {
                    $asaas->requestAsaas('DELETE', "/subscriptions/{$venda->asaas_subscription_id}");
                    Log::info('Assinatura cancelada no Asaas', ['subscription_id' => $venda->asaas_subscription_id]);
                } catch (\Exception $e) {
                    Log::warning('Falha ao cancelar assinatura', ['subscription_id' => $venda->asaas_subscription_id, 'error' => $e->getMessage()]);
                }
            }

            foreach ($venda->pagamentos as $pagamento) {
                if ($pagamento->asaas_payment_id && !in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED', 'PAGO'])) {
                    try {
                        $asaas->requestAsaas('DELETE', "/payments/{$pagamento->asaas_payment_id}");
                        Log::info('Pagamento cancelado no Asaas', ['payment_id' => $pagamento->asaas_payment_id]);
                    } catch (\Exception $e) {
                        try {
                            $asaas->requestAsaas('POST', "/payments/{$pagamento->asaas_payment_id}/cancel");
                            Log::info('Pagamento cancelado via POST /cancel', ['payment_id' => $pagamento->asaas_payment_id]);
                        } catch (\Exception $e2) {
                            Log::warning('Falha ao cancelar pagamento', [
                                'payment_id' => $pagamento->asaas_payment_id,
                                'delete_error' => $e->getMessage(),
                                'cancel_error' => $e2->getMessage(),
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar no Asaas', ['venda_id' => $venda->id, 'error' => $e->getMessage()]);
        }
    }
}
