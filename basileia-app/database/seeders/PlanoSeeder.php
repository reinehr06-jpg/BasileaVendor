<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar tabela existente
        DB::table('planos')->truncate();

        // Inserir novos planos
        DB::table('planos')->insert([
            [
                'nome' => 'Start',
                'faixa_min_membros' => 1,
                'faixa_max_membros' => 100,
                'valor_mensal' => 197.00,
                'valor_anual' => 1548.00,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Basic',
                'faixa_min_membros' => 101,
                'faixa_max_membros' => 300,
                'valor_mensal' => 297.00,
                'valor_anual' => 2748.00,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Plus',
                'faixa_min_membros' => 301,
                'faixa_max_membros' => 500,
                'valor_mensal' => 397.00,
                'valor_anual' => 3948.00,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Performance',
                'faixa_min_membros' => 501,
                'faixa_max_membros' => 99999,
                'valor_mensal' => 0.00,
                'valor_anual' => 0.00,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}