<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'basileia.vendas@basileia.com';
        $password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

        // SEMPRE garantir que o admin existe com a senha correta
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador Master',
                'password' => Hash::make($password),
                'perfil' => 'master',
                'status' => 'ativo',
                'two_factor_enabled' => false,
                'require_password_change' => false,
                'security_notifications' => true,
            ]
        );

        $this->command->info('Admin garantido: ' . $email);
    }
}
