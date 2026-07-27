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
        
        // Gerar secret
        $secret = TwoFactorAuthService::generateSecret();
        
        // Salvar temporariamente (não confirmar ainda)
        $user->two_factor_secret = $secret;
        $user->two_factor_enabled = false;
        $user->save();
        
        // Gerar QR Code HTML/Base64 string
        $qrCodeImg = TwoFactorAuthService::generateQrCode($user->email, $secret, config('app.name'));
        
        // O frontend espera qr_code_url, mas TwoFactorAuthService retorna a tag <img>
        // Vamos extrair a string Base64/SVG da tag img
        preg_match('/src="([^"]+)"/', $qrCodeImg, $matches);
        $qrCodeUrl = $matches[1] ?? '';
        
        return response()->json([
            'qr_code_url' => $qrCodeUrl,
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
        $isValid = TwoFactorAuthService::verifyToken($user->two_factor_secret, $request->code);
        
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
