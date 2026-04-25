<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Vendedor;
use App\Models\CommissionRule;
use App\Services\AsaasService;
use App\Services\Checkout\CheckoutClient;
use App\Services\Integration\IntegrationTestService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class IntegracaoController extends Controller
{
    private IntegrationTestService $testService;

    public function __construct(IntegrationTestService $testService)
    {
        $this->testService = $testService;
    }

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
        
        $emailVendedorFrom = Setting::get('email_vendedor_from', '');
        $emailClienteFrom = Setting::get('email_cliente_from', '');
        $emailSuporte = Setting::get('email_suporte', '');
        $whatsappSuporte = Setting::get('whatsapp_suporte', '');
        
        $churchWebhookUrl = Setting::get('basileia_church_webhook_url', '');
        $churchWebhookToken = Setting::get('basileia_church_webhook_token', '');
        
        $googleCalendarClientId = Setting::get('google_calendar_client_id', '');
        $googleCalendarClientSecret = Setting::get('google_calendar_client_secret', '');
        $googleCalendarRedirectUri = Setting::get('google_calendar_redirect_uri', '');
        $googleCalendarId = Setting::get('google_calendar_id', 'primary');
        $googleCalendarAtivo = Setting::get('google_calendar_ativo', false);

        $googleGmailClientId = Setting::get('google_gmail_client_id', '');
        $googleGmailClientSecret = Setting::get('google_gmail_client_secret', '');
        $googleGmailRedirectUri = Setting::get('google_gmail_redirect_uri', '');
        $googleGmailEmail = Setting::get('google_gmail_email', '');
        $googleGmailAtivo = Setting::get('google_gmail_ativo', false);

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
            'asaasApiKey', 'asaasWebhookToken', 'asaasEnvironment', 'asaasCallbackUrl',
            'splitGlobalAtivo', 'jurosPadrao', 'multaPadrao',
            'emailVendedorFrom', 'emailClienteFrom', 'emailSuporte', 'whatsappSuporte',
            'churchWebhookUrl', 'churchWebhookToken',
            'googleCalendarClientId', 'googleCalendarClientSecret', 'googleCalendarRedirectUri',
            'googleCalendarId', 'googleCalendarAtivo',
            'googleGmailClientId', 'googleGmailClientSecret', 'googleGmailRedirectUri',
            'googleGmailEmail', 'googleGmailAtivo',
            'iaProvider', 'iaAtivo', 'iaLocalEndpoint', 'iaLocalModel', 'iaRateLimit', 'openaiApiKey',
            'vendedoresComSplit'
        ));
    }

    /**
     * Update the integrations settings.
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
        
        if ($apiKey) {
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
        Setting::set('checkout_api_url', $request->input('checkout_external_url')); // Sincroniza para o testService
        Setting::set('checkout_api_key', $request->input('checkout_api_key'));
        Setting::set('checkout_webhook_secret', $request->input('checkout_webhook_secret'));

        Setting::clearAllCache();

        return redirect()->route('master.configuracoes', ['tab' => 'integracoes'])
                         ->with('success', 'Configurações atualizadas com sucesso.');
    }

    /**
     * Testar integração (Geral/Asaas)
     */
    public function testarConexao()
    {
        try {
            $result = $this->testService->testAsaas();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar integração Asaas
     */
    public function testAsaas()
    {
        try {
            $result = $this->testService->testAsaas();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar integração Checkout
     */
    public function testCheckout()
    {
        try {
            $result = $this->testService->testCheckout();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar webhook Basileia Church
     */
    public function testBasileiaChurch()
    {
        try {
            $result = $this->testService->testBasileiaChurchWebhook();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar Google Calendar
     */
    public function testGoogleCalendar()
    {
        try {
            $result = $this->testService->testGoogleCalendar();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar OpenAI
     */
    public function testOpenAI()
    {
        try {
            $result = $this->testService->testOpenAI();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar Ollama
     */
    public function testOllama()
    {
        try {
            $result = $this->testService->testOllama();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar envio de email
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email_teste' => 'required|email|max:255',
        ]);

        try {
            $emailTeste = $request->input('email_teste');
            
            Mail::raw(
                'Teste de integração de email - ' . now(),
                function ($message) use ($emailTeste) {
                    $message->to($emailTeste)
                            ->subject('Teste de Integração - Basiléia Vendas');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Email enviado com sucesso para ' . $emailTeste
            ]);
        } catch (\Exception $e) {
            Log::error('Email test failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar todas as integrações de uma vez
     */
    public function testAll()
    {
        try {
            $result = $this->testService->testAll();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ... restante dos métodos (updateSplit, updateChurch, etc.) permanecem iguais
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
                         ->with('success', 'Configurações de split atualizadas.');
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
                         ->with('success', 'Configurações de email atualizadas.');
    }

    /**
     * Test email sending.
     */
    public function testEmailLegacy(Request $request)
    {
        $request->validate([
            'email_teste' => 'required|email|max:255',
        ]);

        $emailTeste = $request->input('email_teste');
        
        Setting::set('email_teste', $emailTeste);

        try {
            Mail::raw(
                'Teste de integração de email - ' . now(),
                function ($message) use ($emailTeste) {
                    $message->to($emailTeste)
                            ->subject('Teste de Integração - Basiléia Vendas');
                }
            );

            return back()->with('success', 'Email de teste enviado com sucesso para ' . $emailTeste);
        } catch (\Exception $e) {
            Log::error('Email test failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Update church settings.
     */
    public function updateChurch(Request $request)
    {
        $request->validate([
            'basileia_church_webhook_url' => 'nullable|url|max:255',
            'basileia_church_webhook_token' => 'nullable|string|max:255',
        ]);

        Setting::set('basileia_church_webhook_url', $request->input('basileia_church_webhook_url'));
        Setting::set('basileia_church_webhook_token', $request->input('basileia_church_webhook_token'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações Basileia Church atualizadas.');
    }

    /**
     * Test Basileia Church webhook
     */
    public function testChurchWebhook()
    {
        try {
            $result = $this->testService->testBasileiaChurchWebhook();
            if ($result['success']) {
                return back()->with('success', $result['message']);
            }
            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
            'google_calendar_redirect_uri' => 'nullable|url|max:255',
        ]);

        Setting::set('google_calendar_client_id', $request->input('google_calendar_client_id'));
        Setting::set('google_calendar_client_secret', $request->input('google_calendar_client_secret'));
        Setting::set('google_calendar_redirect_uri', $request->input('google_calendar_redirect_uri'));
        Setting::set('google_calendar_ativo', $request->boolean('google_calendar_ativo'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações Google Calendar atualizadas.');
    }

    /**
     * Update Google Gmail settings.
     */
    public function updateGoogleGmail(Request $request)
    {
        $request->validate([
            'google_gmail_client_id' => 'nullable|string|max:255',
            'google_gmail_client_secret' => 'nullable|string|max:255',
            'google_gmail_redirect_uri' => 'nullable|url|max:255',
            'google_gmail_email' => 'nullable|email|max:255',
        ]);

        Setting::set('google_gmail_client_id', $request->input('google_gmail_client_id'));
        Setting::set('google_gmail_client_secret', $request->input('google_gmail_client_secret'));
        Setting::set('google_gmail_redirect_uri', $request->input('google_gmail_redirect_uri'));
        Setting::set('google_gmail_email', $request->input('google_gmail_email'));
        Setting::set('google_gmail_ativo', $request->boolean('google_gmail_ativo'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações Google Gmail atualizadas.');
    }

    /**
     * Update IA settings.
     */
    public function updateIA(Request $request)
    {
        $request->validate([
            'ia_provider' => 'required|in:ollama,openai',
            'ia_ativo' => 'boolean',
            'ia_local_endpoint' => 'nullable|url|max:255',
            'ia_local_model' => 'nullable|string|max:255',
            'ia_rate_limit' => 'nullable|integer|min:1|max:1000',
            'openai_api_key' => 'nullable|string|max:255',
            'prompt_primeira_mensagem' => 'nullable|string|max:5000',
            'prompt_qualificacao' => 'nullable|string|max:5000',
            'prompt_resumo' => 'nullable|string|max:5000',
            'prompt_sugestao' => 'nullable|string|max:5000',
        ]);

        Setting::set('ia_provider', $request->input('ia_provider'));
        Setting::set('ia_ativo', $request->boolean('ia_ativo'));
        Setting::set('ia_local_endpoint', $request->input('ia_local_endpoint'));
        Setting::set('ia_local_model', $request->input('ia_local_model'));
        Setting::set('ia_rate_limit', $request->input('ia_rate_limit', 100));
        Setting::set('openai_api_key', $request->input('openai_api_key'));
        
        // Salvar prompts individuais
        $prompts = [
            'primeira_mensagem' => $request->input('prompt_primeira_mensagem'),
            'qualificacao' => $request->input('prompt_qualificacao'),
            'resumo' => $request->input('prompt_resumo'),
            'sugestao' => $request->input('prompt_sugestao'),
        ];
        
        foreach ($prompts as $key => $value) {
            Setting::set("ia_prompt_{$key}", $value);
        }

        Setting::clearAllCache();

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de IA atualizadas.');
    }

    /**
     * Testar configuração de IA
     */
    public function testIA(Request $request)
    {
        $provider = $request->input('provider', Setting::get('ia_provider'));
        
        try {
            if ($provider === 'openai') {
                $result = $this->testService->testOpenAI();
            } else {
                $result = $this->testService->testOllama();
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter status de todas as integrações
     */
    public function statusAll()
    {
        $status = [
            'asaas' => [
                'configured' => !empty(Setting::get('asaas_api_key')),
                'test_url' => route('master.configuracoes.integracoes.test.asaas')
            ],
            'checkout' => [
                'configured' => !empty(Setting::get('checkout_api_key')),
                'test_url' => route('master.configuracoes.integracoes.test.checkout')
            ],
            'basileia_church' => [
                'configured' => !empty(Setting::get('basileia_church_webhook_url')),
                'test_url' => route('master.configuracoes.integracoes.test.church')
            ],
            'google_calendar' => [
                'configured' => !empty(Setting::get('google_calendar_client_id')),
                'test_url' => route('master.configuracoes.integracoes.test.calendar')
            ],
            'openai' => [
                'configured' => !empty(Setting::get('openai_api_key')),
                'test_url' => route('master.configuracoes.integracoes.test.openai')
            ],
            'ollama' => [
                'configured' => !empty(Setting::get('ia_local_endpoint')),
                'test_url' => route('master.configuracoes.integracoes.test.ollama')
            ],
        ];

        return response()->json($status);
    }

    /**
     * Testar API Key do Checkout
     */
    public function testarCheckoutApi()
    {
        try {
            $result = $this->testService->testCheckout();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar API: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar Webhook do Checkout
     */
    public function testarWebhook()
    {
        try {
            // Tenta enviar um ping para o serviço de checkout informando nosso webhook
            $apiUrl = Setting::get('checkout_api_url', Setting::get('checkout_external_url', ''));
            $apiKey = Setting::get('checkout_api_key', '');
            $webhookUrl = url('/api/webhook/checkout');

            if (!$apiUrl || !$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configurações de API incompletas. Verifique a URL e a API Key.'
                ]);
            }

            if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A URL do serviço de checkout é inválida. Certifique-se de incluir http:// ou https://'
                ]);
            }

            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post(rtrim($apiUrl, '/') . '/api/v1/webhooks/test', [
                    'url' => $webhookUrl
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook testado com sucesso! O Checkout recebeu o sinal.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'O Checkout retornou erro: ' . ($response->json('message') ?? 'Status ' . $response->status())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Chat/Leads settings.
     */
    public function updateChatLeads(Request $request)
    {
        $request->validate([
            'chat_google_ads_webhook_key' => 'nullable|string|max:255',
            'meta_webhook_verify_token' => 'nullable|string|max:255',
            'meta_app_secret' => 'nullable|string|max:255',
        ]);

        Setting::set('chat_google_ads_webhook_key', $request->input('chat_google_ads_webhook_key'));
        Setting::set('meta_webhook_verify_token', $request->input('meta_webhook_verify_token'));
        Setting::set('meta_app_secret', $request->input('meta_app_secret'));

        return redirect()->route('master.configuracoes', ['tab' => 'integracoes'])
                         ->with('success', 'Integrações de Chat atualizadas.');
    }

    /**
     * Validar Wallet de Vendedor.
     */
    public function validarWallet(Request $request)
    {
        $vendedorId = $request->input('vendedor_id');
        $vendedor = Vendedor::findOrFail($vendedorId);
        
        if (!$vendedor->asaas_wallet_id) {
             return response()->json(['success' => false, 'message' => 'Vendedor não possui Wallet ID.']);
        }
        
        $vendedor->update([
            'wallet_status' => 'validado',
            'wallet_validado_em' => now()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Carteira validada com sucesso!']);
    }

    /**
     * Update Commission Rule.
     */
    public function updateComissaoRule(Request $request, $id)
    {
        $rule = CommissionRule::findOrFail($id);
        
        if ($request->has('active')) {
            $rule->update(['active' => $request->boolean('active')]);
            return back()->with('success', 'Regra ' . ($rule->active ? 'ativada' : 'desativada') . ' com sucesso.');
        }
        
        $rule->update($request->only([
            'seller_fixed_value_first_payment',
            'seller_fixed_value_recurring',
            'manager_fixed_value_first_payment',
            'manager_fixed_value_recurring'
        ]));
        
        return back()->with('success', 'Regra de comissão atualizada com sucesso.');
    }
}
