<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use App\Services\SecurityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    const MAX_2FA_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public function showVerify()
    {
        $user = Auth::user();

        // If 2FA is not enabled, redirect to setup
        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        $lockKey = '2fa_lock_' . $user->id;

        if (Cache::has($lockKey)) {
            return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
        }

        return view('auth.2fa.verify');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();
        $lockKey = '2fa_lock_' . $user->id;

        if (Cache::has($lockKey)) {
            return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        try {
            // Try recovery codes first
            if ($user->recovery_codes) {
                $codes = json_decode($user->recovery_codes, true) ?: [];
                $key = array_search($request->code, $codes);
                if ($key !== false) {
                    unset($codes[$key]);
                    $user->recovery_codes = json_encode(array_values($codes));
                    $user->save();
                    Cache::forget('2fa_attempts_' . $user->id);
                    Session::put('2fa_verified_' . $user->id, true);
                    SecurityLogService::logTwoFactorEvent($user->id, 'recovery_code_used', 'success');
                    return redirect()->intended(route('dashboard'));
                }
            }

            if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
                Cache::forget('2fa_attempts_' . $user->id);
                Cache::forget('2fa_lock_' . $user->id);
                Session::put('2fa_verified_' . $user->id, true);
                SecurityLogService::logTwoFactorEvent($user->id, 'verified', 'success');
                return redirect()->intended(route('dashboard'));
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('2FA_VERIFY_DECRYPT_ERROR', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            
            // Auto-heal: se ocorreu erro de decriptação, a APP_KEY mudou ou os dados estão corrompidos.
            // Limpa o 2FA via Query Builder para não acionar os Casts do Model.
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'two_factor_enabled' => false,
                    'two_factor_secret' => null,
                    'recovery_codes' => null,
                ]);

            return redirect()->route('2fa.setup')->with('warning', 'O sistema de chaves foi atualizado e seu 2FA antigo expirou. Por favor, reconfigure seu aplicativo autenticador e salve os novos códigos.');
        } catch (\Exception $e) {
            Log::error('2FA_VERIFY_FATAL_ERROR', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return back()->withErrors([
                'code' => 'Erro interno ao validar 2FA. Tente novamente mais tarde e contate o suporte.',
            ]);
        }

        // Track failed attempts
        $attemptsKey = '2fa_attempts_' . $user->id;
        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        $remaining = self::MAX_2FA_ATTEMPTS - $attempts;

        if ($remaining <= 0) {
            Cache::put($lockKey, true, now()->addMinutes(self::LOCKOUT_MINUTES));
            SecurityLogService::logTwoFactorEvent($user->id, 'locked', 'failed');
            Log::warning('2FA_CONTA_BLOQUEADA', ['user_id' => $user->id, 'email' => $user->email]);
            return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
        }

        SecurityLogService::logTwoFactorEvent($user->id, 'verify_failed', 'failed');

        return back()->withErrors([
            'code' => "Código inválido. Tentativa {$attempts} de " . self::MAX_2FA_ATTEMPTS . ".",
        ]);
    }

    public function showSetup()
    {
        try {
            $user = Auth::user();

            if ($user->two_factor_enabled) {
                if (Session::get('2fa_verified_' . $user->id)) {
                    return redirect()->route('dashboard');
                }
                return redirect()->route('2fa.verify');
            }

            // Try to read existing secret, regenerate if decryption fails
            $secret = null;
            try {
                $secret = $user->two_factor_secret;
                // If the secret was accidentally serialized (starts with s:32:"), clear it to force regeneration
                if ($secret && !preg_match('/^[A-Z2-7]{32}$/', $secret)) {
                    $secret = null;
                }
            } catch (\Exception $e) {
                Log::warning('2FA_SECRET_DECRYPT_FAILED', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                $secret = null;
            }

            if (!$secret) {
                $newSecret = TwoFactorAuthService::generateSecret();
                // Use query builder to bypass the encrypted cast when the old value is corrupt.
                // Pass false to encrypt() so it matches Eloquent's un-serialized behavior.
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['two_factor_secret' => encrypt($newSecret, false)]);
                $user->refresh();
                $secret = $newSecret;
            }

            // Check if this is a rotation (secret was rotated within last 24h)
            $isRotation = false;
            try {
                $isRotation = $user->two_factor_rotated_at && $user->two_factor_rotated_at->diffInHours(now()) < 24;
            } catch (\Exception $e) {
                // Ignore rotation check errors
            }

            $qrCode = TwoFactorAuthService::generateQrCode($user->email, $secret);

            return view('auth.2fa.setup', [
                'user' => $user,
                'secret' => $secret,
                'enableRoute' => '2fa.enable',
                'isRotation' => $isRotation,
                'qrCode' => $qrCode,
            ]);
        } catch (\Exception $e) {
            Log::error('2FA_SETUP_ERROR', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a simple error page instead of 500
            return response()->view('auth.2fa.setup-error', [
                'message' => 'Erro ao configurar 2FA. Tente novamente ou entre em contato com o suporte.',
                'debug' => $e->getMessage() . " \n " . $e->getTraceAsString(),
            ], 200);
        }
    }

    public function enable(Request $request)
    {
        try {
            $user = Auth::user();

            $lockKey = '2fa_lock_' . $user->id;
            if (Cache::has($lockKey)) {
                return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
            }

            $request->validate([
                'code' => ['required', 'digits:6'],
            ]);

            if (!$user->two_factor_secret) {
                return back()->withErrors(['code' => 'Configure o 2FA primeiro.']);
            }

            if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
                $user->two_factor_enabled = true;
                $user->recovery_codes = json_encode(TwoFactorAuthService::generateRecoveryCodes());
                $user->save();

                // Mark as verified since user just proved they have the authenticator
                Session::put('2fa_verified_' . $user->id, true);
                SecurityLogService::logTwoFactorEvent($user->id, 'enabled', 'success');

                return redirect()->route('dashboard')->with('success', 'Autenticação de dois fatores ativada com sucesso!');
            }

            $attemptsKey = '2fa_attempts_' . $user->id;
            $attempts = Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

            if ($attempts >= self::MAX_2FA_ATTEMPTS) {
                Cache::put($lockKey, true, now()->addMinutes(self::LOCKOUT_MINUTES));
                SecurityLogService::logTwoFactorEvent($user->id, 'locked', 'failed');
                return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
            }

            return back()->withErrors([
                'code' => "Código inválido. Tentativa {$attempts} de " . self::MAX_2FA_ATTEMPTS . ".",
            ]);
        } catch (\Exception $e) {
            Log::error('2FA_ENABLE_ERROR', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // We must return 200 instead of 500, otherwise Nginx intercept the 500 on a POST request
            // and does an internal redirect that causes a 405 Method Not Allowed.
            return response()->view('auth.2fa.setup-error', [
                'message' => 'Erro ao ativar o 2FA. Tente novamente ou entre em contato.',
                'debug' => $e->getMessage() . " \n " . $e->getTraceAsString(),
            ], 200);
        }
    }

    public function disable(Request $request)
    {
        $user = Auth::user();
        $lockKey = '2fa_lock_' . $user->id;
        if (Cache::has($lockKey)) {
            return back()->withErrors(['code' => "Conta bloqueada. Aguarde " . self::LOCKOUT_MINUTES . " minutos."]);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = false;
            $user->two_factor_secret = null;
            $user->recovery_codes = null;
            $user->save();

            Session::forget('2fa_verified_' . $user->id);
            Cache::forget('2fa_attempts_' . $user->id);
            Cache::forget('2fa_lock_' . $user->id);
            SecurityLogService::logTwoFactorEvent($user->id, 'disabled', 'success');

            return back()->with('success', 'Autenticação de dois fatores desativada.');
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }
}
