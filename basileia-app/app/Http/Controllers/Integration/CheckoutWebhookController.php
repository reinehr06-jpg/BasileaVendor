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

        match ($event) {
            'payment.approved' => $this->handlePaymentApproved($transaction),
            'payment.refused' => $this->handlePaymentRefused($transaction),
            'payment.pending' => $this->handlePaymentPending($transaction),
            'payment.overdue' => $this->handlePaymentOverdue($transaction),
            'payment.refunded' => $this->handlePaymentRefunded($transaction),
            'payment.cancelled' => $this->handlePaymentCancelled($transaction),
            'payment.refund_pending' => $this->handlePaymentRefundPending($transaction),
            default => Log::info("Checkout webhook: evento não tratado: {$event}"),
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentApproved(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        if (!$externalId) {
            Log::warning('Checkout webhook: external_id não encontrado');
            return;
        }

        $venda = Venda::find($externalId);
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
        if (!$externalId) return;

        $venda = Venda::find($externalId);
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
        if (!$externalId) return;

        $venda = Venda::find($externalId);
        if (!$venda) return;

        $venda->update(['status' => 'pendente']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento pendente");
    }

    private function handlePaymentOverdue(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        if (!$externalId) return;

        $venda = Venda::find($externalId);
        if (!$venda) return;

        $venda->update(['status' => 'vencida']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento vencido");
    }

    private function handlePaymentRefunded(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        if (!$externalId) return;

        $venda = Venda::find($externalId);
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
        if (!$externalId) return;

        $venda = Venda::find($externalId);
        if (!$venda) return;

        $venda->update(['status' => 'cancelada']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: pagamento cancelado");
    }

    private function handlePaymentRefundPending(array $transaction): void
    {
        $externalId = $transaction['external_id'] ?? null;
        if (!$externalId) return;

        $venda = Venda::find($externalId);
        if (!$venda) return;

        $venda->update(['status' => 'estorno_pendente']);
        Log::info("Venda {$venda->id} atualizada via Checkout webhook: estorno pendente");
    }
}
