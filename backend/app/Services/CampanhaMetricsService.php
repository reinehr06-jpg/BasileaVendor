<?php

namespace App\Services;

use App\Models\Campanha;
use App\Models\Contato;

class CampanhaMetricsService
{
    public function calcularTaxaConversao(Campanha $campanha): float
    {
        $total = $campanha->contatos()->count();
        if ($total === 0) {
            return 0.0;
        }
        $convertidos = $campanha->contatos()->where('status', 'convertido')->count();
        return round(($convertidos / $total) * 100, 2);
    }

    public function calcularCPL(Campanha $campanha): ?float
    {
        $total = $campanha->contatos()->count();
        $custoTotal = $campanha->custo_total;
        if (!$custoTotal || $total === 0) {
            return null;
        }
        return round($custoTotal / $total, 2);
    }

    public function getUltimoLead(Campanha $campanha): ?string
    {
        return $campanha->contatos()
            ->latest('entry_date')
            ->value('entry_date');
    }

    public function getFunil(Campanha $campanha): array
    {
        $query = $campanha->contatos();
        return [
            'total'       => $query->count(),
            'atendidos'   => $query->whereNotNull('agente_id')->count(),
            'convertidos' => $query->where('status', 'convertido')->count(),
            'perdidos'    => $query->whereIn('status', ['perdido', 'lead_ruim'])->count(),
        ];
    }

    public function getLeadsPorDia(Campanha $campanha, int $dias = 30)
    {
        return $campanha->contatos()
            ->selectRaw('CAST(entry_date AS DATE) as dia, COUNT(*) as total')
            ->where('entry_date', '>=', now()->subDays($dias))
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');
    }

    public function getPorCanal(Campanha $campanha)
    {
        return $campanha->contatos()
            ->selectRaw('canal_origem, COUNT(*) as total')
            ->groupBy('canal_origem')
            ->pluck('total', 'canal_origem');
    }

    public function getPorAgente(Campanha $campanha)
    {
        return $campanha->contatos()
            ->selectRaw('agente_id, COUNT(*) as total_leads,
                         SUM(CASE WHEN status = \'convertido\' THEN 1 ELSE 0 END) as convertidos')
            ->whereNotNull('agente_id')
            ->with('agente')
            ->groupBy('agente_id')
            ->get();
    }

    public function getMetricasGlobais(): array
    {
        $totalLeads = Contato::count();
        $totalConvertidos = Contato::where('status', 'convertido')->count();
        $taxaGeral = $totalLeads > 0
            ? round(($totalConvertidos / $totalLeads) * 100, 2)
            : 0;
        $campanhasAtivas = Campanha::where('status', 'ativa')->count();

        return [
            'total_leads'       => $totalLeads,
            'total_convertidos' => $totalConvertidos,
            'taxa_geral'        => $taxaGeral,
            'campanhas_ativas'  => $campanhasAtivas,
        ];
    }
}
