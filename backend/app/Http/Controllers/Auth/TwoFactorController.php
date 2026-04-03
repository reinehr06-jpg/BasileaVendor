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

        // Try recovery codes first
        if ($user->recovery_codes) {
            $codes = json_decode($user->recovery_codes, true) ?: [];
            $key = array_search($request->code, $codes);
            if ($key !== false) {
                unset($codes[$key]);
                $user->recovery_codes = json_encode(array_values($codes));
                $user->save();
                Session::put('2fa_verified_' . $user->id, true);
                return redirect()->intended(route('dashboard'));
            }
        }

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            Session::put('2fa_verified_' . $user->id, true);
            return redirect()->intended(route('dashboard'));
        }

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

        return view('auth.2fa.setup', [
            'user' => $user,
            'enableRoute' => '2fa.enable',
        ]);
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
