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
use Illuminate\Support\Facades\Mail;

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

        // Configurações de IA
        $iaProvider = Setting::get('ia_provider', 'ollama');
        $iaAtivo = Setting::get('ia_ativo', false);
        $iaLocalEndpoint = Setting::get('ia_local_endpoint', '');
        $iaLocalModel = Setting::get('ia_local_model', 'gemma4:e4b');
        $iaRateLimit = Setting::get('ia_rate_limit', 100);
        $openaiApiKey = Setting::get('openai_api_key', '');

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
            'iaProvider',
            'iaAtivo',
            'iaLocalEndpoint',
            'iaLocalModel',
            'iaRateLimit',
            'openaiApiKey',
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
            'email_teste' => 'nullable|email|max:255',
        ]);

        Setting::set('email_vendedor_from', $request->input('email_vendedor_from', ''));
        Setting::set('email_cliente_from', $request->input('email_cliente_from', ''));
        Setting::set('email_suporte', $request->input('email_suporte', ''));
        Setting::set('whatsapp_suporte', $request->input('whatsapp_suporte', ''));
        Setting::set('email_teste', $request->input('email_teste', ''));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de email atualizadas com sucesso.');
    }

    /**
     * Test email sending.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email_teste' => 'required|email|max:255',
        ]);

        $emailTeste = $request->input('email_teste');
        
        // Salvar o email de teste para uso futuro
        Setting::set('email_teste', $emailTeste);

        try {
            // Criar dados fake para o e-mail de teste
            $vendaFake = new \App\Models\Venda();
            $vendaFake->valor = 197.00;
            $vendaFake->plano = 'Growth';
            $vendaFake->forma_pagamento = 'PIX';
            
            $clienteFake = new \App\Models\Cliente();
            $clienteFake->nome = 'Igreja Teste';
            $clienteFake->nome_igreja = 'Igreja Teste';
            $clienteFake->email = $emailTeste;
            
            $vendaFake->cliente = $clienteFake;
            $vendaFake->vendedor = null;

            $fromEmail = Setting::get('email_vendedor_from', config('mail.from.address', 'noreply@basileiachurch.com'));
            $fromName = config('mail.from.name', 'Basiléia Global');

            \Mail::to($emailTeste)
                ->send((new \App\Mail\VendedorPagamentoConfirmado($vendaFake))
                    ->from($fromEmail, $fromName));

            return redirect()->route('master.configuracoes.integracoes')
                ->with('success', '✅ E-mail de teste enviado com sucesso para ' . $emailTeste);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar e-mail de teste', [
                'email' => $emailTeste,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('master.configuracoes.integracoes')
                ->with('error', '❌ Erro ao enviar e-mail de teste: ' . $e->getMessage());
        }
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

    public function updateChatLeads(Request $request)
    {
        $request->validate([
            'chat_google_ads_webhook_key' => 'required|string|max:255',
            'meta_webhook_verify_token' => 'required|string|max:255',
            'meta_app_secret' => 'nullable|string|max:255',
        ]);

        Setting::set('chat_google_ads_webhook_key', $request->input('chat_google_ads_webhook_key'));
        Setting::set('google_ads_webhook_key', $request->input('chat_google_ads_webhook_key'));
        Setting::set('meta_webhook_verify_token', $request->input('meta_webhook_verify_token'));
        Setting::set('meta_app_secret', $request->input('meta_app_secret', ''));

        return redirect()->route('master.configuracoes', ['tab' => 'integracoes'])
                         ->with('success', 'Integrações de Chat/Leads atualizadas com sucesso.');
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
        try {
            $payload = [
                'event' => 'PAYMENT_APPROVED',
                'transaction' => [
                    'uuid' => 'test-' . now()->timestamp,
                    'external_id' => 'venda_teste_1',
                    'status' => 'APPROVED',
                    'amount' => 100,
                    'currency' => 'BRL',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            $controller = app(\App\Http\Controllers\Integration\CheckoutWebhookController::class);
            $response = $controller->testHandle($payload);

            if ($response->getStatusCode() === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processado com sucesso! O evento simulado foi recebido e validado.',
                    'detail' => 'Evento: PAYMENT_APPROVED (teste) — Processado sem erros',
                ]);
            }

            $body = $response->getContent();
            return response()->json([
                'success' => false,
                'message' => 'O webhook rejeitou o evento.',
                'detail' => "HTTP {$response->getStatusCode()}\n" . substr($body, 0, 500),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar o webhook.',
                'detail' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Update IA settings
     */
    public function updateIA(Request $request)
    {
        $request->validate([
            'ia_provider' => 'required|in:ollama,openai',
            'ia_ativo' => 'nullable|boolean',
            'ia_local_endpoint' => 'nullable|url|max:500',
            'ia_local_model' => 'nullable|string|max:100',
            'ia_rate_limit' => 'nullable|integer|min:1|max:1000',
            'openai_api_key' => 'nullable|string|max:255',
        ]);
        
        Setting::set('ia_provider', $request->input('ia_provider'));
        Setting::set('ia_ativo', $request->boolean('ia_ativo'));
        Setting::set('ia_local_endpoint', $request->input('ia_local_endpoint'));
        Setting::set('ia_local_model', $request->input('ia_local_model', 'gemma4:e4b'));
        Setting::set('ia_rate_limit', $request->input('ia_rate_limit', 100));
        
        if ($request->filled('openai_api_key')) {
            Setting::set('openai_api_key', $request->input('openai_api_key'));
        }
        
        // Atualizar .env se necessário
        try {
            $this->atualizarEnvIA([
                'IA_PROVIDER' => $request->input('ia_provider'),
                'IA_LOCAL_ENDPOINT' => $request->input('ia_local_endpoint'),
                'IA_LOCAL_MODEL' => $request->input('ia_local_model'),
                'IA_RATE_LIMIT' => $request->input('ia_rate_limit'),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar .env com config de IA: ' . $e->getMessage());
        }
        
        return redirect()->route('master.configuracoes', ['tab' => 'integracoes'])
            ->with('success', 'Configurações da IA salvas com sucesso!');
    }
    
    /**
     * Atualiza variáveis no arquivo .env
     */
    private function atualizarEnvIA(array $values): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) return;
        
        $envContent = file_get_contents($envPath);
        
        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}=" . ($value ?? '');
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$key}=" . ($value ?? '');
            }
        }
        
        file_put_contents($envPath, $envContent);
    }
}
