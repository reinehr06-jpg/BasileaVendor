<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Vendedor;
use App\Services\AsaasService;
use App\Services\Checkout\CheckoutClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IntegracaoController extends Controller
{
    /**
     * Display the integrations settings page.
     */
    public function index()
    {
        $asaasApiKey = Setting::get('asaas_api_key', '');
        $asaasWebhookToken = Setting::get('asaas_webhook_token', '');
        $asaasEnvironment = Setting::get('asaas_environment', 'sandbox');
        $asaasCallbackUrl = Setting::get('asaas_callback_url', '');
        $splitGlobalAtivo = Setting::get('asaas_split_global_ativo', false);
        $jurosPadrao = Setting::get('asaas_juros_padrao', 0);
        $multaPadrao = Setting::get('asaas_multa_padrao', 0);
        
        // Configurações de Email
        $emailVendedorFrom = Setting::get('email_vendedor_from', '');
        $emailClienteFrom = Setting::get('email_cliente_from', '');
        $emailSuporte = Setting::get('email_suporte', '');
        $whatsappSuporte = Setting::get('whatsapp_suporte', '');
        
        // Configurações do Basileia Church
        $churchWebhookUrl = Setting::get('basileia_church_webhook_url', '');
        $churchWebhookToken = Setting::get('basileia_church_webhook_token', '');
        
        // Configurações Google Calendar
        $googleCalendarClientId = Setting::get('google_calendar_client_id', '');
        $googleCalendarClientSecret = Setting::get('google_calendar_client_secret', '');
        $googleCalendarRedirectUri = Setting::get('google_calendar_redirect_uri', '');
        $googleCalendarId = Setting::get('google_calendar_id', 'primary');
        $googleCalendarAtivo = Setting::get('google_calendar_ativo', false);

        // Configurações Google Gmail
        $googleGmailClientId = Setting::get('google_gmail_client_id', '');
        $googleGmailClientSecret = Setting::get('google_gmail_client_secret', '');
        $googleGmailRedirectUri = Setting::get('google_gmail_redirect_uri', '');
        $googleGmailEmail = Setting::get('google_gmail_email', '');
        $googleGmailAtivo = Setting::get('google_gmail_ativo', false);

        $vendedoresComSplit = Vendedor::where('split_ativo', true)
            ->whereNotNull('asaas_wallet_id')
            ->with('user')
            ->get();

        return view('master.configuracoes.integracoes', compact(
            'asaasApiKey',
            'asaasWebhookToken',
            'asaasEnvironment',
            'asaasCallbackUrl',
            'splitGlobalAtivo',
            'jurosPadrao',
            'multaPadrao',
            'emailVendedorFrom',
            'emailClienteFrom',
            'emailSuporte',
            'whatsappSuporte',
            'churchWebhookUrl',
            'churchWebhookToken',
            'googleCalendarClientId',
            'googleCalendarClientSecret',
            'googleCalendarRedirectUri',
            'googleCalendarId',
            'googleCalendarAtivo',
            'googleGmailClientId',
            'googleGmailClientSecret',
            'googleGmailRedirectUri',
            'googleGmailEmail',
            'googleGmailAtivo',
            'vendedoresComSplit'
        ));
    }

    /**
     * Update the integrations settings in the database.
     */
    public function update(Request $request)
    {
        $request->validate([
            'asaas_api_key' => 'nullable|string|max:255',
            'asaas_webhook_token' => 'nullable|string|max:255',
            'asaas_environment' => 'required|in:sandbox,production',
            'asaas_callback_url' => 'nullable|url|max:255',
            'checkout_api_key' => 'nullable|string|max:255',
        ]);

        $apiKey = $request->input('asaas_api_key');
        
        // Limpeza inteligente e obsessiva: remove lixo, múltiplos pedaços e caracteres invisíveis (BOM, etc)
        if ($apiKey) {
            // Remove qualquer caractere que não seja imprimível (espaços invisíveis, etc)
            $apiKey = preg_replace('/[[:^print:]]/', '', $apiKey);
            
            if (str_contains($apiKey, ':')) {
                $parts = preg_split('/[:\s]+/', $apiKey, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $p) {
                    if (str_starts_with($p, '$aact_')) {
                        $apiKey = $p;
                        break;
                    }
                }
            }
            $apiKey = trim($apiKey);
        }

        Setting::set('asaas_api_key', $apiKey);
        Setting::set('asaas_webhook_token', $request->input('asaas_webhook_token'));
        Setting::set('asaas_environment', $request->input('asaas_environment'));
        Setting::set('asaas_callback_url', $request->input('asaas_callback_url'));
        Setting::set('checkout_external_url', $request->input('checkout_external_url'));
        Setting::set('checkout_api_key', $request->input('checkout_api_key'));
        Setting::set('checkout_webhook_secret', $request->input('checkout_webhook_secret'));

        // Forçar limpeza de todo cache de configurações para garantir atualização instantânea
        Setting::clearAllCache();

        return redirect()->route('master.configuracoes', ['tab' => 'integracoes'])
                         ->with('success', 'Configurações de integração atualizadas com sucesso e aplicadas imediatamente.');
    }

    /**
     * Update split settings.
     */
    public function updateSplit(Request $request)
    {
        $request->validate([
            'asaas_juros_padrao' => 'nullable|numeric|min:0|max:100',
            'asaas_multa_padrao' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::set('asaas_split_global_ativo', $request->boolean('asaas_split_global_ativo'));
        Setting::set('asaas_juros_padrao', $request->input('asaas_juros_padrao', 0));
        Setting::set('asaas_multa_padrao', $request->input('asaas_multa_padrao', 0));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de split atualizadas com sucesso.');
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email_vendedor_from' => 'nullable|email|max:255',
            'email_cliente_from' => 'nullable|email|max:255',
            'email_suporte' => 'nullable|email|max:255',
            'whatsapp_suporte' => 'nullable|string|max:20',
        ]);

        Setting::set('email_vendedor_from', $request->input('email_vendedor_from', ''));
        Setting::set('email_cliente_from', $request->input('email_cliente_from', ''));
        Setting::set('email_suporte', $request->input('email_suporte', ''));
        Setting::set('whatsapp_suporte', $request->input('whatsapp_suporte', ''));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de email atualizadas com sucesso.');
    }

    /**
     * Update Basileia Church settings.
     */
    public function updateChurch(Request $request)
    {
        $request->validate([
            'basileia_church_webhook_url' => 'nullable|url|max:255',
            'basileia_church_webhook_token' => 'nullable|string|max:255',
        ]);

        Setting::set('basileia_church_webhook_url', $request->input('basileia_church_webhook_url', ''));
        Setting::set('basileia_church_webhook_token', $request->input('basileia_church_webhook_token', ''));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações do Basileia Church atualizadas com sucesso.');
    }

    /**
     * Test connection to Asaas API.
     */
    public function testarConexao()
    {
        try {
            Log::info('testarConexao: iniciando teste');
            $asaas = new AsaasService();
            Log::info('testarConexao: AsaasService criado', [
                'baseUrl' => $asaas->baseUrl,
                'hasKey' => !empty($asaas->getApiKey()),
            ]);
            $response = $asaas->requestAsaas('GET', '/payments?limit=1');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso! A API do Asaas está respondendo.'
            ]);
        } catch (\Exception $e) {
            Log::error('testarConexao: falhou', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Falha na conexão: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update Google Calendar settings.
     */
    public function updateGoogleCalendar(Request $request)
    {
        $request->validate([
            'google_calendar_client_id' => 'nullable|string|max:255',
            'google_calendar_client_secret' => 'nullable|string|max:255',
            'google_calendar_redirect_uri' => 'nullable|url|max:500',
            'google_calendar_id' => 'nullable|string|max:255',
        ]);

        Setting::set('google_calendar_client_id', $request->input('google_calendar_client_id', ''));
        Setting::set('google_calendar_client_secret', $request->input('google_calendar_client_secret', ''));
        Setting::set('google_calendar_redirect_uri', $request->input('google_calendar_redirect_uri', ''));
        Setting::set('google_calendar_id', $request->input('google_calendar_id', 'primary'));
        Setting::set('google_calendar_ativo', $request->boolean('google_calendar_ativo'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações do Google Calendar atualizadas com sucesso.');
    }

    /**
     * Update Google Gmail settings.
     */
    public function updateGoogleGmail(Request $request)
    {
        $request->validate([
            'google_gmail_client_id' => 'nullable|string|max:255',
            'google_gmail_client_secret' => 'nullable|string|max:255',
            'google_gmail_redirect_uri' => 'nullable|url|max:500',
            'google_gmail_email' => 'nullable|email|max:255',
        ]);

        Setting::set('google_gmail_client_id', $request->input('google_gmail_client_id', ''));
        Setting::set('google_gmail_client_secret', $request->input('google_gmail_client_secret', ''));
        Setting::set('google_gmail_redirect_uri', $request->input('google_gmail_redirect_uri', ''));
        Setting::set('google_gmail_email', $request->input('google_gmail_email', ''));
        Setting::set('google_gmail_ativo', $request->boolean('google_gmail_ativo'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações do Google Gmail atualizadas com sucesso.');
    }

    /**
     * Validate wallet ID.
     */
    public function validarWallet(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|string',
            'vendedor_id' => 'required|integer',
        ]);

        try {
            $asaas = new AsaasService();
            $result = $asaas->validateWallet($request->wallet_id);
            
            if ($result['valid']) {
                $vendedor = Vendedor::findOrFail($request->vendedor_id);
                $vendedor->update([
                    'wallet_status' => 'validado',
                    'wallet_validado_em' => now(),
                ]);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Erro ao validar wallet: ' . $e->getMessage()
            ]);
        }
    }

    // ==========================================
    // Comissões por Plano
    // ==========================================
    public function comissoesRules()
    {
        $rules = \App\Models\CommissionRule::all();
        $planos = $this->getPlanos();
        return view('master.configuracoes.comissoes', compact('rules', 'planos'));
    }

    public function updateComissaoRule(Request $request, $id)
    {
        $rule = \App\Models\CommissionRule::findOrFail($id);
        $rule->update([
            'seller_fixed_value_first_payment' => $request->seller_fixed_value_first_payment ?? 0,
            'seller_fixed_value_recurring' => $request->seller_fixed_value_recurring ?? 0,
            'manager_fixed_value_first_payment' => $request->manager_fixed_value_first_payment ?? 0,
            'manager_fixed_value_recurring' => $request->manager_fixed_value_recurring ?? 0,
            'active' => $request->has('active'),
        ]);
        return back()->with('success', 'Regra de comissão atualizada com sucesso!');
    }

    private function getPlanos()
    {
        return [
            ['nome' => 'Essential', 'max_membros' => 50],
            ['nome' => 'Essentials Plus', 'max_membros' => 100],
            ['nome' => 'Growth', 'max_membros' => 200],
            ['nome' => 'Professional', 'max_membros' => 500],
            ['nome' => 'Performance', 'max_membros' => 99999],
        ];
    }

    /**
     * Test the Checkout API Key connectivity.
     */
    public function testarCheckoutApi()
    {
        $apiKey = Setting::get('checkout_api_key', '');
        $baseUrl = Setting::get('checkout_external_url', '');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma API Key do Checkout configurada. Preencha o campo "Passo 1.5" e salve.',
            ]);
        }

        if (empty($baseUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma URL de Checkout configurada. Preencha o "Passo 5" e salve.',
            ]);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->acceptJson()
                ->get(rtrim($baseUrl, '/') . '/api/v1/transactions?limit=1');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão com o Checkout estabelecida com sucesso! A API Key está válida.',
                    'detail' => 'Status: ' . $response->status() . ' — Resposta recebida.',
                ]);
            }

            $body = $response->body();
            return response()->json([
                'success' => false,
                'message' => 'O Checkout respondeu mas retornou erro.',
                'detail' => "HTTP {$response->status()}\n" . substr($body, 0, 500),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível conectar ao Checkout.',
                'detail' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test the webhook endpoint by sending a simulated event.
     */
    public function testarWebhook()
    {
        $secret = Setting::get('checkout_webhook_secret', '');

        if (empty($secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum Webhook Secret configurado. Gere um no "Passo 1 e 2" e salve.',
            ]);
        }

        try {
            $payload = [
                'event' => 'PAYMENT_APPROVED',
                'data' => [
                    'transaction_uuid' => 'test-' . now()->timestamp,
                    'status' => 'APPROVED',
                    'amount' => 100,
                    'currency' => 'BRL',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            $signature = hash_hmac('sha256', json_encode($payload), $secret);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Checkout-Signature' => $signature,
            ])
                ->timeout(15)
                ->post(url('/webhook/checkout'), $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook respondeu com sucesso! O evento simulado foi processado.',
                    'detail' => 'Status: ' . $response->status() . ' — Evento: PAYMENT_APPROVED (teste)',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'O webhook respondeu com erro.',
                'detail' => "HTTP {$response->status()}\n" . substr($response->body(), 0, 500),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar o webhook.',
                'detail' => $e->getMessage(),
            ]);
        }
    }
}
