<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\SecurityLogService;
use App\Services\TwoFactorAuthService;

class LoginController extends Controller
{
    // Credenciais do admin master (nunca mudam)
    const ADMIN_EMAIL = 'basileia.vendas@basileia.com';
    const ADMIN_PASSWORD = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

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
        if ($email === self::ADMIN_EMAIL) {
            $this->ensureAdminExists();
        }

        // Buscar usuário com email case-insensitive
        $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Check if account is locked (só se coluna existir)
        if (Schema::hasColumn('users', 'account_locked_until')) {
            if (!is_null($user->account_locked_until) && $user->account_locked_until > now()) {
                return back()->withErrors([
                    'email' => 'Conta temporariamente bloqueada. Tente novamente em alguns minutos.',
                ])->onlyInput('email');
            }
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            // Incrementar tentativas falhas (só se coluna existir)
            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                try {
                    $user->increment('failed_login_attempts');
                    $user->refresh();
                    if ($user->failed_login_attempts >= 5 && Schema::hasColumn('users', 'account_locked_until')) {
                        DB::table('users')->where('id', $user->id)->update([
                            'account_locked_until' => now()->addMinutes(30),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignorar erro de coluna inexistente
                }
            }

            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Check if account is active (só se coluna existir)
        if (Schema::hasColumn('users', 'status') && $user->status !== 'ativo') {
            return back()->withErrors([
                'email' => 'Sua conta encontra-se inativa ou bloqueada no sistema.',
            ])->onlyInput('email');
        }

        // Reset failed login attempts (só se coluna existir)
        if (Schema::hasColumn('users', 'failed_login_attempts') && $user->failed_login_attempts > 0) {
            try {
                DB::table('users')->where('id', $user->id)->update([
                    'failed_login_attempts' => 0,
                    'account_locked_until' => null,
                ]);
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        // Handle 2FA (só se coluna existir)
        if (Schema::hasColumn('users', 'two_factor_enabled')) {
            if ($user->two_factor_enabled) {
                if (Schema::hasColumn('users', 'two_factor_secret') && !$user->two_factor_secret) {
                    session(['2fa_setup_user_id' => $user->id]);
                    return redirect()->route('2fa.setup');
                }
                session(['2fa_verify_user_id' => $user->id, '2fa_verify_ip' => $ip]);
                return redirect()->route('2fa.verify');
            }
        }

        // Successful login
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Update login info
        try {
            DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);
        } catch (\Exception $e) {
            // Ignorar
        }

        // Redirecionamento baseado no perfil
        if ($user->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }

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
            $hashed = Hash::make(self::ADMIN_PASSWORD);
            $existing = DB::table('users')->where('email', self::ADMIN_EMAIL)->first();

            $data = [
                'password' => $hashed,
                'perfil' => 'master',
                'updated_at' => now(),
            ];

            // Definir colunas opcionais se existirem
            if (Schema::hasColumn('users', 'status')) {
                $data['status'] = 'ativo';
            }
            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                $data['failed_login_attempts'] = 0;
            }
            if (Schema::hasColumn('users', 'account_locked_until')) {
                $data['account_locked_until'] = null;
            }
            if (Schema::hasColumn('users', 'two_factor_enabled')) {
                $data['two_factor_enabled'] = false;
            }
            if (Schema::hasColumn('users', 'require_password_change')) {
                $data['require_password_change'] = false;
            }

            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update($data);
            } else {
                $data['name'] = 'Administrador Master';
                $data['email'] = self::ADMIN_EMAIL;
                $data['created_at'] = now();
                DB::table('users')->insert($data);
            }
        } catch (\Exception $e) {
            Log::error('ensureAdminExists falhou: ' . $e->getMessage());
        }
    }
}
