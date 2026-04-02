<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class LoginController extends Controller
{
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

        $email = strtolower($credentials['email']);
        $password = $credentials['password'];

        // === ADMIN MASTER: acesso garantido ===
        if ($email === self::ADMIN_EMAIL) {
            // Verificar senha
            if ($password !== self::ADMIN_PASSWORD) {
                return back()->withErrors([
                    'email' => 'As credenciais informadas não correspondem aos nossos registros.',
                ])->onlyInput('email');
            }

            // Criar ou atualizar admin
            $hashed = Hash::make(self::ADMIN_PASSWORD);
            $existing = DB::table('users')->where('email', self::ADMIN_EMAIL)->first();

            if ($existing) {
                DB::table('users')->where('id', $existing->id)->update([
                    'password' => $hashed,
                    'perfil' => 'master',
                    'updated_at' => now(),
                ]);
                $userId = $existing->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => 'Administrador Master',
                    'email' => self::ADMIN_EMAIL,
                    'password' => $hashed,
                    'perfil' => 'master',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Logar
            $user = User::find($userId);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        // === OUTROS USUÁRIOS: login normal ===
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Verificar conta bloqueada (se coluna existir)
        if (Schema::hasColumn('users', 'account_locked_until')) {
            try {
                if (!is_null($user->account_locked_until) && $user->account_locked_until > now()) {
                    return back()->withErrors([
                        'email' => 'Conta temporariamente bloqueada. Tente novamente em alguns minutos.',
                    ])->onlyInput('email');
                }
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        // Verificar senha
        if (!Hash::check($password, $user->password)) {
            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        // Verificar status (se coluna existir)
        if (Schema::hasColumn('users', 'status')) {
            try {
                if ($user->status !== 'ativo') {
                    return back()->withErrors([
                        'email' => 'Sua conta encontra-se inativa ou bloqueada.',
                    ])->onlyInput('email');
                }
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        // Login bem-sucedido
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        try {
            DB::table('users')->where('id', $user->id)->update(['last_login_at' => now()]);
        } catch (\Exception $e) {
            // Ignorar
        }

        if ($user->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }

        return redirect()->intended(route('vendedor.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
