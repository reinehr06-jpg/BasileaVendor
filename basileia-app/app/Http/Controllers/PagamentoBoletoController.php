<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Venda;
use App\Services\AsaasService;

class PagamentoBoletoController extends Controller
{
    /**
     * GET /vendedor/vendas/{id}/boleto
     *
     * Busca o URL/dados do boleto gerado no Asaas e retorna JSON.
     */
    /**
     * GET /vendedor/vendas/{id}/boleto
     *
     * Busca o URL/dados do boleto gerado no Asaas e retorna JSON.
     */
    public function download(int $id)
    {
        $user = Auth::user();
        
        // --- Metrificação e Auditoria Inicial ---
        $logContext = [
            'user_id' => $user->id,
            'perfil'  => $user->perfil,
            'venda_id' => $id,
            'ip'      => request()->ip()
        ];

        // Definir escopo de acesso
        $vendedorIds = [];
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$user->vendedor->id ?? 0];
        } elseif ($user->perfil === 'gestor') {
            $vendedorIds = \App\Models\Vendedor::where('gestor_id', $user->id)
                ->orWhere('usuario_id', $user->id)
                ->pluck('id')
                ->toArray();
        } elseif ($user->perfil === 'master') {
            // Adm pode ver todos
            $vendedorIds = null;
        }

        $query = Venda::with(['pagamentos']);
        if ($vendedorIds !== null) {
            $query->whereIn('vendedor_id', $vendedorIds);
        }
        $venda = $query->find($id);

        if (!$venda) {
            Log::warning('[BOLETO_FETCH_FAILED_PERMISSION] Tentativa de acesso não autorizado ou venda inexistente.', $logContext);
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada ou sem permissão de acesso.',
            ], 404);
        }

        $pagamento = $venda->pagamentos->first();

        if (!$pagamento) {
            Log::warning('[BOLETO_FETCH_FAILED_NO_PAYMENT] Venda sem cobrança registrada.', $logContext);
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma cobrança registrada para esta venda.',
            ], 404);
        }

        // -------------------------------------------------------
        // 1. Tenta pegar a URL do boleto de qualquer campo salvo
        // -------------------------------------------------------
        $boletoUrl = $pagamento->bank_slip_url
                  ?? $pagamento->link_pagamento
                  ?? null;
        $linhaDigitavel = $pagamento->linha_digitavel ?? null;

        // -------------------------------------------------------
        // 2. Se tiver asaas_payment_id, SEMPRE busca no Asaas para atualizar dados
        // -------------------------------------------------------
        if ($pagamento->asaas_payment_id) {
            try {
                $startTime = microtime(true);
                $asaas       = new AsaasService();
                $asaasId = $pagamento->asaas_payment_id;

                // Se for uma subscription (começa com sub_), buscar o primeiro pagamento
                if (str_starts_with($asaasId, 'sub_')) {
                    $paymentsResponse = $asaas->requestAsaas('GET', "/subscriptions/{$asaasId}/payments");
                    if (!empty($paymentsResponse['data']) && count($paymentsResponse['data']) > 0) {
                        $paymentData = $paymentsResponse['data'][0];
                        if (!empty($paymentData['id'])) {
                            $pagamento->asaas_payment_id = $paymentData['id'];
                        }
                    } else {
                        $paymentData = null;
                    }
                } else {
                    $paymentData = $asaas->getPayment($asaasId);
                }

                $duration = round((microtime(true) - $startTime) * 1000, 2);

                if ($paymentData) {
                    $logContext['asaas_status'] = $paymentData['status'] ?? 'unknown';
                    $logContext['duration_ms']  = $duration;

                    // Atualiza URLs do boleto (Self-healing)
                    $oldUrl = $boletoUrl;
                    if (!empty($paymentData['bankSlipUrl'])) {
                        $pagamento->bank_slip_url = $paymentData['bankSlipUrl'];
                        $boletoUrl = $paymentData['bankSlipUrl'];
                    }
                    if (!empty($paymentData['invoiceUrl'])) {
                        $pagamento->invoice_url = $paymentData['invoiceUrl'];
                    }
                    if (!empty($paymentData['transactionReceiptUrl'])) {
                        $pagamento->link_pagamento = $paymentData['transactionReceiptUrl'];
                    }

                    if ($oldUrl !== $boletoUrl) {
                        Log::info('[BOLETO_FETCH_RECOVERED_BY_SELF_HEALING] Link do boleto atualizado via Asaas API.', $logContext);
                    }

                    // Atualiza linha digitável
                    if (!empty($paymentData['identificationField'])) {
                        $linhaDigitavel = $paymentData['identificationField'];
                        $pagamento->linha_digitavel = $linhaDigitavel;
                    }

                    // Sincronização de Status (Self-healing)
                    $statusAsaas = $paymentData['status'] ?? '';
                    if (in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']) && $pagamento->status !== 'RECEIVED') {
                        $pagamento->status = 'RECEIVED';
                        $pagamento->data_pagamento = now();
                        $pagamento->save();

                        if ($venda->status !== 'PAGO') {
                            $pagamentoService = new \App\Services\PagamentoService();
                            $pagamentoService->confirmarPagamento($pagamento, $paymentData);
                            $venda->refresh();
                        }
                        Log::info('[BOLETO_STATUS_SYNCED] Status sincronizado com Asaas (Pago).', $logContext);
                    } else {
                        $pagamento->save();
                    }

                    // Fallback para Link de Fatura
                    if (!$boletoUrl && !empty($paymentData['invoiceUrl'])) {
                        $boletoUrl = $paymentData['invoiceUrl'];
                    }
                }
            } catch (\Exception $e) {
                Log::error('[BOLETO_FETCH_FAILED_ASAAS_API] Erro de comunicação com o Asaas.', array_merge($logContext, ['error' => $e->getMessage()]));
            }
        }

        // -------------------------------------------------------
        // 3. Resultado Final
        // -------------------------------------------------------
        if (!$boletoUrl) {
            Log::warning('[BOLETO_FETCH_FAILED_NOT_READY] Cobrança existente mas boleto ainda não gerado pelo Asaas.', $logContext);
            return response()->json([
                'success' => false,
                'message' => 'Este boleto ainda está sendo processado pelo Asaas. Tente novamente em 30 segundos.',
            ], 404);
        }

        Log::info('[BOLETO_FETCH_SUCCESS] Link de boleto entregue com sucesso.', $logContext);

        return response()->json([
            'success'        => true,
            'url'            => $boletoUrl,
            'linha_digitavel' => $linhaDigitavel,
            'metered_at'     => now()->toDateTimeString()
        ]);
    }

    /**
     * GET /vendedor/vendas/{id}/boleto/baixar
     *
     * Faz o download direto do PDF do boleto com o nome do cliente no arquivo.
     */
    public function forceDownload(int $id)
    {
        $user = Auth::user();
        
        // — Mesma lógica de permissão do download() —
        $vendedorIds = null;
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$user->vendedor->id ?? 0];
        } elseif ($user->perfil === 'gestor') {
            $vendedorIds = \App\Models\Vendedor::where('gestor_id', $user->id)
                ->orWhere('usuario_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        $query = Venda::with(['cliente', 'pagamentos']);
        if ($vendedorIds !== null) {
            $query->whereIn('vendedor_id', $vendedorIds);
        }
        $venda = $query->findOrFail($id);

        $pagamento = $venda->pagamentos->first();
        if (!$pagamento) abort(404, 'Nenhuma cobrança registrada.');

        $boletoUrl = $pagamento->bank_slip_url ?? $pagamento->link_pagamento ?? null;

        // Tenta atualizar no Asaas antes de forçar o download
        if ($pagamento->asaas_payment_id) {
            try {
                $asaas = new AsaasService();
                $asaasId = $pagamento->asaas_payment_id;
                
                if (str_starts_with($asaasId, 'sub_')) {
                    $paymentsResponse = $asaas->requestAsaas('GET', "/subscriptions/{$asaasId}/payments");
                    $paymentData = !empty($paymentsResponse['data']) ? $paymentsResponse['data'][0] : null;
                } else {
                    $paymentData = $asaas->getPayment($asaasId);
                }
                
                if ($paymentData) {
                    if (!empty($paymentData['bankSlipUrl'])) {
                        $boletoUrl = $paymentData['bankSlipUrl'];
                        $pagamento->bank_slip_url = $boletoUrl;
                    }
                    if (!empty($paymentData['invoiceUrl'])) {
                        $pagamento->invoice_url = $paymentData['invoiceUrl'];
                        if (!$boletoUrl) $boletoUrl = $paymentData['invoiceUrl'];
                    }
                    $pagamento->save();
                }
            } catch (\Exception $e) {
                Log::error('[FORCE_DOWNLOAD_ASAAS_FAIL] Erro ao sincronizar boleto para download direto.', ['venda_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        if (!$boletoUrl) abort(404, 'Boleto não disponível ou ainda não gerado no Asaas.');

        $clientName = $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? 'Cliente';
        $safeName = preg_replace('/[^A-Za-z0-9\- ]/', '', $clientName);
        $filename = "Boleto - " . $safeName . ".pdf";

        try {
            $content = file_get_contents($boletoUrl);
            Log::info('[FORCE_DOWNLOAD_SUCCESS] PDF do boleto baixado e entregue.', ['venda_id' => $id, 'filename' => $filename]);
            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::warning('[FORCE_DOWNLOAD_FALLBACK] Falha ao baixar PDF internamente, redirecionando para URL original.', ['venda_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->away($boletoUrl);
        }
    }
}
