<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MasterFixController extends Controller
{
    /**
     * Rota de emergência para restaurar o acesso Master no servidor AWS.
     * 
     * Rota: GET /master-recovery-fix
     */
    public function fix()
    {
        Log::info('Master Recovery: Tentativa de restauração de conta Master.');

        $email = 'basileia.vendas@basileia.com';
        $password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

        // Usar updateOrCreate para evitar conflito de constraint UNIQUE
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador Master',
                'password' => $password, // O cast 'hashed' do model cuida do hash
                'perfil' => 'master',
                'status' => 'ativo',
            ]
        );

        Log::info('Master Recovery: Conta Master restaurada com sucesso!', ['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Conta Master restaurada com sucesso!',
            'email' => $email,
            'password' => 'Senha definida para Basileia.vendas@Basileia.com',
            'note' => 'Por motivos de segurança, você deve deletar esta rota de routes/web.php após o uso.'
        ]);
    }
}
