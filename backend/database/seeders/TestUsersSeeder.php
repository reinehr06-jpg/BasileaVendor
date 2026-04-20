<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        echo "Criando usuários de teste...\n";

        $admin = \App\Models\User::create([
            'name' => 'Master Teste',
            'email' => 'admin@teste.com',
            'password' => Hash::make('123456'),
            'perfil' => 'master',
            'status' => 'active',
            'require_password_change' => false,
        ]);
        echo "Master criado: admin@teste.com / 123456\n";

        $gestor = \App\Models\User::create([
            'name' => 'Gestor Teste',
            'email' => 'gestor@teste.com',
            'password' => Hash::make('123456'),
            'perfil' => 'gestor',
            'status' => 'active',
            'require_password_change' => false,
        ]);
        echo "Gestor criado: gestor@teste.com / 123456\n";

        $vendedor = \App\Models\User::create([
            'name' => 'Vendedor Teste',
            'email' => 'vendedor@teste.com',
            'password' => Hash::make('123456'),
            'perfil' => 'vendedor',
            'status' => 'active',
            'require_password_change' => false,
        ]);
        echo "Vendedor criado: vendedor@teste.com / 123456\n";

        $equipe = \App\Models\Equipe::create([
            'tenant_id' => $tenantId,
            'nome' => 'Equipe Teste',
        ]);
        echo "Equipe criada\n";

        $vendedorModel = \App\Models\Vendedor::create([
            'user_id' => $vendedor->id,
            'tenant_id' => $tenantId,
            'equipe_id' => $equipe->id,
            'meta_mensal' => 10000,
            'chat_enabled' => true,
            'lead_enabled' => true,
        ]);
        echo "Vendedor vinculado\n";

        \App\Models\Vendedor::create([
            'user_id' => $gestor->id,
            'tenant_id' => $tenantId,
            'equipe_id' => $equipe->id,
            'gestor_id' => null,
            'meta_mensal' => 0,
            'chat_enabled' => true,
            'lead_enabled' => true,
        ]);
        echo "Gestor vinculado\n";

        \App\Models\Setting::updateOrCreate(['key' => 'chat_enabled'], ['value' => 'true']);
        \App\Models\Setting::updateOrCreate(['key' => 'lead_round_robin_enabled'], ['value' => 'true']);
        \App\Models\Setting::updateOrCreate(['key' => 'lead_default_equipe_id'], ['value' => (string)$equipe->id]);
        \App\Models\Setting::updateOrCreate(['key' => 'google_ads_webhook_key'], ['value' => 'gads_k9x2mPqR7vLnT4wZ']);

        echo "\n=== USUÁRIOS DE TESTE ===\n";
        echo "Admin:  admin@teste.com  / 123456\n";
        echo "Gestor: gestor@teste.com / 123456\n";
        echo "Vendedor: vendedor@teste.com / 123456\n";
        echo "========================\n";
    }
}