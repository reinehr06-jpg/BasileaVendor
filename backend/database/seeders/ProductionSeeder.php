<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Venda;
use Faker\Factory as Faker;

class ProductionSeeder extends Seeder
{
    /**
     * Semeia o banco com grande volume de dados simulados (Stress Test & Chart Preview)
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        $this->command->info('Iniciando Seeder de Produção Massivo...');
        $this->command->warn('Esta ação pode demorar alguns minutos. Pegue um café!');

        // 1. Garante que o Master existe
        $master = User::where('perfil', 'master')->first();
        if (!$master) {
            $master = User::factory()->create(['perfil' => 'master', 'email' => 'master@basileia.global']);
        }

        // 2. Cria 5 Vendedores e 2 Gestores
        $vendedores = User::factory()->count(5)->create(['perfil' => 'vendedor']);
        $gestores = User::factory()->count(2)->create(['perfil' => 'gestor']);

        // 3. Cria 500 Clientes distribuídos entre os Vendedores
        $clientes = [];
        for ($i = 0; $i < 500; $i++) {
            $clientes[] = [
                'user_id' => $vendedores->random()->id,
                'nome' => $faker->company,
                'nome_igreja' => 'Igreja ' . $faker->city,
                'nome_responsavel' => $faker->name,
                'documento' => $faker->cnpj(false),
                'email' => $faker->unique()->companyEmail,
                'telefone' => $faker->cellphone(false),
                'whatsapp' => $faker->cellphone(false),
                'endereco' => $faker->streetAddress,
                'cep' => $faker->postcode,
                'status' => $faker->randomElement(['ACTIVE', 'INACTIVE', 'PENDING']),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ];
        }

        // Chunk insert para não explodir a memória do PHP
        foreach (array_chunk($clientes, 100) as $chunk) {
            DB::table('clientes')->insert($chunk);
        }
        $this->command->info('500 Clientes criados!');

        // 4. Cria 1500 Vendas espalhadas pelo último 1 ano
        $todasIdsClientes = DB::table('clientes')->pluck('id')->toArray();
        $planos = ['BASICO', 'PRO', 'ENTERPRISE'];
        $ciclos = ['MENSAL', 'ANUAL'];

        $vendas = [];
        for ($i = 0; $i < 1500; $i++) {
            $dataVenda = $faker->dateTimeBetween('-1 year', 'now');
            $vendaStatus = $faker->randomElement(['ATIVA', 'CANCELADA', 'INADIMPLENTE', 'PENDENTE']);
            $ciclo = $faker->randomElement($ciclos);
            
            // Valor aleatório baseado no plano
            $valor = $faker->randomFloat(2, 99, 499);
            if ($ciclo === 'ANUAL') {
                $valor = $valor * 10;
            }

            $vendas[] = [
                'cliente_id' => $faker->randomElement($todasIdsClientes),
                'user_id' => $vendedores->random()->id, // Vendedor que fechou a venda
                'asaas_subscription_id' => 'sub_' . $faker->regexify('[A-Za-z0-9]{16}'),
                'plano' => $faker->randomElement($planos),
                'ciclo' => $ciclo,
                'valor' => $valor,
                'status' => $vendaStatus,
                'data_venda' => $dataVenda,
                'proximo_vencimento' => $vendaStatus === 'ATIVA' ? $faker->dateTimeBetween('now', '+1 month') : null,
                'created_at' => $dataVenda,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($vendas, 200) as $chunk) {
            DB::table('vendas')->insert($chunk);
        }
        $this->command->info('1500 Vendas criadas e distribuídas nos gráficos!');
        $this->command->info('Seeder de Produção concluído com Sucesso! Seu painel agora está populado.');
    }
}
