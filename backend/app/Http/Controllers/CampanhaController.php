<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\Contato;
use App\Services\CampanhaMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampanhaController extends Controller
{
    private CampanhaMetricsService $metrics;

    public function __construct(CampanhaMetricsService $metrics)
    {
        $this->metrics = $metrics;
    }

    // Listagem com métricas em tempo real
    public function index(Request $request)
    {
        $query = Campanha::with('criador')
            ->withCount([
                'contatos as total_leads',
                'contatos as total_convertidos' => fn($q) => $q->where('status', 'convertido'),
                'contatos as total_perdidos'    => fn($q) => $q->whereIn('status', ['perdido', 'lead_ruim']),
            ]);

        // Filtros
        if ($request->status)  $query->where('status', $request->status);
        if ($request->canal)   $query->where('canal', $request->canal);
        if ($request->periodo_inicio && $request->periodo_fim) {
            $query->whereBetween('data_inicio', [$request->periodo_inicio, $request->periodo_fim]);
        }

        // Ordenação
        $ordem = $request->ordem ?? 'created_at';
        $query->orderByDesc($ordem);

        $campanhas = $query->get()->map(function ($c) {
            $c->taxa_conversao = $this->metrics->calcularTaxaConversao($c);
            $c->cpl = $this->metrics->calcularCPL($c);
            $c->ultimo_lead = $this->metrics->getUltimoLead($c);
            return $c;
        });

        // KPIs globais para o topo
        $kpis = $this->metrics->getMetricasGlobais();

        return view('admin.campanhas.index', compact('campanhas', 'kpis'));
    }

    // Detalhes de uma campanha — funil + gráfico + lista de leads
    public function show(Campanha $campanha)
    {
        $funil = $this->metrics->getFunil($campanha);
        $leadsPorDia = $this->metrics->getLeadsPorDia($campanha);
        $porCanal = $this->metrics->getPorCanal($campanha);
        $porAgente = $this->metrics->getPorAgente($campanha);

        $leads = $campanha->contatos()
            ->with('agente', 'vendedor')
            ->orderByDesc('entry_date')
            ->paginate(25);

        $taxaConversao = $funil['total'] > 0
            ? round(($funil['convertidos'] / $funil['total']) * 100, 2)
            : 0;

        $tempoMedio = $campanha->tempoMedioConversao();

        return view('admin.campanhas.show', compact(
            'campanha', 'funil', 'leadsPorDia',
            'porCanal', 'porAgente', 'leads',
            'taxaConversao', 'tempoMedio'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'       => 'required|string|max:255',
            'canal'      => 'required|in:meta_ads,google_ads,whatsapp_link,instagram,tiktok_ads,formulario_web,landing_page,organico,importacao,outro',
            'status'     => 'required|in:ativa,pausada,encerrada',
            'data_inicio' => 'nullable|date',
            'data_fim'    => 'nullable|date|after_or_equal:data_inicio',
        ]);

        Campanha::create([...$request->only([
            'nome', 'descricao', 'canal', 'status', 'data_inicio', 'data_fim',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
            'ref_param', 'custo_total', 'moeda',
        ]), 'criado_por' => Auth::id()]);

        return back()->with('success', 'Campanha criada!');
    }

    public function update(Request $request, Campanha $campanha)
    {
        $campanha->update($request->only([
            'nome', 'descricao', 'canal', 'status', 'data_inicio', 'data_fim',
            'utm_source', 'utm_medium', 'utm_campaign', 'custo_total',
        ]));

        return back()->with('success', 'Campanha atualizada!');
    }

    // Endpoint para dados em tempo real via AJAX/polling
    public function metricas(Campanha $campanha)
    {
        return response()->json([
            'total_leads'       => $campanha->contatos()->count(),
            'convertidos'       => $campanha->contatos()->where('status', 'convertido')->count(),
            'perdidos'          => $campanha->contatos()->whereIn('status', ['perdido', 'lead_ruim'])->count(),
            'taxa_conversao'    => $this->metrics->calcularTaxaConversao($campanha),
            'cpl'               => $this->metrics->calcularCPL($campanha),
            'ultimo_lead'       => $this->metrics->getUltimoLead($campanha),
        ]);
    }
}
