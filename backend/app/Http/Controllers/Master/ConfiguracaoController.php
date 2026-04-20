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
use App\Models\LoginLog;
use App\Services\AsaasService;
use App\Services\LegacyImportService;
use App\Services\LegacyCommissionService;
use App\Services\TwoFactorAuthService;
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

        // 2. Aba Segurança - Dados de 2FA e logs
        $data['usuarios2fa'] = User::select('id', 'name', 'email', 'perfil', 'status', 'two_factor_enabled', 'two_factor_secret', 'two_factor_rotated_at', 'last_login_at', 'login_ip')
            ->orderBy('name')
            ->get();

        $data['loginLogs'] = [
            'recent' => [],
            'stats' => [
                'totalToday' => 0,
                'successToday' => 0,
                'failedToday' => 0,
            ],
        ];

        try {
            if (DB::getSchemaBuilder()->hasTable('login_logs')) {
                $data['loginLogs'] = [
                    'recent' => LoginLog::with('user:id,name,email')
                        ->orderBy('created_at', 'desc')
                        ->limit(50)
                        ->get(),
                    'stats' => [
                        'totalToday' => LoginLog::whereDate('created_at', today())->count(),
                        'successToday' => LoginLog::whereDate('created_at', today())->where('status', 'success')->count(),
                        'failedToday' => LoginLog::whereDate('created_at', today())->where('status', 'failed')->count(),
                    ],
                ];
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet
        }

        $data['securitySettings'] = [
            '2faMandatoryMaster' => Setting::get('2fa_mandatory_master', true),
            '2faMandatoryGestor' => Setting::get('2fa_mandatory_gestor', false),
            '2faMandatoryVendedor' => Setting::get('2fa_mandatory_vendedor', false),
            'maxLoginAttempts' => Setting::get('max_login_attempts', 5),
            'lockoutMinutes' => Setting::get('lockout_minutes', 15),
            'sessionTimeout' => Setting::get('session_timeout_minutes', 480),
        ];

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
            'iaProvider' => Setting::get('ia_provider', 'ollama'),
            'iaAtivo' => Setting::get('ia_ativo', false),
            'iaLocalEndpoint' => Setting::get('ia_local_endpoint', ''),
            'iaLocalModel' => Setting::get('ia_local_model', 'gemma4:e4b'),
            'iaRateLimit' => Setting::get('ia_rate_limit', 100),
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

    public function toggleUser2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->two_factor_enabled = !$user->two_factor_enabled;
        
        if (!$user->two_factor_enabled) {
            $user->two_factor_secret = null;
            $user->recovery_codes = null;
        }
        
        $user->save();

        $status = $user->two_factor_enabled ? 'ativado' : 'desativado';
        
        return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                         ->with('success', "2FA {$status} para {$user->name}!");
    }

    public function resetUser2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->recovery_codes = null;
        $user->two_factor_rotated_at = null;
        $user->save();

        return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                         ->with('success', "2FA redefinido para {$user->name}. O usuário precisará configurar novamente.");
    }

    public function addUser2faDevice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_name' => ['required', 'string', 'max:60'],
        ]);

        $user = User::findOrFail($request->user_id);

        if (! $user->two_factor_enabled) {
            return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                ->with('error', 'Ative o 2FA do usuário antes de adicionar dispositivos.');
        }

        $pairs = [];
        $current = $user->two_factor_secret ?: '';

        if (! empty($current)) {
            foreach (explode(',', $current) as $entry) {
                $entry = trim($entry);
                if ($entry === '') {
                    continue;
                }

                if (str_contains($entry, '|')) {
                    $pairs[] = $entry;
                } else {
                    $pairs[] = 'Dispositivo Principal|'.$entry;
                }
            }
        }

        if (count($pairs) >= 5) {
            return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                ->with('error', 'Máximo de 5 dispositivos por usuário.');
        }

        $safeName = trim($request->device_name);
        $newSecret = TwoFactorAuthService::generateSecret();
        $pairs[] = $safeName.'|'.$newSecret;

        $user->two_factor_secret = implode(',', $pairs);
        $user->save();

        $qrCode = TwoFactorAuthService::generateQrCode($user->email . ' (' . $safeName . ')', $newSecret);

        return view('master.configuracoes.2fa', [
            'user' => $user,
            'secret' => $newSecret,
            'deviceName' => $safeName,
            'qrCode' => $qrCode,
            'isSetup' => true, // Always show form when adding new device
        ]);
    }

    public function removeUser2faDevice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_name' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $deviceToRemove = trim($request->device_name);

        $pairs = [];
        $current = $user->two_factor_secret ?: '';

        if (!empty($current)) {
            foreach (explode(',', $current) as $entry) {
                $entry = trim($entry);
                if ($entry === '') continue;

                if (str_contains($entry, '|')) {
                    [$name, $secret] = explode('|', $entry, 2);
                    if (trim($name) !== $deviceToRemove) {
                        $pairs[] = $entry;
                    }
                }
            }
        }

        if (empty($pairs)) {
            $user->two_factor_secret = null;
            $user->two_factor_enabled = false;
            $user->recovery_codes = null;
        } else {
            $user->two_factor_secret = implode(',', $pairs);
        }
        $user->save();

        return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
            ->with('success', "Dispositivo '{$deviceToRemove}' removido.");
    }

    public function enableUser2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|digits:6',
        ]);

        $user = User::findOrFail($request->user_id);

        $verified = false;
        foreach ($this->parseTwoFactorDevices($user->two_factor_secret) as $device) {
            if (TwoFactorAuthService::verifyToken($device['secret'], $request->code)) {
                $verified = true;
                break;
            }
        }

        if ($verified) {
            if (!$user->two_factor_enabled) {
                $user->two_factor_enabled = true;
                $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
                $user->save();
            }

            return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                ->with('success', '2FA ativado com sucesso para ' . $user->name . '!');
        }

        return back()->with('error', 'Código inválido. Tente novamente.');
    }

    private function parseTwoFactorDevices(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $devices = [];
        $index = 1;

        foreach (explode(',', $raw) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            if (str_contains($entry, '|')) {
                [$name, $secret] = explode('|', $entry, 2);
                $name = trim($name) !== '' ? trim($name) : 'Dispositivo '.$index;
                $secret = trim($secret);
            } else {
                $name = $index === 1 ? 'Dispositivo Principal' : 'Dispositivo '.$index;
                $secret = trim($entry);
            }

            if ($secret !== '') {
                $devices[] = ['name' => $name, 'secret' => $secret];
                $index++;
            }
        }

        return $devices;
    }

    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'max_login_attempts' => 'integer|min:3|max:10',
            'lockout_minutes' => 'integer|min:1|max:60',
            'session_timeout' => 'integer|min:30|max:1440',
        ]);

        // 2FA is mandatory for all profiles by policy.
        Setting::set('2fa_mandatory_master', true);
        Setting::set('2fa_mandatory_gestor', true);
        Setting::set('2fa_mandatory_vendedor', true);
        Setting::set('max_login_attempts', $request->max_login_attempts);
        Setting::set('lockout_minutes', $request->lockout_minutes);
        Setting::set('session_timeout_minutes', $request->session_timeout);

        return redirect()->route('master.configuracoes', ['tab' => 'seguranca'])
                         ->with('success', 'Configurações de segurança atualizadas!');
    }

    public function getLoginLogs(Request $request)
    {
        $logs = LoginLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json($logs);
    }
}
