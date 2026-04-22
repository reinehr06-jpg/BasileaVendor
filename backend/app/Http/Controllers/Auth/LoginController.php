<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\DeviceDetection;
use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Services\LoginTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public function showLoginForm()
    {
        return redirect()->route('login.generate');
    }

    public function showLoginFormWithToken(Request $request, string $token)
    {
        $tokenData = LoginTokenService::validate($token);

        if (! $tokenData) {
            Log::warning('LOGIN_TOKEN_INVALIDO', ['token_prefix' => substr($token, 0, 4)]);

            return redirect()->route('login.generate')
                ->with('error', 'Token de acesso expirado ou invalido. Gere um novo link.');
        }

        $request->session()->put('login_token', $token);
        $request->session()->put('login_token_data', $tokenData);

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login')->with('token', $token);
    }

    public function login(Request $request)
    {
        return redirect()->route('login.generate');
    }

    public function loginWithToken(Request $request, string $token)
    {
        $sessionToken = $request->session()->get('login_token');

        if (! $sessionToken || $sessionToken !== $token) {
            Log::warning('LOGIN_TOKEN_SESSAO_INVALIDA', ['token_prefix' => substr($token, 0, 4)]);

            return redirect()->route('login.generate')
                ->with('error', 'Sessao de login expirada. Gere um novo link.');
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        Log::info('LOGIN_ATTEMPT', ['email' => $email, 'has_password' => ! empty($password)]);

        $lockKey = 'login_lock_'.md5($email);
        if (Cache::has($lockKey)) {
            Log::warning('LOGIN_BLOQUEADO', ['email' => $email]);

            return back()->withErrors([
                'email' => 'Conta bloqueada por muitas tentativas. Aguarde '.self::LOCKOUT_MINUTES.' minutos.',
            ])->onlyInput('email');
        }

        try {
            // Pesquisa usuário ignorando case-sensitive, pois email já é salvo como lowercase
            $user = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            
            if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $request->boolean('remember'))) {
                LoginTokenService::markUsed($token);
                $request->session()->forget('login_token');
                $request->session()->forget('login_token_data');

                Cache::forget('login_attempts_'.md5($email));
                Cache::forget($lockKey);

                $request->session()->regenerate();
                Log::info('LOGIN_OK', ['email' => $email, 'user_id' => Auth::id()]);

                $user = Auth::user();
                $request->session()->put('login_2fa_user_id', $user->id);


                LoginLog::logLogin([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_type' => DeviceDetection::detectDeviceType($request->userAgent()),
                    'browser' => DeviceDetection::detectBrowser($request->userAgent()),
                    'os' => DeviceDetection::detectOS($request->userAgent()),
                    'status' => '2fa_required',
                    'login_token' => $token,
                ]);

                if ($user->require_password_change) {
                    return redirect()->route('password.change');
                }

                if ($user->needsTwoFactorRotation()) {
                    $user->rotateTwoFactorSecret();
                    \App\Services\SecurityLogService::logTwoFactorEvent($user->id, 'secret_rotated', 'success');
                }

                if ($user->two_factor_enabled) {
                    return redirect()->route('2fa.verify');
                }

                return redirect()->route('2fa.setup');
            }

            Log::warning('LOGIN_FAILED_AUTH', ['email' => $email]);
            $user = \App\Models\User::where('email', $email)->first();
            if ($user) {
                LoginLog::logLogin([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'failed',
                    'failure_reason' => 'Invalid credentials',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('LOGIN_ERRO', ['erro' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        $attemptsKey = 'login_attempts_'.md5($email);
        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        $remaining = self::MAX_LOGIN_ATTEMPTS - $attempts;

        if ($remaining <= 0) {
            Cache::put($lockKey, 0, now()->addMinutes(self::LOCKOUT_MINUTES));
            Log::warning('LOGIN_CONTA_BLOQUEADA', ['email' => $email]);

            return back()->withErrors([
                'email' => 'Conta bloqueada por '.self::MAX_LOGIN_ATTEMPTS.' tentativas falhas. Aguarde '.self::LOCKOUT_MINUTES.' minutos.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'As credenciais informadas nao correspondem aos nossos registros. Tentativa '.$attempts.' de '.self::MAX_LOGIN_ATTEMPTS.'.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget('login_2fa_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.generate');
    }
}
