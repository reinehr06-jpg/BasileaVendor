<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use App\Services\SecurityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    public function showVerify()
    {
        return view('auth.2fa.verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            Session::put('2fa_verified_' . $user->id, true);
            SecurityLogService::logTwoFactorEvent($user->id, 'verified', 'success');
            return redirect()->intended(route('dashboard'));
        }

        SecurityLogService::logTwoFactorEvent($user->id, 'verify_failed', 'failed');
        return back()->withErrors(['code' => 'Código inválido. Tente novamente.']);
    }

    public function showSetup()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = TwoFactorAuthService::generateSecret();
            $user->save();
        }

        $qrCode = TwoFactorAuthService::generateQrCode($user->email, $user->two_factor_secret);

        return view('auth.2fa.setup', compact('qrCode'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Configure o 2FA primeiro.']);
        }

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = true;
            $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
            $user->save();

            SecurityLogService::logTwoFactorEvent($user->id, 'enabled', 'success');

            return redirect()->route('dashboard')->with('success', 'Autenticação de dois fatores ativada com sucesso!');
        }

        return back()->withErrors(['code' => 'Código inválido. Verifique se o código do app está correto.']);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->recovery_codes = null;
            $user->save();

            Session::forget('2fa_verified_' . $user->id);
            SecurityLogService::logTwoFactorEvent($user->id, 'disabled', 'success');

            return back()->with('success', 'Autenticação de dois fatores desativada.');
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }
}
