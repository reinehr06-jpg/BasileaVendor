<?php

namespace App\Services;

use App\Models\Vendedor;
use App\Models\Contato;
use Illuminate\Support\Facades\Log;

class AtribuicaoLeadService
{
    public function atribuir(Contato $contato): ?int
    {
        $vendedores = Vendedor::where('status', 'ativo')
            ->whereHas('user', function ($q) {
                $q->where('perfil', 'vendedor');
            })
            ->withCount([
                'contatos as leads_hoje' => function ($q) {
                    $q->whereDate('entry_date', now()->toDateString());
                }
            ])
            ->orderBy('leads_hoje')
            ->get();

        if ($vendedores->isEmpty()) {
            Log::warning('ATRIBUICAO_LEAD_NENHUM_VENDEDOR', [
                'contato_id' => $contato->id,
                'mensagem' => 'Nenhum vendedor ativo encontrado para atribuição'
            ]);
            return null;
        }

        $vendedorSelecionado = $vendedores->first();

        $contato->update([
            'vendedor_id' => $vendedorSelecionado->id,
        ]);

        Log::info('LEAD_ATRIBUIDO', [
            'contato_id' => $contato->id,
            'vendedor_id' => $vendedorSelecionado->id,
            'vendedor_nome' => $vendedorSelecionado->user->name,
            'leads_hoje' => $vendedorSelecionado->leads_hoje + 1,
        ]);

        return $vendedorSelecionado->id;
    }

    public function atribuirPorGestor(Contato $contato, int $gestorId): ?int
    {
        $vendedores = Vendedor::where('status', 'ativo')
            ->where('gestor_id', $gestorId)
            ->withCount([
                'contatos as leads_hoje' => function ($q) {
                    $q->whereDate('entry_date', now()->toDateString());
                }
            ])
            ->orderBy('leads_hoje')
            ->get();

        if ($vendedores->isEmpty()) {
            return null;
        }

        $vendedorSelecionado = $vendedores->first();

        $contato->update([
            'vendedor_id' => $vendedorSelecionado->id,
            'gestor_id' => $gestorId,
        ]);

        return $vendedorSelecionado->id;
    }
}