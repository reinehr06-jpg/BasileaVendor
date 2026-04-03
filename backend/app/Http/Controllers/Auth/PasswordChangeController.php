<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Show the form for changing the password.
     */
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ], [
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        $user = Auth::user();
        
        // Update password and clear requirement
        $user->password = $request->password;
        $user->require_password_change = false;
        $user->save();

        // After password change, check 2FA status
        if ($user->two_factor_enabled) {
            return redirect()->route('2fa.verify')
                ->with('success', 'Sua senha foi atualizada com sucesso! Agora verifique sua identidade.');
        }

        // 2FA not configured - must set up before accessing system
        return redirect()->route('2fa.setup')
            ->with('success', 'Sua senha foi atualizada com sucesso! Agora configure a autenticação em duas etapas.');
    }
}
