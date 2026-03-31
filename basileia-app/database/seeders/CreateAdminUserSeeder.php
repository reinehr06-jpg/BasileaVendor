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
        if (!User::where('email', $email)->exists()) {
            User::create([
                'name' => 'Administrador Master',
                'email' => $email,
                'password' => 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0',
                'perfil' => 'master',
                'status' => 'ativo',
                'two_factor_enabled' => false,
                'require_password_change' => false,
                'security_notifications' => true
            ]);
            
            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists - keeping current password.');
        }
    }
}
