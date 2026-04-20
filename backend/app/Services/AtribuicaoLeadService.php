<?php

namespace App\Services;

use App\Models\Contato;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Log;

class AtribuicaoLeadService
{
    /**
     * Atribui lead automaticamente para o vendedor com menos leads hoje (Round Robin)
     */
    public function atribuirAutomaticamente(Contato $contato): void
    {
        // Só atribui se ainda não tem responsável
        if ($contato->agente_id || $contato->vendedor_id) {
            return;
        }

        $vendedor = $this->encontrarMelhorVendedor();

        if ($vendedor) {
            $contato->update([
                'agente_id' => $vendedor->id,
                'vendedor_id' => $vendedor->id,
            ]);

            Log::info('Lead atribuído automaticamente', [
                'contato_id' => $contato->id,
                'vendedor_id' => $vendedor->id,
                'vendedor_nome' => $vendedor->user->name,
            ]);
        }
    }

    /**
     * Encontra o vendedor com menos leads no dia atual
     */
    private function encontrarMelhorVendedor()
    {
        return Vendedor::with('user')
            ->withCount([
                'contatos as leads_hoje' => fn($q) =>
                    $q->whereDate('created_at', today())
            ])
            ->where('status', 'ativo')
            ->whereHas('user', fn($q) => $q->where('status', 'ativo'))
            ->orderBy('leads_hoje')
            ->orderBy('created_at') // Para desempate consistente
            ->first();
    }

    /**
     * Reatribui leads de um vendedor que saiu/ficou inativo
     */
    public function reatribuirLeadsDoVendedor(int $vendedorId): int
    {
        $leadsParaReatribuir = Contato::where('vendedor_id', $vendedorId)
            ->where('status', 'lead')
            ->get();

        $reatribuidos = 0;

        foreach ($leadsParaReatribuir as $contato) {
            $novoVendedor = $this->encontrarMelhorVendedor();

            if ($novoVendedor && $novoVendedor->id !== $vendedorId) {
                $contato->update([
                    'agente_id' => $novoVendedor->id,
                    'vendedor_id' => $novoVendedor->id,
                ]);
                $reatribuidos++;
            }
        }

        Log::info('Reatribuição de leads', [
            'vendedor_antigo_id' => $vendedorId,
            'leads_reatribuidos' => $reatribuidos,
        ]);

        return $reatribuidos;
    }

    /**
     * Estatísticas de distribuição de leads
     */
    public function getEstatisticasDistribuicao(): array
    {
        $vendedores = Vendedor::with('user')
            ->withCount([
                'contatos as total_leads',
                'contatos as leads_hoje' => fn($q) => $q->whereDate('created_at', today()),
                'contatos as leads_semana' => fn($q) => $q->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]),
                'contatos as leads_convertidos' => fn($q) => $q->where('status', 'convertido'),
            ])
            ->where('status', 'ativo')
            ->get();

        return [
            'total_vendedores_ativos' => $vendedores->count(),
            'distribuicao_por_vendedor' => $vendedores->map(function ($vendedor) {
                return [
                    'id' => $vendedor->id,
                    'nome' => $vendedor->user->name,
                    'total_leads' => $vendedor->total_leads,
                    'leads_hoje' => $vendedor->leads_hoje,
                    'leads_semana' => $vendedor->leads_semana,
                    'taxa_conversao' => $vendedor->total_leads > 0
                        ? round(($vendedor->leads_convertidos / $vendedor->total_leads) * 100, 1)
                        : 0,
                ];
            }),
            'leads_sem_responsavel' => Contato::whereNull('vendedor_id')
                ->where('status', 'lead')
                ->count(),
        ];
    }
}
