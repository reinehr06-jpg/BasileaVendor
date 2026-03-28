<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Vendedor;
use App\Services\AsaasService;

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
        ]);

        Setting::set('asaas_api_key', $request->input('asaas_api_key'));
        Setting::set('asaas_webhook_token', $request->input('asaas_webhook_token'));
        Setting::set('asaas_environment', $request->input('asaas_environment'));
        Setting::set('asaas_callback_url', $request->input('asaas_callback_url'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de integração atualizadas com sucesso.');
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
            $asaas = new AsaasService();
            // Tenta fazer uma requisição simples para testar a conexão
            $response = $asaas->requestAsaas('GET', '/payments?limit=1');
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso! A API do Asaas está respondendo.'
            ]);
        } catch (\Exception $e) {
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
}
