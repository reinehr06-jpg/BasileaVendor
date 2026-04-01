<?php

namespace App\Services;

use App\Models\WebhookOutboundConfig;
use App\Models\WebhookOutboundLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutboundWebhookService
{
    public function dispatch(string $eventType, array $data): void
    {
        $configs = WebhookOutboundConfig::where('active', true)->get();

        foreach ($configs as $config) {
            if (!$config->hasEvent($eventType)) {
                continue;
            }

            $payload = [
                'event' => $eventType,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ];

            if ($config->secret) {
                $payload['signature'] = hash_hmac('sha256', json_encode($payload['data']), $config->secret);
            }

            WebhookOutboundLog::create([
                'webhook_config_id' => $config->id,
                'event_type' => $eventType,
                'payload' => $payload,
                'status' => 'pending',
                'attempts' => 0,
            ]);
        }

        $this->processPending();
    }

    public function processPending(): void
    {
        $logs = WebhookOutboundLog::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                  ->orWhere('next_retry_at', '<=', now());
            })
            ->with('config')
            ->limit(20)
            ->get();

        foreach ($logs as $log) {
            $this->deliver($log);
        }
    }

    private function deliver(WebhookOutboundLog $log): void
    {
        $config = $log->config;

        if (!$config || !$config->active) {
            $log->update(['status' => 'failed']);
            return;
        }

        $log->increment('attempts');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($config->url, $log->payload);

            $log->update([
                'http_status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            if ($response->successful()) {
                $log->update([
                    'status' => 'success',
                    'delivered_at' => now(),
                ]);
            } else {
                $this->handleFailure($log, $config);
            }
        } catch (\Exception $e) {
            Log::warning('Outbound webhook delivery failed', [
                'log_id' => $log->id,
                'url' => $config->url,
                'error' => $e->getMessage(),
            ]);
            $this->handleFailure($log, $config);
        }
    }

    private function handleFailure(WebhookOutboundLog $log, WebhookOutboundConfig $config): void
    {
        if ($log->attempts >= $config->retry_count) {
            $log->update(['status' => 'failed']);
        } else {
            $delay = pow(2, $log->attempts) * 60;
            $log->update(['next_retry_at' => now()->addSeconds($delay)]);
        }
    }
}
