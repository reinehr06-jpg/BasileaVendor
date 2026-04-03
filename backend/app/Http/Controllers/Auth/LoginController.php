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

        try {
            if (Auth::attempt(['email' => $email, 'password' => $password], $request->boolean('remember'))) {
                $request->session()->regenerate();
                Log::info('LOGIN_OK', ['email' => $email]);

                $user = Auth::user();

                // Force password change if required
                if ($user->require_password_change) {
                    return redirect()->route('password.change');
                }

                // 2FA check
                if ($user->two_factor_enabled) {
                    return redirect()->route('2fa.verify');
                }

                return redirect('/dashboard');
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
