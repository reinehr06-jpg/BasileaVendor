<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'basileia.vendas@basileia.com';
        $password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';
        $hashed = Hash::make($password);

        // Verificar se o admin já existe
        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            // Atualizar senha e dados básicos
            $data = [
                'password' => $hashed,
                'name' => 'Administrador Master',
                'perfil' => 'master',
                'updated_at' => now(),
            ];

            // Adicionar colunas opcionais apenas se existirem
            if (Schema::hasColumn('users', 'status')) {
                $data['status'] = 'ativo';
            }
            if (Schema::hasColumn('users', 'two_factor_enabled')) {
                $data['two_factor_enabled'] = false;
            }
            if (Schema::hasColumn('users', 'require_password_change')) {
                $data['require_password_change'] = false;
            }

            DB::table('users')->where('id', $existing->id)->update($data);
            $this->command->info('Admin atualizado: ' . $email);
        } else {
            // Criar novo admin
            $data = [
                'name' => 'Administrador Master',
                'email' => $email,
                'password' => $hashed,
                'perfil' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Adicionar colunas opcionais apenas se existirem
            if (Schema::hasColumn('users', 'status')) {
                $data['status'] = 'ativo';
            }
            if (Schema::hasColumn('users', 'two_factor_enabled')) {
                $data['two_factor_enabled'] = false;
            }
            if (Schema::hasColumn('users', 'require_password_change')) {
                $data['require_password_change'] = false;
            }

            DB::table('users')->insert($data);
            $this->command->info('Admin criado: ' . $email);
        }
    }
}
