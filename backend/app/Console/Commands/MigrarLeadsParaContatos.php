<?php

namespace App\Console\Commands;

use App\Models\Contato;
use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarLeadsParaContatos extends Command
{
    protected $signature   = 'sistema:migrar-leads {--dry-run : Executa sem salvar no banco}';
    protected $description = 'Migra leads existentes para a nova tabela contatos';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 MODO DRY-RUN: Nenhuma alteração será feita no banco');
        }

        $leads = Lead::all();

        if ($leads->isEmpty()) {
            $this->info('✅ Nenhum lead encontrado para migração');
            return;
        }

        $this->info("📊 Encontrados {$leads->count()} leads para migração");
        $this->newLine();

        $migrados = 0;
        $erros = 0;

        $bar = $this->output->createProgressBar($leads->count());
        $bar->start();

        foreach ($leads as $lead) {
            try {
                // Verificar se já foi migrado
                $jaMigrado = Contato::where('cliente_id_legado', $lead->id)->exists();

                if ($jaMigrado) {
                    $this->warn("Lead {$lead->id} já foi migrado anteriormente");
                    $bar->advance();
                    continue;
                }

                $dadosContato = [
                    'nome'              => $lead->name,
                    'email'             => $lead->email,
                    'telefone'          => $lead->phone,
                    'whatsapp'          => $lead->phone, // assumindo que phone é whatsapp
                    'documento'         => $lead->document,
                    'status'            => $this->mapearStatus($lead->status),
                    'canal_origem'      => $lead->source ?? 'migracao',
                    'entry_date'        => $lead->created_at,
                    'cliente_id_legado' => $lead->id,

                    // Campos específicos da igreja
                    'nome_igreja'       => $lead->church_name,
                    'quantidade_membros' => $lead->members_count,
                    'moeda'             => $lead->currency,
                    'localidade'        => $lead->referrer,
                ];

                if (!$dryRun) {
                    Contato::create($dadosContato);
                }

                $migrados++;

            } catch (\Exception $e) {
                $this->error("Erro ao migrar lead {$lead->id}: {$e->getMessage()}");
                $erros++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Migração concluída!");
        $this->info("📊 Total de leads processados: {$leads->count()}");
        $this->info("✅ Leads migrados com sucesso: {$migrados}");
        $this->info("❌ Leads com erro: {$erros}");

        if ($dryRun) {
            $this->warn("🔍 Foi executado em modo dry-run. Execute sem --dry-run para fazer as alterações.");
        } else {
            $this->info("💡 Próximos passos:");
            $this->info("   1. Verifique se os dados foram migrados corretamente");
            $this->info("   2. Teste o sistema de campanhas e contatos");
            $this->info("   3. Considere remover a tabela leads após período de testes");
        }
    }

    private function mapearStatus(?string $statusLead): string
    {
        return match ($statusLead) {
            'novo' => 'lead',
            'contato' => 'lead',
            'proposta' => 'convertido',
            'ganho' => 'convertido',
            'perdido' => 'perdido',
            'lead_ruim' => 'lead_ruim',
            default => 'lead',
        };
    }
}
