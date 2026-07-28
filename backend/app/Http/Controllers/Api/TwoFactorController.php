<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwoFactorAuthService;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        $secret = $user->two_factor_secret;
        
        if (!$secret || $user->two_factor_enabled) {
            $secret = TwoFactorAuthService::generateSecret();
            $user->two_factor_secret = $secret;
            $user->two_factor_enabled = false;
            $user->save();
        } else {
            if (preg_match('/([A-Z2-7]{16,32})/', strtoupper($secret), $matches)) {
                $secret = $matches[1];
            }
        }
        
        $qrCodeHtml = TwoFactorAuthService::generateQrCode($user->email, $secret, config('app.name'));
        
        return response()->json([
            'qr_code_html' => $qrCodeHtml,
            'secret' => $secret, // Para usuário inserir manualmente se necessário
            'message' => 'Escaneie o QR Code com seu app autenticador'
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string'
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        // Verificar código
        $isValid = TwoFactorAuthService::verifyToken($user->two_factor_secret ?? '', $request->code);
        
        if (!$isValid) {
            return response()->json(['error' => 'Código inválido'], 400);
        }
        
        // Confirmar 2FA
        $user->two_factor_enabled = true;
        $user->save();
        
        // Deleta os tokens anteriores
        $user->tokens()->delete();
        
        // Gerar token de sessão (com HttpOnly no futuro próximo, por enquanto retorna normal)
        $token = $user->createToken('auth_token')->plainTextToken;
        
        $secure = env('SESSION_SECURE_COOKIE', app()->environment('production'));
        
        return response()->json([
            'success' => true,
            'message' => '2FA configurado com sucesso',
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
}
