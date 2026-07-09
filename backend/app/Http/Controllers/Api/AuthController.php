<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
            return response()->json([
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        // Deleta os tokens anteriores (opcional, para manter apenas 1 sessão)
        $user->tokens()->delete();

        // Gera novo token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->perfil ?? 'vendedor',
                'vendedor_id' => $user->vendedor?->id ?? null,
                'termos_aceitos' => $user->termos_aceitos,
            ]
        ]);
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
                'role' => $user->perfil ?? 'vendedor',
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
        ]);
    }
}
