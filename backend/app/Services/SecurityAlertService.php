<?php

namespace App\Services;

use App\Models\SysadminLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dispara alertas de segurança. Sempre registra em sysadmin_logs (nível security)
 * e, se houver um canal configurado, tenta avisar o número do responsável.
 *
 * IMPORTANTE: a entrega no celular é "best-effort" e NUNCA quebra o fluxo chamador.
 * Para funcionar de verdade é preciso um provedor de WhatsApp/SMS ativo (ver README).
 */
class SecurityAlertService
{
    public static function notify(string $event, array $context = []): void
    {
        // 1) Registro imediato no painel (não depende de rede externa)
        try {
            SysadminLog::create([
                'source' => 'backend',
                'level' => 'warning',
                'message' => '[SECURITY] ' . $event,
                'payload' => $context,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[SECURITY] ' . $event, $context);
        }

        // 2) Aviso no Telegram (best-effort, nunca quebra o chamador)
        try {
            self::sendTelegramAlert($event, $context);
        } catch (\Throwable $e) {
            Log::warning('SecurityAlert: falha ao enviar Telegram: ' . $e->getMessage());
        }
    }

    /**
     * Envia via Telegram Bot API. Precisa de:
     *   TELEGRAM_BOT_TOKEN  -> token do bot (criado no @BotFather)
     *   TELEGRAM_CHAT_ID    -> id do seu chat com o bot
     * Sem regra de template: entrega texto livre na hora.
     */
    private static function sendTelegramAlert(string $event, array $context): void
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (empty($token) || empty($chatId)) {
            Log::info('SecurityAlert: Telegram não configurado; alerta ficou só no painel.', compact('event'));
            return;
        }

        $ip = $context['ip'] ?? 'desconhecido';
        $extra = collect($context)->except('ip')
            ->map(fn ($v, $k) => "$k: " . (is_scalar($v) ? $v : json_encode($v)))
            ->implode("\n");

        $msg = "🔐 *ALERTA BASILEIA*\n"
             . "Evento: `{$event}`\n"
             . "IP: {$ip}\n"
             . "Quando: " . now()->format('d/m/Y H:i') . "\n"
             . ($extra ? "\n{$extra}" : '');

        Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'Markdown',
        ]);
    }
}
