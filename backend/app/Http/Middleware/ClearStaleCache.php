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

            // Limpar views compiladas em ambiente local
            if (app()->environment('local')) {
                $counter = Cache::get('request_counter', 0);
                Cache::put('request_counter', $counter + 1, now()->addHour());

                if ($counter % 50 === 0) {
                    \Illuminate\Support\Facades\Artisan::call('view:clear');
                }
            }
        }

        // Auto-clear de cache obsoleto a cada 24h (evita acúmulo)
        if (!Cache::has('last_cache_cleanup')) {
            $this->clearExpiredCache();
            Cache::put('last_cache_cleanup', true, now()->addHours(24));
        }

        $response = $next($request);

        // Headers anti-cache para rotas sensíveis
        if (str_contains($request->path(), 'vendas') || str_contains($request->path(), 'configuracoes') || str_contains($request->path(), 'integracoes')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Headers de segurança para todos os responses
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }

    /**
     * Limpar todo o cache de configurações do sistema.
     */
    private function clearSettingsCache(): void
    {
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
    }

    /**
     * Limpar cache expirado para evitar acúmulo de dados obsoletos.
     */
    private function clearExpiredCache(): void
    {
        try {
            \Illuminate\Support\Facades\DB::table('cache')
                ->where('expiration', '<', time())
                ->delete();
        } catch (\Exception $e) {
            // Ignorar erros de limpeza de cache
        }
    }
}
