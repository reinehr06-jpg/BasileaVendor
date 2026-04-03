<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;

class VendedorSettingsController extends Controller
{
    public function index($tab = 'perfil')
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        return view('vendedor.configuracoes.index', compact('user', 'vendedor', 'tab'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
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
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return back()->with('error', '2FA já está ativado.');
        }

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = TwoFactorAuthService::generateSecret();
            $user->save();
        }

        $qrCode = TwoFactorAuthService::generateQrCode($user->email, $user->two_factor_secret);

        return view('vendedor.configuracoes.2fa', compact('qrCode'));
    }

    public function enable2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Configure o 2FA primeiro.']);
        }

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = true;
            $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
            $user->save();

            Session::put('2fa_verified_' . $user->id, true);

            return redirect()->route('vendedor.configuracoes', ['tab' => 'seguranca'])->with('success', 'Autenticação de dois fatores ativada com sucesso!');
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

            Session::forget('2fa_verified_' . $user->id);

            return back()->with('success', 'Autenticação de dois fatores desativada.');
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }
}
