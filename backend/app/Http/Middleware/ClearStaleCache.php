<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ClearStaleCache
{
    /**
     * Handle an incoming request.
     * Automatically clears stale cache on specific conditions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Limpar cache automaticamente em requisições de escrita
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (str_contains($request->path(), 'configuracoes') || str_contains($request->path(), 'integracoes')) {
                $this->clearSettingsCache();
            }
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Limpar todo o cache de configurações do sistema.
     */
    private function clearSettingsCache(): void
    {
        try {
            $keys = [
                // Asaas
                'setting_asaas_api_key',
                'setting_asaas_webhook_token',
                'setting_asaas_environment',
                'setting_asaas_callback_url',
                'setting_asaas_split_global_ativo',
                'setting_asaas_juros_padrao',
                'setting_asaas_multa_padrao',
                // Email
                'setting_email_vendedor_from',
                'setting_email_cliente_from',
                'setting_email_suporte',
                'setting_whatsapp_suporte',
                // Basileia Church
                'setting_basileia_church_webhook_url',
                'setting_basileia_church_webhook_token',
                // Google Calendar
                'setting_google_calendar_client_id',
                'setting_google_calendar_client_secret',
                'setting_google_calendar_redirect_uri',
                'setting_google_calendar_id',
                'setting_google_calendar_ativo',
                // Google Gmail
                'setting_google_gmail_client_id',
                'setting_google_gmail_client_secret',
                'setting_google_gmail_redirect_uri',
                'setting_google_gmail_email',
                'setting_google_gmail_ativo',
            ];

            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            // Ignorar erros de cache
        }
    }
}
