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
        // Check if admin already exists
        if (!User::where('email', 'Vinicius@basileia.global')->exists()) {
            $password = 'B4s1l31@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';
            
            User::create([
                'name' => 'Vinicius Reinehr',
                'email' => 'Vinicius@basileia.global',
                'password' => Hash::make($password),
                'perfil' => 'master',
                'status' => 'ativo',
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'recovery_codes' => null,
                'login_ip' => null,
                'last_login_at' => null,
                'failed_login_at' => null,
                'failed_login_attempts' => 0,
                'account_locked_until' => null,
                'password_reset_token' => null,
                'password_reset_expires' => null,
                'require_password_change' => true,
                'allowed_ips' => null,
                'security_notifications' => true,
                'security_scan_at' => null
            ]);
            
            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
