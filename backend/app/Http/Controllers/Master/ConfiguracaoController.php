<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Vendedor;
use App\Models\User;
use App\Models\LegacyCustomerImport;
use App\Models\LegacyCommission;
use App\Models\Plano;
use App\Models\CommissionRule;
use App\Services\AsaasService;
use App\Services\LegacyImportService;
use App\Services\LegacyCommissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfiguracaoController extends Controller
{
    protected LegacyImportService $importService;
    protected LegacyCommissionService $commissionService;

    public function __construct()
    {
        $this->importService = new LegacyImportService;
        $this->commissionService = new LegacyCommissionService;
    }

    /**
     * Main settings page with tabs.
     */
    public function index(Request $request, $tab = null)
    {
        // Prioritize tab from parameter, then query string, then null (for Hub)
        $activeTab = $tab ?? $request->query('tab');
        
        $data = [
            'activeTab' => $activeTab,
            'user' => Auth::user(),
        ];

        // 1. Aba Geral (Conta)
        // No additional data needed

        // 2. Aba Segurança
        // No additional data needed

        // 3. Aba Integrações
        $data['integracoes'] = [
            'asaasApiKey' => Setting::get('asaas_api_key', ''),
            'asaasWebhookToken' => Setting::get('asaas_webhook_token', ''),
            'asaasEnvironment' => Setting::get('asaas_environment', 'sandbox'),
            'asaasCallbackUrl' => Setting::get('asaas_callback_url', ''),
            'splitGlobalAtivo' => Setting::get('asaas_split_global_ativo', false),
            'jurosPadrao' => Setting::get('asaas_juros_padrao', 0),
            'multaPadrao' => Setting::get('asaas_multa_padrao', 0),
            'emailVendedorFrom' => Setting::get('email_vendedor_from', ''),
            'emailClienteFrom' => Setting::get('email_cliente_from', ''),
            'emailSuporte' => Setting::get('email_suporte', ''),
            'whatsappSuporte' => Setting::get('whatsapp_suporte', ''),
            'emailTeste' => Setting::get('email_teste', ''),
            'checkoutExternalUrl' => Setting::get('checkout_external_url', ''),
            'checkoutApiKey' => Setting::get('checkout_api_key', ''),
            'churchWebhookUrl' => Setting::get('basileia_church_webhook_url', ''),
            'churchWebhookToken' => Setting::get('basileia_church_webhook_token', ''),
            'googleCalendarClientId' => Setting::get('google_calendar_client_id', ''),
            'googleCalendarClientSecret' => Setting::get('google_calendar_client_secret', ''),
            'googleCalendarRedirectUri' => Setting::get('google_calendar_redirect_uri', ''),
            'googleCalendarId' => Setting::get('google_calendar_id', 'primary'),
            'googleCalendarAtivo' => Setting::get('google_calendar_ativo', false),
            'googleGmailClientId' => Setting::get('google_gmail_client_id', ''),
            'googleGmailClientSecret' => Setting::get('google_gmail_client_secret', ''),
            'googleGmailRedirectUri' => Setting::get('google_gmail_redirect_uri', ''),
            'googleGmailEmail' => Setting::get('google_gmail_email', ''),
            'googleGmailAtivo' => Setting::get('google_gmail_ativo', false),
            'googleAdsWebhookKey' => Setting::get('chat_google_ads_webhook_key', Setting::get('google_ads_webhook_key', 'gads_k9x2mPqR7vLnT4wZ')),
            'metaWebhookVerifyToken' => Setting::get('meta_webhook_verify_token', 'meta_vt_x9kP2mQrLnW5'),
            'metaAppSecret' => Setting::get('meta_app_secret', ''),
            'chatWhatsappWebhookUrl' => rtrim(config('app.url'), '/') . '/webhooks/chat/whatsapp',
            'chatGoogleAdsWebhookUrl' => rtrim(config('app.url'), '/') . '/api/leads/google-ads',
            'chatMetaWebhookUrl' => rtrim(config('app.url'), '/') . '/webhooks/chat/meta-leads',
            'vendedoresComSplit' => Vendedor::where('split_ativo', true)
                ->whereNotNull('asaas_wallet_id')
                ->with('user')
                ->get(),
        ];

        // 4. Aba Legados (Summary and Filters)
        $legacyQuery = LegacyCustomerImport::with(['vendedor.user', 'gestor', 'plano']);
        
        // Aplicação de Filtros (para que a busca na aba funcione)
        if ($request->filled('search')) {
            $search = $request->search;
            $legacyQuery->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('vendedor_id')) {
            $legacyQuery->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('import_status')) {
            $legacyQuery->where('import_status', $request->import_status);
        }

        $data['legados'] = [
            'stats' => [
                'total' => LegacyCustomerImport::count(),
                'imported' => LegacyCustomerImport::where('import_status', 'IMPORTED')->count(),
                'pending' => LegacyCustomerImport::where('import_status', 'PENDING')->count(),
                'not_found' => LegacyCustomerImport::where('import_status', 'NOT_FOUND')->count(),
                'active' => LegacyCustomerImport::where('customer_status', 'ACTIVE')->count(),
                'overdue' => LegacyCustomerImport::where('customer_status', 'OVERDUE')->count(),
                'commission_pending_count' => LegacyCommission::where('status', 'GENERATED')->count(),
                'commission_pending_value' => LegacyCommission::where('status', 'GENERATED')->sum('seller_commission_amount') + LegacyCommission::where('status', 'GENERATED')->sum('gestor_commission_amount'),
            ],
            'recentImports' => $legacyQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString(),
            'vendedores' => Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get(),
            'gestores' => Vendedor::where('is_gestor', true)->with('user')->get(),
            'planos' => Plano::orderBy('nome')->get(),
        ];

        // 5. Aba Comissões
        $data['comissoes'] = [
            'rules' => CommissionRule::orderBy('id')->get(),
        ];

        // 6. Aba Cartões (Clientes com Token)
        $data['cartoes'] = [
            'clientes' => \App\Models\Cliente::whereNotNull('credit_card_token')
                ->orderBy('created_at', 'desc')
                ->get(),
        ];

        return view('master.configuracoes.index', $data);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('master.configuracoes', ['tab' => 'geral'])
                         ->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                         ->with('success', 'Senha alterada com sucesso!');
    }
}
