<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;
    const MAX_2FA_ATTEMPTS = 5;

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        Log::info('LOGIN_ATTEMPT', ['email' => $email, 'has_password' => !empty($password)]);

        // Check if account is locked
        $lockKey = 'login_lock_' . md5($email);
        if (Cache::has($lockKey)) {
            $remaining = Cache::get($lockKey);
            Log::warning('LOGIN_BLOQUEADO', ['email' => $email, 'tentativas_restantes' => $remaining]);
            return back()->withErrors([
                'email' => "Conta bloqueada por muitas tentativas. Aguarde " . self::LOCKOUT_MINUTES . " minutos.",
            ])->onlyInput('email');
        }

        Log::info('LOGIN_TENTATIVA', ['email' => $email]);

        try {
            if (Auth::attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
                // Clear login attempts on success
                Cache::forget('login_attempts_' . md5($email));
                Cache::forget('login_lock_' . md5($email));

                $request->session()->regenerate();
                Log::info('LOGIN_OK', ['email' => $email, 'user_id' => Auth::id()]);

                $user = Auth::user();

                // Force password change if required
                if ($user->require_password_change) {
                    return redirect()->route('password.change');
                }

                // Check if 2FA needs rotation (90 days)
                if ($user->needsTwoFactorRotation()) {
                    $user->rotateTwoFactorSecret();
                    \App\Services\SecurityLogService::logTwoFactorEvent($user->id, 'secret_rotated', 'success');
                }

                // 2FA is MANDATORY - no access without it
                if ($user->two_factor_enabled) {
                    return redirect()->route('2fa.verify');
                }

                // Not configured yet - MUST set up before any access
                // Redirect to 2fa.setup (outside 2fa middleware group)
                return redirect()->route('2fa.setup');
            } else {
                Log::warning('LOGIN_FAILED_AUTH', ['email' => $email]);
            }
        } catch (\Exception $e) {
            Log::error('LOGIN_ERRO', ['erro' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        // Track failed attempts
        $attemptsKey = 'login_attempts_' . md5($email);
        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        $remaining = self::MAX_LOGIN_ATTEMPTS - $attempts;

        if ($remaining <= 0) {
            Cache::put($lockKey, 0, now()->addMinutes(self::LOCKOUT_MINUTES));
            Log::warning('LOGIN_CONTA_BLOQUEADA', ['email' => $email]);
            return back()->withErrors([
                'email' => "Conta bloqueada por " . self::MAX_LOGIN_ATTEMPTS . " tentativas falhas. Aguarde " . self::LOCKOUT_MINUTES . " minutos.",
            ])->onlyInput('email');
        }

        Log::warning('LOGIN_FALHA', ['email' => $email, 'tentativas_restantes' => $remaining]);

        return back()->withErrors([
            'email' => "As credenciais informadas não correspondem aos nossos registros. Tentativa {$attempts} de " . self::MAX_LOGIN_ATTEMPTS . ".",
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
