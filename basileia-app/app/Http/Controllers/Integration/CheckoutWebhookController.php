<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Services\PagamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutWebhookController extends Controller
{
    public function __construct(
        private PagamentoService $pagamentoService
    ) {}

    public function handle(Request $request)
    {
        $signature = $request->header('X-Checkout-Signature');
        $secret = config('checkout-integration.webhook_secret', env('CHECKOUT_WEBHOOK_SECRET'));

        if ($secret && $signature) {
            $payload = $request->getContent();
            $expected = hash_hmac('sha256', $payload, $secret);

            if (!hash_equals($expected, $signature)) {
                Log::warning('Checkout webhook: assinatura inválida', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('event');
        $transaction = $request->input('transaction', []);

        Log::info('Checkout webhook recebido', [
            'event' => $event, 
            'transaction_uuid' => $transaction['uuid'] ?? null,
            'external_id' => $transaction['external_id'] ?? null,
        ]);

        // Suporte para eventos em maiúsculo (padrão) ou minúsculo
        $eventNormalized = strtoupper($event);

        match ($eventNormalized) {
            'PAYMENT_APPROVED', 'PAYMENT.APPROVED' => $this->handlePaymentApproved($transaction),
            'PAYMENT_REFUSED', 'PAYMENT.REFUSED' => $this->handlePaymentRefused($transaction),
            'PAYMENT_PENDING', 'PAYMENT.PENDING' => $this->handlePaymentPending($transaction),
            'PAYMENT_OVERDUE', 'PAYMENT.OVERDUE' => $this->handlePaymentOverdue($transaction),
            'PAYMENT_REFUNDED', 'PAYMENT.REFUNDED' => $this->handlePaymentRefunded($transaction),
            'PAYMENT_CANCELLED', 'PAYMENT.CANCELLED' => $this->handlePaymentCancelled($transaction),
            'PAYMENT_REFUND_PENDING', 'PAYMENT.REFUND_PENDING' => $this->handlePaymentRefundPending($transaction),
            default => Log::info("Checkout webhook: evento não tratado: {$event}"),
        };

        return response()->json(['received' => true]);
    }

    private function extractVendaId($externalId): ?int
    {
        if (!$externalId) return null;
        // Se externalId vier como "venda_84", retorna 84
        return (int) preg_replace('/\D/', '', $externalId);
    }

    private function handlePaymentApproved(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        
        if (!$vendaId) {
            Log::warning('Checkout webhook: external_id não encontrado');
            return;
        }

        $venda = Venda::find($vendaId);
        if (!$venda) {
            Log::warning("Checkout webhook: venda {$externalId} não encontrada");
            return;
        }

        $pagamento = Pagamento::where('venda_id', $venda->id)->first();
        if ($pagamento) {
            $pagamento->update([
                'status' => 'confirmado',
                'data_pagamento' => now(),
            ]);
        }

        $venda->update([
            'status' => 'paga',
            'data_pagamento' => now(),
        ]);

        $this->pagamentoService->confirmarPagamento($venda);

        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento aprovado");
    }

    private function handlePaymentRefused(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'recusada']);

        $pagamento = Pagamento::where('venda_id', $venda->id)->first();
        if ($pagamento) {
            $pagamento->update(['status' => 'recusado']);
        }

        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento recusado");
    }

    private function handlePaymentPending(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'pendente']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento pendente");
    }

    private function handlePaymentOverdue(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'vencida']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento vencido");
    }

    private function handlePaymentRefunded(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'estornada']);

        $pagamento = Pagamento::where('venda_id', $venda->id)->first();
        if ($pagamento) {
            $pagamento->update(['status' => 'estornado']);
        }

        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento estornado");
    }

    private function handlePaymentCancelled(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'cancelada']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento cancelado");
    }

    private function handlePaymentRefundPending(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        $vendaId = $this->extractVendaId($externalId);
        if (!$vendaId) return;

        $venda = Venda::find($vendaId);
        if (!$venda) return;

        $venda->update(['status' => 'estorno_pendente']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: estorno pendente");
    }
}
