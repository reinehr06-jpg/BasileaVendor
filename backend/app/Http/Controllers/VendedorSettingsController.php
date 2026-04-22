<?php

namespace App\Http\Controllers;

use App\Services\SecurityLogService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class VendedorSettingsController extends Controller
{
    public function index($tab = 'perfil')
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        // Generate 2FA secret if requested from security tab
        if ($tab === 'seguranca' && ! $user->two_factor_enabled && ! $user->two_factor_secret) {
            $user->two_factor_secret = TwoFactorAuthService::generateSecret();
            $user->save();
        }

        // Generate QR code for security tab
        $qrCode = null;
        if ($tab === 'seguranca' && $user->two_factor_secret) {
            $qrCode = TwoFactorAuthService::generateQrCode($user->email, $user->two_factor_secret);
        }

        return view('vendedor.configuracoes.index', compact('user', 'vendedor', 'tab', 'qrCode'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.Auth::id()],
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = strtolower($request->email);
        $user->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ], [
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        $user = Auth::user();
        $user->password = $request->password;
        $user->save();

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function setup2fa()
    {
        try {
            $user = Auth::user();

            if ($user->two_factor_enabled) {
                return back()->with('error', '2FA já está ativado.');
            }

            if (! $user->two_factor_secret) {
                $user->two_factor_secret = TwoFactorAuthService::generateSecret();
                $user->save();
            }

            $qrCode = TwoFactorAuthService::generateQrCode($user->email, $user->two_factor_secret);

            return view('vendedor.configuracoes.2fa', [
                'qrCode' => $qrCode,
                'secret' => $user->two_factor_secret,
            ]);
        } catch (\Exception $e) {
            Log::error('2FA_SETUP_ERROR: '.$e->getMessage().' | '.$e->getTraceAsString());

            return back()->with('error', 'Erro ao gerar QR code: '.$e->getMessage());
        }
    }

    public function enable2fa(Request $request)
    {
        $user = Auth::user();

        if ($request->has('generate_key')) {
            $user->two_factor_secret = TwoFactorAuthService::generateSecret();
            $user->save();

            return back()->with('success', 'Chave gerada! Escaneie o QR code ou use a chave manual.');
        }

        $request->validate(['code' => ['required', 'digits:6']]);

        if (! $user->two_factor_secret) {
            return back()->withErrors(['code' => 'Configure o 2FA primeiro.']);
        }

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = true;
            $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
            $user->save();

            Session::put('2fa_verified_'.$user->id, true);

            return back()->with('success', 'Autenticação de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Código inválido. Verifique se o código do app está correto.']);
    }

    public function disable2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = Auth::user();

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->recovery_codes = null;
            $user->save();

            Session::forget('2fa_verified_'.$user->id);

            return back()->with('success', 'Autenticação de dois fatores desativada.');
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }

    public function rotate2fa(Request $request)
    {
        $user = Auth::user();

        if (! $user->two_factor_enabled) {
            return back()->withErrors(['error' => '2FA não está ativado.']);
        }

        $newSecret = $user->rotateTwoFactorSecret();

        SecurityLogService::logTwoFactorEvent($user->id, 'manual_rotation', 'success');

        return back()
            ->with('success', 'Chave 2FA rotacionada! Você precisará reconfigurar seu app autenticador.')
            ->with('new_secret', $newSecret);
    }

    public function add2faDevice(Request $request)
    {
        $user = Auth::user();

        if (! $user->two_factor_enabled) {
            return back()->with('error', 'Ative o 2FA primeiro.');
        }

        $request->validate([
            'device_name' => ['required', 'string', 'max:60'],
        ]);

        $newSecret = TwoFactorAuthService::generateSecret();
        $currentSecrets = $user->two_factor_secret ?: '';

        $pairs = [];
        if (!empty($currentSecrets)) {
            foreach (explode(',', $currentSecrets) as $entry) {
                $entry = trim($entry);
                if ($entry === '') {
                    continue;
                }
                // format: NomeDoDispositivo|SECRET or legacy plain SECRET
                if (str_contains($entry, '|')) {
                    $pairs[] = $entry;
                } else {
                    $pairs[] = 'Dispositivo Principal|' . $entry;
                }
            }
        }

        if (count($pairs) >= 5) {
            return back()->with('error', 'Máximo de 5 dispositivos permitidos.');
        }

        $safeName = trim($request->device_name);
        $pairs[] = $safeName . '|' . $newSecret;

        $user->two_factor_secret = implode(',', $pairs);
        $user->save();

        $qrCode = TwoFactorAuthService::generateQrCode($user->email . ' (' . $safeName . ')', $newSecret);

        return view('vendedor.configuracoes.2fa', [
            'qrCode' => $qrCode,
            'secret' => $newSecret,
            'is_second_device' => true,
            'device_name' => $safeName,
        ])->with('success', 'Dispositivo adicionado! Configure-o no app autenticador.');
    }

    public function list2faDevices()
    {
        $user = Auth::user();
        $secrets = $user->two_factor_secret ?? '';

        $devices = [];
        if (!empty($secrets)) {
            $index = 1;
            foreach (explode(',', $secrets) as $entry) {
                $entry = trim($entry);
                if ($entry === '') {
                    continue;
                }

                if (str_contains($entry, '|')) {
                    [$name, $secret] = explode('|', $entry, 2);
                    $devices[] = [
                        'name' => $name,
                        'secret' => substr($secret, 0, 4) . '****',
                    ];
                } else {
                    $devices[] = [
                        'name' => $index === 1 ? 'Dispositivo Principal' : 'Dispositivo ' . $index,
                        'secret' => substr($entry, 0, 4) . '****',
                    ];
                }
                $index++;
            }
        }

        return response()->json(['devices' => $devices]);
    }
}
