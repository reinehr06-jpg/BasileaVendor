<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\ChatWhatsappConfig;
use App\Models\PrimeiraMensagem;
use App\Models\TermsDocument;
use App\Models\Vendedor;
use App\Services\TwoFactorAuthService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GestorSettingsController extends Controller
{
    public function index(Request $request, $tab = null)
    {
        // A view navega com ?tab=xxx, então lemos da query string como fallback
        $tab = $tab ?: $request->query('tab', 'geral');

        $user = Auth::user();
        $gestorId = $user->id;

        $data = [
            'user' => $user,
            'tab' => $tab,
        ];

        try {
            if ($tab === 'whatsapp') {
                $config = ChatWhatsappConfig::byGestor($gestorId)->first();
                if (!$config) {
                    $config = ChatWhatsappConfig::create([
                        'gestor_id' => $gestorId,
                        'is_active' => false
                    ]);
                }
                $data['whatsappConfig'] = $config;
            }

            if ($tab === 'aprovacoes') {
                $vendedorIds = Vendedor::where('gestor_id', $gestorId)->pluck('user_id');
                $data['pendentes'] = PrimeiraMensagem::whereIn('user_id', $vendedorIds)
                    ->where('status', 'pendente_aprovacao')
                    ->with('usuario')
                    ->get();
            }

            if ($tab === 'split') {
                $data['vendedor'] = $user->vendedor;
            }

            if ($tab === 'seguranca') {
                // Gerar secret automaticamente se não existe (igual ao VendedorSettingsController)
                if (!$user->two_factor_enabled && !$user->two_factor_secret) {
                    try {
                        $user->two_factor_secret = TwoFactorAuthService::generateSecret();
                        $user->save();
                    } catch (\Exception $e) {
                        Log::warning('GESTOR_2FA_GENERATE_SECRET_ERROR: ' . $e->getMessage());
                    }
                }

                // Gerar QR Code
                $qrCode = null;
                if ($user->two_factor_secret) {
                    try {
                        $secret = $user->two_factor_secret;
                        if (str_contains($secret, ',')) {
                            $secret = explode(',', $secret)[0];
                        }
                        if (str_contains($secret, '|')) {
                            $secret = explode('|', $secret)[1];
                        }
                        $qrCode = TwoFactorAuthService::generateQrCode($user->email, $secret);
                    } catch (\Exception $e) {
                        Log::warning('GESTOR_2FA_QRCODE_ERROR: ' . $e->getMessage());
                    }
                }
                $data['qrCode'] = $qrCode;

                // Lista de dispositivos
                $secrets = $user->two_factor_secret ?? '';
                $devices = [];
                if (!empty($secrets)) {
                    $idx = 1;
                    foreach (explode(',', $secrets) as $entry) {
                        $entry = trim($entry);
                        if ($entry === '') continue;
                        if (str_contains($entry, '|')) {
                            [$name, $s] = explode('|', $entry, 2);
                            $devices[] = ['name' => $name, 'mask' => substr($s, 0, 4) . '****'];
                        } else {
                            $devices[] = ['name' => $idx === 1 ? 'Principal' : 'Dispositivo '.$idx, 'mask' => substr($entry, 0, 4) . '****'];
                        }
                        $idx++;
                    }
                }
                $data['devices'] = $devices;
                $data['recoveryCodes'] = $user->recovery_codes ? json_decode($user->recovery_codes, true) : null;
            }
        } catch (\Exception $e) {
            Log::error('GESTOR_SETTINGS_ERROR: tab=' . $tab . ' | ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return back()->with('error', 'Erro ao carregar configurações: ' . $e->getMessage());
        }

        Log::info('GESTOR_SETTINGS_RENDER: tab=' . $tab . ' | user=' . $user->id . ' | data_keys=' . implode(',', array_keys($data)));

        return view('gestor.configuracoes.index', $data);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'email' => strtolower($request->email),
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Senha atualizada com sucesso!');
    }

    public function updateWhatsapp(Request $request)
    {
        $request->validate([
            'numero_telefone' => 'required|string',
            'provider' => 'required|in:meta,Take,WppConnect,Evolution',
            'api_token' => 'required|string',
            'webhook_verify_token' => 'nullable|string',
        ]);

        $gestorId = Auth::id();
        $config = ChatWhatsappConfig::byGestor($gestorId)->firstOrCreate(
            ['gestor_id' => $gestorId],
            ['is_active' => false]
        );

        $config->update([
            'numero_telefone' => $request->numero_telefone,
            'provider' => $request->provider,
            'api_token' => $request->api_token,
            'webhook_verify_token' => $request->webhook_verify_token ?? Str::random(32),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Configurações do WhatsApp atualizadas!');
    }

    public function updateSplit(Request $request)
    {
        $vendedor = Auth::user()->vendedor;
        if (!$vendedor) return back()->with('error', 'Vendedor não encontrado.');

        if ($vendedor->wallet_status === 'validado') {
            return back()->with('error', 'Sua carteira já está validada e não pode ser alterada.');
        }

        $request->validate([
            'asaas_wallet_id' => 'required|string|max:100',
        ]);

        $vendedor->update([
            'asaas_wallet_id' => $request->asaas_wallet_id,
            'wallet_status' => 'pendente'
        ]);

        return back()->with('success', 'Wallet ID salva com sucesso! Aguarde a validação.');
    }

    public function termos()
    {
        $termos = TermsDocument::ativos()->orderByDesc('created_at')->get();
        return view('gestor.configuracoes.termos', compact('termos'));
    }

    public function downloadPdf(TermsDocument $termo)
    {
        $pdf = Pdf::loadHTML($termo->conteudo_html)
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
        
        return $pdf->download("{$termo->titulo}-v{$termo->versao}.pdf");
    }

    public function downloadHtml(TermsDocument $termo)
    {
        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>{$termo->titulo}</title><style>body{font-family:Arial,sans-serif;padding:40px;max-width:800px;margin:0 auto;}</style></head><body>{$termo->conteudo_html}</body></html>";
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$termo->titulo}-v{$termo->versao}.html\"");
    }
}
