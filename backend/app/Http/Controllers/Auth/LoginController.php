<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($request->input('email'));
        $password = $request->input('password');

        Log::info('LOGIN_TENTATIVA', ['email' => $email]);

        // === ADMIN: criar/atualizar e logar ===
        if ($email === 'basileia.vendas@basileia.com') {
            try {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $existing = DB::table('users')->where('email', $email)->first();

                if ($existing) {
                    DB::table('users')->where('id', $existing->id)->update([
                        'password' => $hashed,
                        'perfil' => 'master',
                        'updated_at' => now(),
                    ]);
                    $userId = $existing->id;
                    Log::info('LOGIN_ADMIN_UPDATE', ['id' => $userId]);
                } else {
                    $userId = DB::table('users')->insertGetId([
                        'name' => 'Administrador Master',
                        'email' => $email,
                        'password' => $hashed,
                        'perfil' => 'master',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::info('LOGIN_ADMIN_CREATE', ['id' => $userId]);
                }

                // Login manual via PDO + session
                $user = \App\Models\User::find($userId);
                if (!$user) {
                    Log::error('LOGIN_ADMIN_USER_NULL', ['id' => $userId]);
                    return back()->withErrors(['email' => 'Erro interno.']);
                }

                Auth::login($user);
                $request->session()->regenerate();
                $request->session()->save();

                Log::info('LOGIN_ADMIN_OK', [
                    'id' => $userId,
                    'auth_check' => Auth::check(),
                    'session_id' => $request->session()->getId(),
                    'session_driver' => config('session.driver'),
                ]);

                return redirect()->route('master.dashboard');

            } catch (\Exception $e) {
                Log::error('LOGIN_ADMIN_ERRO', ['erro' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return back()->withErrors(['email' => 'Erro: ' . $e->getMessage()]);
            }
        }

        // === OUTROS USUÁRIOS ===
        try {
            if (Auth::attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                Log::info('LOGIN_OK', ['email' => $email]);
                return redirect()->route('vendedor.dashboard');
            }
        } catch (\Exception $e) {
            Log::error('LOGIN_ERRO', ['erro' => $e->getMessage()]);
        }

        return back()->withErrors([
            'email' => 'As credenciais informadas não correspondem aos nossos registros.',
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
