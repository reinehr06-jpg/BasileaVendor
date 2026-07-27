<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\AsaasEvent;

class AsaasWebhookController extends Controller
{
    /**
     * Receber webhook do Asaas, garantir idempotência e enfileirar para processamento assíncrono.
     */
    public function handle(Request $request)
    {
        $webhookToken = \App\Models\Setting::get('asaas_webhook_token', config('services.asaas.webhook_token', env('ASAAS_WEBHOOK_TOKEN', '')));

        // NOVO: Bloquear se token não estiver configurado em produção
        if (empty($webhookToken) && app()->environment('production')) {
            Log::error('Asaas Webhook: Token não configurado em produção', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Configuração inválida'], 500);
        }

        if ($webhookToken) {
            $headerToken = $request->header('asaas-access-token');
            if ($headerToken !== $webhookToken) {
                Log::warning('Asaas Webhook: token inválido', ['received' => $headerToken, 'ip' => $request->ip()]);
                return response()->json(['error' => 'Token inválido'], 403);
            }
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? null;
        $eventId = $payload['id'] ?? null;

        if (!$event || !$eventId) {
            Log::warning('Asaas Webhook: evento ou ID do evento ausente', $payload);
            return response()->json(['error' => 'Evento ou ID ausente'], 400);
        }

        Log::info("Asaas Webhook: recebido evento {$event}", ['event_id' => $eventId]);

        // ═══════════════════════════════════════════════════════════════
        // Idempotência via tabela asaas_events (unique no banco)
        // Bloqueia processamento duplicado garantido pelo BD
        // ═══════════════════════════════════════════════════════════════
        try {
            AsaasEvent::create([
                'asaas_event_id' => $eventId,
                'event_name'     => $event,
                'payload'        => $payload,
                'status'         => 'PENDING',
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                // Duplicata detectada pelo constraint unique — ignora com segurança
                Log::info('[Webhook] Evento duplicado bloqueado pelo banco', [
                    'asaas_event_id' => $eventId,
                ]);
            } else {
                Log::error('Asaas Webhook: Erro ao inserir evento', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Erro interno ao processar'], 500);
            }
        }

        // Retorna HTTP 200 imediatamente, conforme boa prática.
        // O processamento real será feito via Cron/Job.
        return response()->json(['ok' => true, 'message' => 'Evento recebido'], 200);
    }
}
