<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\Contato;
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
     * GET/POST /webhooks/asaas
     */
    public function asaasWebhook(Request $request)
    {
        // Quando o Asaas redireciona o usuário via GET após pagamento
        if ($request->isMethod('get')) {
            return redirect()->route('master.vendas');
        }

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

        // Responder a testes de conexão do Asaas (payload vazio ou sem event)
        if (empty($payload) || !isset($payload['event'])) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Webhook Asaas configurado corretamente',
                'timestamp' => now()->toDateTimeString(),
            ], 200);
        }

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

            return response()->json(['error' => 'Internal server error'], 500);
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

    // ─────────────────────────────────────────
    // LEAD CAPTURE WEBHOOKS
    // ─────────────────────────────────────────

    // GET: verificação de challenge do Facebook (obrigatório)
    public function metaVerify(Request $request)
    {
        $verifyToken = config('services.meta.webhook_verify_token');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            return response($request->get('hub_challenge'), 200);
        }

        return response('Unauthorized', 403);
    }

    // POST: recebe os leads do Meta
    public function metaLead(Request $request)
    {
        Log::info('META_WEBHOOK', $request->all());

        $entries = $request->input('entry', []);

        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if ($change['field'] !== 'leadgen') continue;

                $value = $change['value'];
                $this->criarContatoDeWebhook([
                    'nome'          => $this->extrairCampo($value['field_data'] ?? [], 'full_name'),
                    'email'         => $this->extrairCampo($value['field_data'] ?? [], 'email'),
                    'telefone'      => $this->extrairCampo($value['field_data'] ?? [], 'phone_number'),
                    'canal_origem'  => 'meta_ads',
                    'utm_source'    => 'facebook',
                    'utm_medium'    => 'paid',
                    'utm_campaign'  => $value['campaign_name'] ?? null,
                    'ref_param'     => $value['ad_name'] ?? null,
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // Google Ads — via Landing Page com UTMs
    public function googleLead(Request $request)
    {
        Log::info('GOOGLE_WEBHOOK', $request->all());

        $this->criarContatoDeWebhook([
            'nome'          => $request->nome ?? $request->name ?? '',
            'email'         => $request->email ?? '',
            'telefone'      => $request->telefone ?? $request->phone ?? '',
            'canal_origem'  => 'google_ads',
            'utm_source'    => $request->utm_source   ?? 'google',
            'utm_medium'    => $request->utm_medium   ?? 'cpc',
            'utm_campaign'  => $request->utm_campaign ?? null,
            'utm_content'   => $request->utm_content  ?? null,
            'utm_term'      => $request->utm_term     ?? null,
            'ref_param'     => $request->ref          ?? null,
        ]);

        return response()->json(['status' => 'ok']);
    }

    // WhatsApp Link com ?ref=campanha
    public function whatsappLead(Request $request)
    {
        $this->criarContatoDeWebhook([
            'nome'         => $request->nome ?? 'Lead WhatsApp',
            'whatsapp'     => $request->whatsapp ?? $request->telefone ?? '',
            'canal_origem' => 'whatsapp_link',
            'ref_param'    => $request->ref ?? null,
            'utm_campaign' => $request->utm_campaign ?? null,
            'utm_source'   => 'whatsapp',
            'utm_medium'   => 'social',
        ]);

        return response()->json(['status' => 'ok']);
    }

    // Formulário Web Próprio
    public function formLead(Request $request)
    {
        $this->criarContatoDeWebhook([
            'nome'          => $request->nome ?? '',
            'email'         => $request->email ?? '',
            'telefone'      => $request->telefone ?? '',
            'canal_origem'  => 'formulario_web',
            'utm_source'    => $request->utm_source   ?? 'organico',
            'utm_medium'    => $request->utm_medium   ?? 'web',
            'utm_campaign'  => $request->utm_campaign ?? null,
            'utm_content'   => $request->utm_content  ?? null,
            'utm_term'      => $request->utm_term     ?? null,
            'ref_param'     => $request->ref          ?? null,
        ]);

        return response()->json(['status' => 'ok']);
    }

    // LÓGICA CENTRAL DE CRIAÇÃO DO CONTATO
    private function criarContatoDeWebhook(array $dados): Contato
    {
        // Tenta encontrar a campanha pelo utm_campaign ou ref_param
        $campanha = null;

        if ($dados['utm_campaign'] ?? null) {
            $campanha = Campanha::where('utm_campaign', $dados['utm_campaign'])
                ->where('status', 'ativa')
                ->first();
        }

        if (!$campanha && ($dados['ref_param'] ?? null)) {
            $campanha = Campanha::where('ref_param', $dados['ref_param'])
                ->where('status', 'ativa')
                ->first();
        }

        $contato = Contato::create([
            ...$dados,
            'campanha_id' => $campanha?->id,
            'status'      => 'lead',
            'entry_date'  => now(),
        ]);

        Log::info('CONTATO_CRIADO', ['id' => $contato->id, 'campanha' => $campanha?->nome]);

        return $contato;
    }

    // Helper para extrair campos do payload do Meta Lead Form
    private function extrairCampo(array $fieldData, string $key): ?string
    {
        foreach ($fieldData as $field) {
            if ($field['name'] === $key) return $field['values'][0] ?? null;
        }
        return null;
    }
}
