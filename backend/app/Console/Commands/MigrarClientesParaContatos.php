<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Contato;
use Illuminate\Console\Command;

class MigrarClientesParaContatos extends Command
{
    protected $signature = 'sistema:migrar-clientes';
    protected $description = 'Migra clientes existentes para a tabela contatos';

    public function handle(): int
    {
        $clientes = Cliente::all();
        $this->info("Migrando {$clientes->count()} clientes...");

        $bar = $this->output->createProgressBar($clientes->count());
        $bar->start();

        $migrados = 0;
        $ignorados = 0;

        foreach ($clientes as $cliente) {
            if (Contato::where('cliente_id_legado', $cliente->id)->exists()) {
                $ignorados++;
                $bar->advance();
                continue;
            }

            Contato::create([
                'nome' => $cliente->nome,
                'email' => $cliente->email ?? null,
                'telefone' => $cliente->telefone ?? null,
                'whatsapp' => $cliente->whatsapp ?? null,
                'documento' => $cliente->documento ?? null,
                'status' => 'convertido',
                'canal_origem' => 'importacao',
                'entry_date' => $cliente->created_at,
                'nome_igreja' => $cliente->nome_igreja ?? null,
                'nome_pastor' => $cliente->nome_pastor ?? null,
                'nome_responsavel' => $cliente->nome_responsavel ?? null,
                'localidade' => $cliente->localidade ?? null,
                'moeda' => $cliente->moeda ?? null,
                'quantidade_membros' => $cliente->quantidade_membros ?? null,
                'cep' => $cliente->cep ?? null,
                'endereco' => $cliente->endereco ?? null,
                'numero' => $cliente->numero ?? null,
                'complemento' => $cliente->complemento ?? null,
                'bairro' => $cliente->bairro ?? null,
                'cidade' => $cliente->cidade ?? null,
                'estado' => $cliente->estado ?? null,
                'pais' => $cliente->pais ?? 'Brasil',
                'observacoes' => $cliente->observacoes ?? null,
                'cliente_id_legado' => $cliente->id,
            ]);

            $migrados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Migração concluída!");
        $this->info("   • Migrados: {$migrados}");
        $this->info("   • Ignorados (já existentes): {$ignorados}");

        return Command::SUCCESS;
    }
}