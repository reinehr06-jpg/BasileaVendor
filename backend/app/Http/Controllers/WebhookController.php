<?php

namespace App\Http\Controllers;

use App\Models\PaymentEvent;
use App\Services\Checkout\PaymentOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentOrchestrator $orchestrator;

    public function __construct()
    {
        $this->orchestrator = new PaymentOrchestrator();
    }

    /**
     * Webhook do Asaas
     * POST /webhooks/asaas
     */
    public function asaasWebhook(Request $request)
    {
        // Validar token de autenticação
        $authToken = $request->header('asaas-access-token');
        $expectedToken = config('services.asaas.webhook_token');

        if ($expectedToken && $authToken !== $expectedToken) {
            Log::warning('Webhook Asaas: Token inválido', [
                'received_token' => $authToken,
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        Log::info('Webhook Asaas: Recebido', [
            'event' => $payload['event'] ?? null,
            'payment_id' => $payload['payment']['id'] ?? null,
        ]);

        // Verificar idempotência
        $eventId = $payload['id'] ?? null;
        if ($eventId && PaymentEvent::isProcessedBefore($eventId)) {
            Log::info('Webhook Asaas: Evento já processado', [
                'event_id' => $eventId,
            ]);

            return response()->json(['status' => 'already_processed'], 200);
        }

        try {
            // Registrar evento
            $paymentId = $payload['payment']['id'] ?? null;
            $payment = $paymentId ? $this->orchestrator->getPaymentByAsaasId($paymentId) : null;

            $event = PaymentEvent::create([
                'asaas_event_id' => $eventId,
                'asaas_payment_id' => $paymentId,
                'payment_id' => $payment?->id,
                'event_type' => $payload['event'] ?? 'unknown',
                'status_from' => $payment?->status,
                'payload' => json_encode($payload),
            ]);

            // Processar webhook
            $this->orchestrator->processWebhook($payload);

            // Marcar evento como processado
            $event->markAsProcessed();

            Log::info('Webhook Asaas: Processado com sucesso', [
                'event_id' => $eventId,
                'payment_id' => $paymentId,
            ]);

            return response()->json(['status' => 'processed'], 200);

        } catch (\Exception $e) {
            Log::error('Webhook Asaas: Erro ao processar', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            // Marcar evento com erro (se existir)
            if (isset($event)) {
                $event->markAsFailed($e->getMessage());
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para verificar status do webhook
     * GET /webhooks/asaas/status
     */
    public function webhookStatus()
    {
        $recentEvents = PaymentEvent::orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'asaas_payment_id' => $event->asaas_payment_id,
                    'processed_at' => $event->processed_at,
                    'error' => $event->processing_error,
                ];
            });

        return response()->json([
            'status' => 'ok',
            'recent_events' => $recentEvents,
        ]);
    }
}
