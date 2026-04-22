<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;
use App\Models\Pagamento;
use App\Models\Venda;
use App\Models\Assinatura;
use App\Models\LogEvento;
use App\Models\WebhookEvento;
use App\Services\AsaasService;
use App\Services\PagamentoService;
use App\Services\ChurchProvisioningService;
use App\Mail\ClienteAcessoSuspenso;

class AsaasWebhookController extends Controller
{
    /**
     * Receber webhook do Asaas e sincronizar status internos
     */
    public function handle(Request $request)
    {
        // Validar origem
        $webhookToken = \App\Models\Setting::get('asaas_webhook_token', config('services.asaas.webhook_token', env('ASAAS_WEBHOOK_TOKEN', '')));
        if ($webhookToken) {
            $headerToken = $request->header('asaas-access-token');
            if ($headerToken !== $webhookToken) {
                Log::warning('Asaas Webhook: token inválido', ['received' => $headerToken]);
                return response()->json(['error' => 'Token inválido'], 403);
            }
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        if (!$event) {
            Log::warning('Asaas Webhook: evento ausente', $payload);
            return response()->json(['error' => 'Evento ausente'], 400);
        }

        // Ignorar eventos de sistema que não possuem pagamento (ex: ACCESS_TOKEN_CREATED, ACCOUNT_STATUS_CHANGED)
        if (!$payment) {
            Log::info("Asaas Webhook: evento de sistema recebido e ignorado", ['event' => $event]);
            return response()->json(['message' => 'Evento de sistema recebido'], 200);
        }

        // Ignorar eventos de sistema que não possuem pagamento (ex: ACCESS_TOKEN_CREATED, ACCOUNT_STATUS_CHANGED)
        if (!$payment) {
            Log::info("Asaas Webhook: evento de sistema recebido e ignorado", ['event' => $event]);
            return response()->json(['message' => 'Evento de sistema recebido'], 200);
        }

        $asaasPaymentId = $payment['id'] ?? null;

        Log::info("Asaas Webhook: evento recebido", [
            'event'      => $event,
            'payment_id' => $asaasPaymentId,
            'status'     => $payment['status'] ?? null,
        ]);

        // ═══════════════════════════════════════════════════════════════
        // CAMADA 2: Idempotência via tabela webhook_eventos (unique no banco)
        // Bloqueia processamento duplicado mesmo em race condition
        // ═══════════════════════════════════════════════════════════════
        try {
            WebhookEvento::create([
                'asaas_payment_id' => $asaasPaymentId,
                'evento'           => $event,
                'processado_em'    => now(),
            ]);
        } catch (QueryException $e) {
            // Duplicata detectada pelo constraint unique — ignora com segurança
            Log::info('[Webhook] Evento duplicado bloqueado pelo banco', [
                'asaas_id' => $asaasPaymentId,
                'event'    => $event,
            ]);
            return response()->json(['ok' => true, 'message' => 'Evento duplicado ignorado'], 200);
        }

        // Localizar pagamento no banco
        $pagamento = Pagamento::where('asaas_payment_id', $asaasPaymentId)->first();

        // 0. Se não encontrou e o evento tem subscription, buscar pelo subscription ID
        if (!$pagamento && !empty($payment['subscription'])) {
            $pagamento = Pagamento::where('asaas_payment_id', $payment['subscription'])->first();
            if ($pagamento) {
                $pagamento->asaas_payment_id = $asaasPaymentId;
                $pagamento->save();
                Log::info("Asaas Webhook: Pagamento encontrado via subscription ID", [
                    'old_id' => $payment['subscription'],
                    'new_id' => $asaasPaymentId,
                ]);
            }
        }

        // 1. Tentar encontrar por externalReference
        if (!$pagamento && !empty($payment['externalReference'])) {
            $extRef = $payment['externalReference'];
            if (str_starts_with($extRef, 'venda_')) {
                $vendaId = str_replace('venda_', '', $extRef);
                $vendaParaVincular = Venda::find($vendaId);
                if ($vendaParaVincular) {
                    $pagamento = Pagamento::where('venda_id', $vendaId)->first();
                    if ($pagamento && !$pagamento->asaas_payment_id) {
                        $pagamento->asaas_payment_id = $asaasPaymentId;
                        $pagamento->save();
                        Log::info("Asaas Webhook: Pagamento vinculado via externalReference", ['venda_id' => $vendaId]);
                    }
                }
            }
        }

        // 2. Se o pagamento for de uma ASSINATURA e não existir localmente
        if (!$pagamento && !empty($payment['subscription'])) {
            $assinatura = Assinatura::where('asaas_subscription_id', $payment['subscription'])->with('venda')->first();
            if ($assinatura && $assinatura->venda) {
                $pagamento = Pagamento::create([
                    'venda_id' => $assinatura->venda_id,
                    'cliente_id' => $assinatura->venda->cliente_id,
                    'vendedor_id' => $assinatura->venda->vendedor_id,
                    'asaas_payment_id' => $asaasPaymentId,
                    'valor' => $payment['value'] ?? $assinatura->venda->valor_final,
                    'billing_type' => $payment['billingType'] ?? 'BOLETO',
                    'forma_pagamento' => $payment['billingType'] ?? 'BOLETO',
                    'status' => 'PENDING',
                    'data_vencimento' => $payment['dueDate'] ?? null,
                    'invoice_url' => $payment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
                ]);
                Log::info('Asaas Webhook: pagamento criado via ciclo de assinatura', ['payment_id' => $asaasPaymentId]);
            }
        }

        if (!$pagamento) {
            Log::warning("Asaas Webhook: pagamento não encontrado localmente", [
                'asaas_payment_id' => $asaasPaymentId,
                'event' => $event
            ]);
            return response()->json(['message' => 'Pagamento não encontrado'], 200);
        }

        // ═══════════════════════════════════════════════════════════════
        // CAMADA 1: Verificação de status (mais rápida)
        // Se a venda já está paga, ignora
        // ═══════════════════════════════════════════════════════════════
        $vendaExistente = Venda::find($pagamento->venda_id);
        if ($vendaExistente && strtoupper($vendaExistente->status) === 'PAGO' && $event === 'PAYMENT_RECEIVED') {
            Log::info('[Webhook] Venda já estava paga, ignorando evento duplicado', [
                'venda_id' => $vendaExistente->id,
                'asaas_id' => $asaasPaymentId,
            ]);
            return response()->json(['ok' => true], 200);
        }

        $statusAnterior = $pagamento->status;

        // Regra de eventos
        switch ($event) {
            case 'PAYMENT_CREATED':
                $novoStatusPagamento = 'PENDING';
                $novoStatusVenda = 'Aguardando pagamento';
                break;
            case 'PAYMENT_RECEIVED':
            case 'PAYMENT_CONFIRMED':
                $novoStatusPagamento = 'RECEIVED';
                $novoStatusVenda = 'Pago';
                break;
            case 'PAYMENT_OVERDUE':
                $novoStatusPagamento = 'OVERDUE';
                $novoStatusVenda = 'Vencido';
                break;
            case 'PAYMENT_AWAITING_RISK_ANALYSIS':
                $novoStatusPagamento = 'AWAITING_RISK_ANALYSIS';
                $novoStatusVenda = 'Aguardando pagamento';
                break;
            case 'PAYMENT_DELETED':
            case 'PAYMENT_CANCELED':
            case 'PAYMENT_REFUNDED':
                $novoStatusPagamento = 'CANCELED';
                $novoStatusVenda = 'Cancelado';
                break;
            default:
                $novoStatusPagamento = strtoupper($payment['status'] ?? 'PENDING');
                $novoStatusVenda = AsaasService::mapStatus($novoStatusPagamento);
                break;
        }

        // Atualizar status do pagamento
        $pagamento->status = $novoStatusPagamento;

        if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED'])) {
            $pagamento->data_pagamento = now();
            if (!empty($payment['identificationField'])) {
                $pagamento->linha_digitavel = $payment['identificationField'];
            }
            if (!empty($payment['transactionReceiptUrl'])) {
                $pagamento->link_pagamento = $payment['transactionReceiptUrl'];
            }
            if (!empty($payment['bankSlipUrl'])) {
                $pagamento->bank_slip_url = $payment['bankSlipUrl'];
            }
            if (!empty($payment['invoiceUrl'])) {
                $pagamento->invoice_url = $payment['invoiceUrl'];
            }
        }

        if (!empty($payment['dueDate'])) {
            $pagamento->data_vencimento = $payment['dueDate'];
        }

        $pagamento->save();

        // Atualizar status da venda vinculada
        $venda = Venda::with(['vendedor.user', 'cliente', 'cobrancas'])->find($pagamento->venda_id);
        if ($venda) {
            $statusVendaAnterior = $venda->status;
            $venda->status = $novoStatusVenda;
            $venda->save();

            // Sincronizar com cobrancas
            foreach ($venda->cobrancas as $cobranca) {
                if ($cobranca->asaas_id === $asaasPaymentId || !$cobranca->asaas_id) {
                    $cobranca->status = $novoStatusPagamento;
                    if (!$cobranca->asaas_id) $cobranca->asaas_id = $asaasPaymentId;
                    $cobranca->save();
                }
            }

            // Buscar nota fiscal
            if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED']) && $asaasPaymentId) {
                try {
                    $asaas = new AsaasService();
                    $invoice = $asaas->getInvoice($asaasPaymentId);
                    if ($invoice && !empty($invoice['invoiceUrl'])) {
                        $pagamento->nota_fiscal_url = $invoice['invoiceUrl'];
                        $pagamento->nota_fiscal_status = 'emitida';
                        $pagamento->save();
                    }
                } catch (\Exception $e) {
                    Log::warning('Asaas Webhook: erro ao buscar NF', ['error' => $e->getMessage()]);
                }
            }

            // Gerar comissão + automações quando confirmado
            if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED']) && strtoupper($statusVendaAnterior) !== 'PAGO') {
                $pagamentoService = new PagamentoService();
                $pagamentoService->confirmarPagamento($pagamento, $payment);

                // Reativar conta no Church (caso estivesse suspensa por atraso)
                if ($venda && $venda->cliente) {
                    try {
                        $church = new ChurchProvisioningService();
                        if ($venda->cliente->church_user_id) {
                            $church->reativarConta($venda->cliente);
                        } else {
                            $church->criarConta($venda->cliente, $venda);
                        }
                    } catch (\Exception $e) {
                        Log::error('[Church] Falha ao reativar/criar conta após pagamento', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // PAYMENT_OVERDUE — Suspender conta no Church e avisar cliente
            if ($event === 'PAYMENT_OVERDUE' && $venda && $venda->cliente) {
                try {
                    $church = new ChurchProvisioningService();
                    $church->suspenderConta($venda->cliente);
                    Log::info('[Church] Conta suspensa por pagamento vencido', ['venda_id' => $venda->id]);
                } catch (\Exception $e) {
                    Log::error('[Church] Falha ao suspender conta', ['error' => $e->getMessage()]);
                }

                // Email avisando o cliente
                try {
                    if ($venda->cliente->email) {
                        Mail::to($venda->cliente->email)
                            ->send(new ClienteAcessoSuspenso($venda->cliente));
                        Log::info('[Email] Aviso de suspensão enviado', ['cliente_id' => $venda->cliente_id]);
                    }
                } catch (\Exception $e) {
                    Log::error('[Email] Falha ao enviar aviso de suspensão', ['error' => $e->getMessage()]);
                }
            }
        }

        // Log de evento
        LogEvento::create([
            'usuario_id'  => 1,
            'entidade'    => 'Pagamento',
            'entidade_id' => $pagamento->id,
            'acao'        => "Webhook: {$event}",
            'descricao'   => "Status alterado de '{$statusAnterior}' para '{$novoStatusPagamento}'. Asaas ID: {$asaasPaymentId}",
        ]);

        return response()->json(['message' => 'Webhook processado com sucesso'], 200);
    }
}
