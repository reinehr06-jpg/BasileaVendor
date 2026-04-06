<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimparBancoCommand extends Command
{
    protected $signature = 'db:limpar';
    protected $description = 'Limpa o banco de dados mantendo apenas o usuário admin';

    public function handle(): int
    {
        $this->info('Iniciando limpeza do banco de dados...');
        $this->info('Mantendo apenas o usuário admin...');

        // Desabilitar foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Tabelas em ordem (dependências primeiro)
        $tabelas = [
            'comissaos',
            'comissoes',
            'pagamentos',
            'cobrancas',
            'vendas',
            'clientes',
            'vendedores',
            'planos',
            'settings',
            'users',
            'migrations',
        ];

        // Truncar cada tabela
        foreach ($tabelas as $tabela) {
            if (Schema::hasTable($tabela)) {
                DB::table($tabela)->truncate();
                $this->line("✓ Tabela '{$tabela}' limpa");
            }
        }

        // Manter apenas o primeiro usuário (admin)
        $admin = DB::table('users')->first();
        if ($admin) {
            DB::table('users')->where('id', '!=', $admin->id)->delete();
            $this->info("Mantido usuário admin: {$admin->email}");
        }

        // Reabilitar foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Banco de dados limpo com sucesso!');
        $this->info('Apenas o usuário admin foi mantido.');

        return Command::SUCCESS;
    }
}
