<?php

namespace App\Services;

use App\Models\Cobranca;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Log;

class ExcluirCobrancaService
{
    /**
     * Excluir uma cobrança específica no Basileia Vendas E no Asaas
     * Retorna array com success e message
     */
    public function executar(int $cobrancaId): array
    {
        $cobranca = Cobranca::find($cobrancaId);

        if (!$cobranca) {
            return [
                'success' => false,
                'message' => 'Cobrança não encontrada',
            ];
        }

        if (!$cobranca->asaas_id) {
            $cobranca->delete();
            return [
                'success' => true,
                'message' => 'Cobrança local excluída (sem ID Asaas)',
            ];
        }

        try {
            $asaas = new AsaasService();
            $resultado = $asaas->deletePayment($cobranca->asaas_id);

            if ($resultado) {
                Log::info('Cobrança excluída no Asaas', [
                    'cobranca_id' => $cobranca->id,
                    'asaas_id' => $cobranca->asaas_id,
                ]);
            }

            $cobranca->delete();

            return [
                'success' => true,
                'message' => 'Cobrança excluída com sucesso no Basileia Vendas e no Asaas',
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao excluir cobrança no Asaas', [
                'cobranca_id' => $cobranca->id,
                'asaas_id' => $cobranca->asaas_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao excluir no Asaas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Excluir um pagamento específico
     */
    public function excluirPagamento(int $pagamentoId): array
    {
        $pagamento = Pagamento::find($pagamentoId);

        if (!$pagamento) {
            return [
                'success' => false,
                'message' => 'Pagamento não encontrado',
            ];
        }

        if (!$pagamento->asaas_payment_id) {
            $pagamento->delete();
            return [
                'success' => true,
                'message' => 'Pagamento local excluído (sem ID Asaas)',
            ];
        }

        try {
            $asaas = new AsaasService();
            $resultado = $asaas->deletePayment($pagamento->asaas_payment_id);

            if ($resultado) {
                Log::info('Pagamento excluído no Asaas', [
                    'pagamento_id' => $pagamento->id,
                    'asaas_payment_id' => $pagamento->asaas_payment_id,
                ]);
            }

            $pagamento->delete();

            return [
                'success' => true,
                'message' => 'Pagamento excluído com sucesso',
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao excluir pagamento no Asaas', [
                'pagamento_id' => $pagamento->id,
                'asaas_payment_id' => $pagamento->asaas_payment_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao excluir no Asaas: ' . $e->getMessage(),
            ];
        }
    }
}