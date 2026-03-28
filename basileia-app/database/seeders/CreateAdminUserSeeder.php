<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if easy admin already exists
        $email = 'admin@basileia.global';
        if (!User::where('email', $email)->exists()) {
            User::create([
                'name' => 'Administrador Master',
                'email' => $email,
                'password' => Hash::make('admin123'),
                'perfil' => 'master',
                'status' => 'ativo',
                'two_factor_enabled' => false,
                'require_password_change' => false,
                'security_notifications' => true
            ]);
            
            $this->command->info('Easy Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
