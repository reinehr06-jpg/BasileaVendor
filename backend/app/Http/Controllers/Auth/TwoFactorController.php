<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SecurityLogService;
use App\Services\TwoFactorAuthService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    const MAX_2FA_ATTEMPTS = 5;

    const LOCKOUT_MINUTES = 15;

    public function showVerify(Request $request)
    {
        $user = Auth::user();

        // TEMPORARY: Disable 2FA auth check until properly configured
        // if (! $this->isAuthorizedFor2faFlow($request, $user->id)) {
        //     return response()->view('auth.2fa.denied', [], 403);
        // }

        // If 2FA is not enabled OR no secret exists, redirect to setup
        $secrets = $user->two_factor_secret;
        if (! $user->two_factor_enabled || empty($secrets)) {
            return redirect()->route('2fa.setup');
        }

        $lockKey = '2fa_lock_'.$user->id;

        if (Cache::has($lockKey)) {
            return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
        }

        return view('auth.2fa.verify');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        // TEMPORARY: Disable 2FA auth check until properly configured
        // if (! $this->isAuthorizedFor2faFlow($request, $user->id)) {
        //     return response()->view('auth.2fa.denied', [], 403);
        // }

        $lockKey = '2fa_lock_'.$user->id;

        if (Cache::has($lockKey)) {
            return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        try {
            // Try recovery codes first
            if ($user->recovery_codes) {
                // O accessor do model User já decodifica o JSON se for array
                $codes = $user->recovery_codes;
                
                if (is_string($codes)) {
                    $codes = json_decode($codes, true) ?: [];
                }
                
                if (!is_array($codes)) {
                    $codes = [];
                }
                
                $key = array_search($request->code, $codes);
                if ($key !== false) {
                    unset($codes[$key]);
                    
                    // O mutator do model User já vai lidar com o json_encode e encrypt
                    $user->recovery_codes = array_values($codes);
                    $user->save();
                    
                    Cache::forget('2fa_attempts_'.$user->id);
                    Session::put('2fa_verified_'.$user->id, true);
                    $request->session()->forget('login_2fa_user_id');
                    SecurityLogService::logTwoFactorEvent($user->id, 'recovery_code_used', 'success');

                    return redirect()->intended(route('dashboard'));
                }
            }

            // No devices/secret = redirect to setup
            $devices = $this->parseTwoFactorDevices($user->two_factor_secret);
            if (empty($devices)) {
                return redirect()->route('2fa.setup')->with('warning', 'Nenhum dispositivo 2FA configurado. Por favor, configure novamente.');
            }

            foreach ($devices as $device) {
                if (TwoFactorAuthService::verifyToken($device['secret'], $request->code)) {
                    Cache::forget('2fa_attempts_'.$user->id);
                    Cache::forget('2fa_lock_'.$user->id);
                    Session::put('2fa_verified_'.$user->id, true);
                    $request->session()->forget('login_2fa_user_id');
                    SecurityLogService::logTwoFactorEvent($user->id, 'verified:'.$device['name'], 'success');

                    return redirect()->intended(route('dashboard'));
                }
            }

            // If we get here, code was invalid
            $attemptsKey = '2fa_attempts_'.$user->id;
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
                'code' => "Codigo invalido. Tentativa {$attempts} de ".self::MAX_2FA_ATTEMPTS.'.',
            ]);
        } catch (DecryptException $e) {
            Log::error('2FA_VERIFY_DECRYPT_ERROR', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'recovery_codes' => null,
                'two_factor_rotated_at' => null,
            ]);

            SecurityLogService::logTwoFactorEvent($user->id, 'auto_reset_decrypt_failed', 'success');

            return redirect()->route('2fa.setup')->with('warning', '2FA foi resetado devido a erro de descriptografia. Por favor, configure novamente.');
        } catch (\Throwable $e) {
            Log::error('2FA_VERIFY_FATAL_ERROR', [
                'user_id' => $user->id, 
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                throw $e;
            }

            return response()->view('errors.custom_500', ['message' => 'Erro no 2FA: ' . $e->getMessage()], 500);
        }

        // Código duplicado removido - já tratado no catch e no loop acima
    }

    public function showSetup(Request $request)
    {
        try {
            $user = Auth::user();

            // TEMPORARY: Disable 2FA auth check until properly configured
            // if (! $this->isAuthorizedFor2faFlow($request, $user->id)) {
            //     return response()->view('auth.2fa.denied', [], 403);
            // }

            $existingDevices = $this->parseTwoFactorDevices($user->two_factor_secret);

            if ($user->two_factor_enabled && empty($existingDevices)) {
                $user->update([
                    'two_factor_enabled' => false,
                    'recovery_codes' => null,
                ]);
                $user->two_factor_enabled = false;
            }

            if ($user->two_factor_enabled) {
                if (Session::get('2fa_verified_'.$user->id)) {
                    return redirect()->route('dashboard');
                }

                return redirect()->route('2fa.verify');
            }

            // Try to read existing secret, regenerate if decryption fails
            $secret = null;
            try {
                $secret = $user->two_factor_secret;
                // If the secret was accidentally serialized (starts with s:32:"), clear it to force regeneration
                if ($secret && ! preg_match('/^[A-Z2-7]{32}$/', $secret)) {
                    $secret = null;
                }
            } catch (\Exception $e) {
                Log::warning('2FA_SECRET_DECRYPT_FAILED', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                $secret = null;
            }

            $devices = $this->parseTwoFactorDevices($secret);

            if (empty($devices)) {
                $newSecret = TwoFactorAuthService::generateSecret();
                $devices = [[
                    'name' => 'Dispositivo Principal',
                    'secret' => $newSecret,
                ]];
                // Não encrypt manualmente - o mutator do model já faz isso automaticamente
                $user->update(['two_factor_secret' => 'Dispositivo Principal|'.$newSecret]);
            }

            $primary = $devices[0];
            $secret = $primary['secret'];

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
                'devices' => $devices,
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
                'debug' => $e->getMessage()." \n ".$e->getTraceAsString(),
            ], 200);
        }
    }

    public function enable(Request $request)
    {
        try {
            $user = Auth::user();

            // TEMPORARY: Disable 2FA auth check until properly configured
            // if (! $this->isAuthorizedFor2faFlow($request, $user->id)) {
            //     return response()->view('auth.2fa.denied', [], 403);
            // }

            $lockKey = '2fa_lock_'.$user->id;
            if (Cache::has($lockKey)) {
                return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
            }

            $request->validate([
                'code' => ['required', 'digits:6'],
            ]);

            // Check for any valid device secret
            $devices = $this->parseTwoFactorDevices($user->two_factor_secret);
            if (empty($devices)) {
                return back()->withErrors(['code' => 'Configure o 2FA primeiro.']);
            }

            // Try to verify against any registered device
            $verified = false;
            foreach ($devices as $device) {
                if (TwoFactorAuthService::verifyToken($device['secret'], $request->code)) {
                    $verified = true;
                    break;
                }
            }

            if ($verified) {
                $user->update([
                    'two_factor_enabled' => true,
                    'recovery_codes' => TwoFactorAuthService::generateRecoveryCodes(),
                ]);
                $user->two_factor_enabled = true;

                // Mark as verified since user just proved they have the authenticator
                Session::put('2fa_verified_'.$user->id, true);
                SecurityLogService::logTwoFactorEvent($user->id, 'enabled', 'success');

                return redirect()->route('dashboard')->with('success', 'Autenticação de dois fatores ativada com sucesso!');
            }

            $attemptsKey = '2fa_attempts_'.$user->id;
            $attempts = Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

            if ($attempts >= self::MAX_2FA_ATTEMPTS) {
                Cache::put($lockKey, true, now()->addMinutes(self::LOCKOUT_MINUTES));
                SecurityLogService::logTwoFactorEvent($user->id, 'locked', 'failed');

                return view('auth.2fa.locked', ['minutes' => self::LOCKOUT_MINUTES]);
            }

            return back()->withErrors([
                'code' => "Código inválido. Tentativa {$attempts} de ".self::MAX_2FA_ATTEMPTS.'.',
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
                'debug' => $e->getMessage()." \n ".$e->getTraceAsString(),
            ], 200);
        }
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        // TEMPORARY: Disable 2FA auth check until properly configured
        // if (! $this->isAuthorizedFor2faFlow($request, $user->id)) {
        //     return response()->view('auth.2fa.denied', [], 403);
        // }

        $lockKey = '2fa_lock_'.$user->id;
        if (Cache::has($lockKey)) {
            return back()->withErrors(['code' => 'Conta bloqueada. Aguarde '.self::LOCKOUT_MINUTES.' minutos.']);
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        // Verificar em todos os dispositivos (não apenas o primeiro)
        $devices = $this->parseTwoFactorDevices($user->two_factor_secret);
        $verified = false;
        foreach ($devices as $device) {
            if (TwoFactorAuthService::verifyToken($device['secret'], $request->code)) {
                $verified = true;
                break;
            }
        }

        if ($verified) {
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'recovery_codes' => null,
            ]);

            Session::forget('2fa_verified_'.$user->id);
            Cache::forget('2fa_attempts_'.$user->id);
            Cache::forget('2fa_lock_'.$user->id);
            SecurityLogService::logTwoFactorEvent($user->id, 'disabled', 'success');

            return back()->with('success', 'Autenticação de dois fatores desativada.');
        }

        return back()->withErrors(['code' => 'Código inválido.']);
    }

    private function isAuthorizedFor2faFlow(Request $request, int $userId): bool
    {
        return (int) $request->session()->get('login_2fa_user_id') === $userId;
    }

    private function parseTwoFactorDevices(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        // NÃO descriptografar aqui! O accessor do model User já faz isso automaticamente.
        // Apenas se for string bruta do DB (como em migrations/seeders) precisaria descriptografar.

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
}

