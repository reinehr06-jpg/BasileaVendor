<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\SecurityLogService;
use App\Services\TwoFactorAuthService;

// Credenciais do admin master (nunca mudam)
define('ADMIN_EMAIL', 'basileia.vendas@basileia.com');
define('ADMIN_PASSWORD', 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0');

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Normalizar email para lowercase (case-insensitive)
        $email = strtolower($credentials['email']);
        $password = $credentials['password'];
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // GARANTIR que o admin master SEMPRE existe com a senha correta
        if ($email === ADMIN_EMAIL) {
            $this->ensureAdminExists();
        }

        // Buscar usuário com email case-insensitive
        $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            // Log failed login attempt for non-existent user
            SecurityLogService::logLoginAttempt($email, false, $ip, $userAgent, 'user_not_found');
            Log::warning('Login attempt for non-existent user', [
                'email' => $email,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Check if account is locked
        if (!is_null($user->account_locked_until) && $user->account_locked_until > now()) {
            SecurityLogService::logLoginAttempt($email, false, $ip, $userAgent, 'account_locked');
            return back()->withErrors([
                'email' => 'Conta temporariamente bloqueada devido a múltiplas tentativas de login falhas.',
            ])->onlyInput('email');
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            // Increment failed login attempts
            $user->increment('failed_login_attempts');
            $user->failed_login_at = now();
            
            // Lock account after 5 failed attempts
            if ($user->failed_login_attempts >= 5) {
                $user->account_locked_until = now()->addMinutes(30);
                SecurityLogService::logAccountLockout($email, $user->failed_login_attempts, $ip);
            }
            
            $user->save();

            // Log failed login attempt
            SecurityLogService::logLoginAttempt($email, false, $ip, $userAgent, 'invalid_password');
            Log::warning('Failed login attempt', [
                'user_id' => $user->id,
                'email' => $email,
                'ip' => $ip,
                'failed_attempts' => $user->failed_login_attempts,
            ]);

            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Check if account is active
        if ($user->status !== 'ativo') {
            SecurityLogService::logLoginAttempt($email, false, $ip, $userAgent, 'account_inactive');
            return back()->withErrors([
                'email' => 'Sua conta encontra-se inativa ou bloqueada no sistema.',
            ])->onlyInput('email');
        }

        // Reset failed login attempts on successful password check
        if ($user->failed_login_attempts > 0) {
            $user->failed_login_attempts = 0;
            $user->account_locked_until = null;
            $user->save();
        }

        // Handle 2FA if enabled
        if ($user->two_factor_enabled && !$user->two_factor_secret) {
            // Secret needs to be set up first
            session(['2fa_setup_user_id' => $user->id]);
            return redirect()->route('2fa.setup');
        }

        if ($user->two_factor_enabled) {
            // Require 2FA verification
            session(['2fa_verify_user_id' => $user->id, '2fa_verify_ip' => $ip]);
            return redirect()->route('2fa.verify');
        }

        // Successful login
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Log successful login
        SecurityLogService::logLoginAttempt($email, true, $ip, $userAgent);
        Log::info('Successful login', [
            'user_id' => $user->id,
            'email' => $email,
            'ip' => $ip,
        ]);

        // Update login info
        $user->last_login_at = now();
        $user->login_ip = $ip;
        $user->save();

        // Redirecionamento baseado no perfil
        if ($user->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }

        // Vendedor e Gestor vão para o mesmo dashboard (vendedor.dashboard)
        return redirect()->intended(route('vendedor.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Garante que o admin master existe com a senha correta.
     * Roda em TODO login do admin - funciona mesmo se entrypoint/seeder falharam.
     */
    private function ensureAdminExists(): void
    {
        try {
            $hashed = Hash::make(ADMIN_PASSWORD);
            $existing = DB::table('users')->where('email', ADMIN_EMAIL)->first();

            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update([
                    'password' => $hashed,
                    'perfil' => 'master',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->insert([
                    'name' => 'Administrador Master',
                    'email' => ADMIN_EMAIL,
                    'password' => $hashed,
                    'perfil' => 'master',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ensureAdminExists falhou: ' . $e->getMessage());
        }
    }
}
