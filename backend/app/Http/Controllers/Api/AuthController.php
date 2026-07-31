<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\TwoFactorAuthService;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            \App\Services\SecurityAlertService::notify('login_falhou', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        // Deleta os tokens anteriores (opcional, para manter apenas 1 sessão)
        $user->tokens()->delete();
        
        $perfil = strtolower($user->perfil ?? 'vendedor');

        // Verificar se é master/gestor e não tem 2FA (Bypass in local environment)
        if (!app()->environment('local')) {
            if (in_array($perfil, ['master', 'gestor']) && !$user->two_factor_secret) {
                return response()->json([
                    'requires_2fa_setup' => true,
                    'user_id' => $user->id,
                    'message' => 'Você precisa configurar o 2FA para continuar.'
                ]);
            }

            // NOVO: Verificar 2FA se estiver habilitado e com secret configurado
            if ($user->two_factor_enabled && $user->two_factor_secret) {
                return response()->json([
                    'requires_2fa' => true,
                    'user_id' => $user->id,
                ]);
            }
        }

        // Gera novo token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Alerta de login. Todos por padrão; para reduzir ruído, defina
        // SECURITY_ALERT_ON_EVERY_LOGIN=false e só master/gestor serão avisados.
        $alertaTodos = filter_var(env('SECURITY_ALERT_ON_EVERY_LOGIN', true), FILTER_VALIDATE_BOOLEAN);
        if ($alertaTodos || in_array($perfil, ['master', 'gestor'])) {
            \App\Services\SecurityAlertService::notify('login', [
                'email' => $user->email,
                'perfil' => $perfil,
                'ip' => $request->ip(),
            ]);
        }

        $secure = env('SESSION_SECURE_COOKIE', app()->environment('production'));
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'perfil' => strtolower($user->perfil ?? 'vendedor'),
                'vendedor_id' => $user->vendedor?->id ?? null,
                'termos_aceitos' => $user->termos_aceitos,
            ]
        ])->cookie('auth_token', $token, 1440, '/', null, $secure, true, false, 'Lax');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return response()->json(['error' => '2FA não ativado'], 400);
        }

        // The stored secret might have devices prefixed: "Dispositivo Principal|SECRET"
        // Let's use the service to verify
        $isValid = TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code);

        if (!$isValid) {
            return response()->json(['error' => 'Código inválido'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $secure = env('SESSION_SECURE_COOKIE', app()->environment('production'));

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'perfil' => strtolower($user->perfil ?? 'vendedor'),
                'vendedor_id' => $user->vendedor?->id ?? null,
                'termos_aceitos' => $user->termos_aceitos,
            ]
        ])->cookie('auth_token', $token, 1440, '/', null, $secure, true, false, 'Lax');
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'perfil' => strtolower($user->perfil ?? 'vendedor'),
                'vendedor_id' => $user->vendedor?->id ?? null,
                'termos_aceitos' => $user->termos_aceitos,
            ]
        ]);
    }

    public function aceitarTermos(Request $request)
    {
        $user = $request->user();
        $user->termos_aceitos = true;
        $user->termos_aceitos_em = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Termos aceitos com sucesso.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.'
        ])->withoutCookie('auth_token');
    }
}
