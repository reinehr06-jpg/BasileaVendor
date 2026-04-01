<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Meta;
use App\Models\Equipe;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    /**
     * Tela principal de relatórios (master)
     */
    public function index(Request $request)
    {
        $filtros = $this->parseFiltros($request);

        $resumo             = $this->getResumo($filtros);
        $vendasPorVendedor  = $this->getVendasPorVendedor($filtros);
        $metasPorEquipe     = $this->getMetasPorEquipe($filtros);
        $pagamentosPeriodo  = $this->getPagamentosPorPeriodo($filtros);
        $churnRenovacoes    = $this->getChurnRenovacoes($filtros);
        $formasPagamento    = $this->getFormasPagamento($filtros);
        $vendedores         = Vendedor::with('user')->get();
        $clientes           = Cliente::orderBy('nome_igreja')->get();

        // Detectar se há dados gerais no sistema (sem filtros)
        $temDadosNoSistema = Venda::exists() || Pagamento::exists();

        // Detectar se os filtros retornaram algo
        $filtrosRetornaramDados = $resumo['totalVendas'] > 0 || $pagamentosPeriodo['total_pagamentos'] > 0;

        return view('master.relatorios.index', compact(
            'resumo', 'vendasPorVendedor', 'metasPorEquipe', 'pagamentosPeriodo',
            'churnRenovacoes', 'formasPagamento', 'vendedores',
            'clientes', 'filtros', 'temDadosNoSistema', 'filtrosRetornaramDados'
        ));
    }

    /**
     * Parsear filtros do request
     */
    private function parseFiltros(Request $request): array
    {
        return [
            'data_inicio'      => $request->get('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'data_fim'         => $request->get('data_fim', Carbon::now()->format('Y-m-d')),
            'vendedor_id'      => $request->get('vendedor_id'),
            'status'           => $request->get('status'),
            'forma_pagamento'  => $request->get('forma_pagamento'),
            'tipo_negociacao'  => $request->get('tipo_negociacao'),
            'cliente_id'       => $request->get('cliente_id'),
            'recorrencia'      => $request->get('recorrencia'),
        ];
    }

    /**
     * Aplicar filtros comuns a uma query de Venda
     */
    private function applyVendaFilters($query, array $filtros, bool $applyVendedor = true): void
    {
        $query->whereBetween('created_at', [$filtros['data_inicio'], $filtros['data_fim'] . ' 23:59:59']);

        if ($filtros['vendedor_id']) $query->where('vendedor_id', $filtros['vendedor_id']);
        if ($filtros['status']) $query->where('status', $filtros['status']);
        if ($filtros['tipo_negociacao']) $query->where('tipo_negociacao', $filtros['tipo_negociacao']);
        if ($filtros['cliente_id']) $query->where('cliente_id', $filtros['cliente_id']);
        if ($filtros['forma_pagamento']) $query->where('forma_pagamento', $filtros['forma_pagamento']);

        if ($filtros['recorrencia']) {
            $query->whereHas('pagamentos', function ($q) use ($filtros) {
                $q->where('recorrencia_status', $filtros['recorrencia'] === 'ativa' ? 'ativa' : 'inativa');
            });
        }
    }

    /**
     * Aplicar filtros comuns a uma query de Pagamento
     * Regra: Para pagamentos confirmados, usar data_pagamento.
     * Para outros status, usar created_at.
     */
    private function applyPagamentoFilters($query, array $filtros): void
    {
        // Filtrar por data_pagamento se o filtro de status for específico para pagos
        // Caso contrário, usar created_at para manter compatibilidade
        if (isset($filtros['status_pagamento']) && in_array($filtros['status_pagamento'], ['pago', 'RECEIVED', 'CONFIRMED'])) {
            $query->whereBetween('data_pagamento', [$filtros['data_inicio'], $filtros['data_fim']]);
            $query->whereIn('status', ['pago', 'RECEIVED', 'CONFIRMED']);
        } else {
            $query->whereBetween('created_at', [$filtros['data_inicio'], $filtros['data_fim'] . ' 23:59:59']);
        }

        if ($filtros['vendedor_id']) $query->where('vendedor_id', $filtros['vendedor_id']);
        // Filtrar por forma de pagamento (usando forma_pagamento_real quando disponível)
        if ($filtros['forma_pagamento']) {
            $query->where(function($q) use ($filtros) {
                $q->where('forma_pagamento_real', $filtros['forma_pagamento'])
                  ->orWhere(function($q2) use ($filtros) {
                      $q2->whereNull('forma_pagamento_real')
                         ->where('forma_pagamento', $filtros['forma_pagamento']);
                  });
            });
        }
        if ($filtros['cliente_id']) $query->where('cliente_id', $filtros['cliente_id']);
        if ($filtros['recorrencia']) {
            $query->where('recorrencia_status', $filtros['recorrencia'] === 'ativa' ? 'ativa' : 'inativa');
        }
    }

    /**
     * 12.1 — Resumo geral
     */
    private function getResumo(array $filtros): array
    {
        $query = Venda::query();
        $this->applyVendaFilters($query, $filtros);
        $vendas = $query->get();

        // Filtra apenas vendas que não foram canceladas ou expiradas para os totais comerciais
        $vendasEfetivas = $vendas->whereNotIn('status', ['Cancelado', 'Expirado']);

        $totalVendas    = $vendasEfetivas->count();
        $valorVendido   = $vendasEfetivas->sum('valor');
        $valorRecebido  = $vendas->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->sum('valor');
        $totalComissoes = $vendas->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->sum('comissao_gerada');
        $clientesAtivos = $vendas->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->pluck('cliente_id')->unique()->count();
        
        // Nova lógica solicitada pelo usuário:
        // Churn = Vendas canceladas que já tinham sido pagas (comissão > 0)
        $churn = $vendas->whereIn('status', ['Cancelado', 'Estornado', 'Expirado', 'Vencido'])
                        ->where('comissao_gerada', '>', 0)
                        ->count();
                        
        // Desistência = Vendas canceladas que NUNCA foram pagas
        $desistencia = $vendas->whereIn('status', ['Cancelado', 'Expirado', 'Vencido'])
                             ->where('comissao_gerada', '<=', 0)
                             ->count();

        $renovacoes     = $vendas->where('tipo_negociacao', 'anual')->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->count();
        $ticketMedio    = $totalVendas > 0 ? $valorVendido / $totalVendas : 0;

        // Recorrência via Pagamento
        $pgQuery = Pagamento::query();
        $this->applyPagamentoFilters($pgQuery, $filtros);
        $pgQuery->where('status', '!=', 'estornado'); // excluir estornados
        $recorrenciaAtiva = (clone $pgQuery)->where('recorrencia_status', 'ativa')->count();

        return compact(
            'totalVendas', 'valorVendido', 'valorRecebido', 'totalComissoes',
            'clientesAtivos', 'churn', 'desistencia', 'renovacoes', 'ticketMedio', 'recorrenciaAtiva'
        );
    }

    /**
     * 12.2 — Vendas por vendedor
     */
    private function getVendasPorVendedor(array $filtros): array
    {
        $vendedores = Vendedor::with('user')->get();
        $resultado = [];

        foreach ($vendedores as $v) {
            $query = Venda::where('vendedor_id', $v->id);
            $this->applyVendaFilters($query, $filtros, false);
            $vendas = $query->get();

            $vendasEfetivas = $vendas->whereNotIn('status', ['Cancelado', 'Expirado']);

            // Busca meta do mês para este vendedor
            $mesMeta = Carbon::parse($filtros['data_inicio'])->format('Y-m');
            $metaObj = Meta::where('vendedor_id', $v->id)->where('mes_referencia', $mesMeta)->first();
            $valorMeta = $metaObj ? $metaObj->valor_meta : ($v->meta_mensal ?? 0);

            $resultado[] = [
                'vendedor_id'    => $v->id,
                'vendedor_nome'  => $v->user->name ?? 'N/A',
                'total_vendas'   => $vendasEfetivas->count(),
                'valor_vendido'  => $vendasEfetivas->sum('valor'),
                'valor_recebido' => $vendas->filter(fn($v_row) => trim(strtoupper($v_row->status)) === 'PAGO')->sum('valor'),
                'comissao'       => $vendas->filter(fn($v_row) => trim(strtoupper($v_row->status)) === 'PAGO')->sum('comissao_gerada'),
                'clientes_ativos'=> $vendas->filter(fn($v_row) => trim(strtoupper($v_row->status)) === 'PAGO')->pluck('cliente_id')->unique()->count(),
                'churn'          => $vendas->whereIn('status', ['Cancelado', 'Estornado', 'Expirado', 'Vencido'])->where('comissao_gerada', '>', 0)->count(),
                'desistencia'    => $vendas->whereIn('status', ['Cancelado', 'Expirado', 'Vencido'])->where('comissao_gerada', '<=', 0)->count(),
                'meta'           => $valorMeta,
                'percentual_meta'=> ($valorMeta > 0) ? round(($vendasEfetivas->sum('valor') / $valorMeta) * 100, 1) : 0,
            ];
        }

        return $resultado;
    }

    /**
     * 12.3 — Pagamentos por período
     * Regra: 
     * - Valor recebido = soma dos pagamentos com status pago/RECEIVED/CONFIRMED 
     *   filtrados por data_pagamento no período
     * - Pagamentos estornados NÃO entram no total recebido
     */
    private function getPagamentosPorPeriodo(array $filtros): array
    {
        // Query para pagamentos confirmados no período (usando data_pagamento)
        $queryPago = Pagamento::where(function($q) use ($filtros) {
            $q->whereIn('status', ['pago', 'RECEIVED', 'CONFIRMED'])
              ->whereBetween('data_pagamento', [$filtros['data_inicio'], $filtros['data_fim']]);
        });
        
        if ($filtros['vendedor_id']) $queryPago->where('vendedor_id', $filtros['vendedor_id']);
        if ($filtros['forma_pagamento']) $queryPago->where('forma_pagamento', $filtros['forma_pagamento']);
        if ($filtros['cliente_id']) $queryPago->where('cliente_id', $filtros['cliente_id']);
        
        $pagamentosPagos = $queryPago->get();
        
        // Query para todos os pagamentos criados no período (para pendentes/vencidos)
        $queryTodos = Pagamento::whereBetween('created_at', [$filtros['data_inicio'], $filtros['data_fim'] . ' 23:59:59']);
        
        if ($filtros['vendedor_id']) $queryTodos->where('vendedor_id', $filtros['vendedor_id']);
        if ($filtros['forma_pagamento']) $queryTodos->where('forma_pagamento', $filtros['forma_pagamento']);
        if ($filtros['cliente_id']) $queryTodos->where('cliente_id', $filtros['cliente_id']);
        
        $pagamentosTodos = $queryTodos->get();

        return [
            'total_pagamentos' => $pagamentosTodos->count(),
            'total_pago'       => $pagamentosPagos->sum('valor'),
            'total_pendente'   => $pagamentosTodos->whereNotIn('status', ['pago', 'RECEIVED', 'CONFIRMED', 'vencido', 'VENCIDO', 'OVERDUE', 'estornado', 'CANCELED', 'REFUNDED'])
                                                   ->sum('valor'),
            'total_vencido'    => $pagamentosTodos->whereIn('status', ['vencido', 'VENCIDO', 'OVERDUE'])->sum('valor'),
            'valor_recebido'   => $pagamentosPagos->whereNotIn('status', ['estornado', 'CANCELED', 'REFUNDED', 'REFUND_REQUESTED'])
                                                   ->sum('valor'),
        ];
    }

    /**
     * 12.4 — Churn e renovações
     * Regra: Churn = clientes que perderam recorrência ativa ou cancelaram.
     * Renovações = clientes que mantiveram pagamento recorrente.
     */
    private function getChurnRenovacoes(array $filtros): array
    {
        // Vendas
        $queryVendas = Venda::query();
        $this->applyVendaFilters($queryVendas, $filtros);
        $vendas = $queryVendas->get();

        $renovados    = $vendas->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->count();
        $churn        = $vendas->whereIn('status', ['Cancelado', 'Estornado', 'Expirado', 'Vencido'])->where('comissao_gerada', '>', 0)->count();
        $desistencias = $vendas->whereIn('status', ['Cancelado', 'Expirado', 'Vencido'])->where('comissao_gerada', '<=', 0)->count();
        $total        = $renovados + $churn; // Taxa de Churn é baseada em quem já era cliente

        // Recorrência via Pagamento
        $queryPg = Pagamento::query();
        $this->applyPagamentoFilters($queryPg, $filtros);

        $ativos   = (clone $queryPg)->where('recorrencia_status', 'ativa')->distinct('cliente_id')->count('cliente_id');
        $inativos = (clone $queryPg)->whereIn('recorrencia_status', ['inativa', 'cancelada'])->distinct('cliente_id')->count('cliente_id');

        return [
            'renovados'        => $renovados,
            'churn'            => $churn,
            'desistencias'     => $desistencias,
            'churn_percentual' => $total > 0 ? round(($churn / $total) * 100, 1) : 0,
            'ativos'           => $ativos,
            'inativos'         => $inativos,
        ];
    }

    /**
     * 12.6 — Metas por equipe (do gestor)
     */
    private function getMetasPorEquipe(array $filtros): array
    {
        $equipes = Equipe::with(['gestor', 'vendedores.user'])->where('status', 'ativa')->get();
        $resultado = [];

        foreach ($equipes as $equipe) {
            $vendedorIds = $equipe->vendedores->pluck('id')->toArray();
            if (empty($vendedorIds)) {
                $resultado[] = [
                    'equipe_id'        => $equipe->id,
                    'equipe_nome'      => $equipe->nome,
                    'gestor_nome'      => $equipe->gestor->name ?? 'N/A',
                    'total_vendedores' => 0,
                    'total_vendas'     => 0,
                    'valor_vendido'    => 0,
                    'valor_recebido'   => 0,
                    'meta'             => $equipe->meta_mensal,
                    'percentual_meta'  => 0,
                ];
                continue;
            }

            $query = Venda::whereIn('vendedor_id', $vendedorIds);
            $this->applyVendaFilters($query, $filtros, false);
            $vendas = $query->get();

            $vendasEfetivas = $vendas->whereNotIn('status', ['Cancelado', 'Expirado']);
            $valorVendido = $vendasEfetivas->sum('valor');
            $valorRecebido = $vendas->filter(fn($v) => trim(strtoupper($v->status)) === 'PAGO')->sum('valor');

            $resultado[] = [
                'equipe_id'        => $equipe->id,
                'equipe_nome'      => $equipe->nome,
                'gestor_nome'      => $equipe->gestor->name ?? 'N/A',
                'total_vendedores' => $equipe->vendedores->count(),
                'total_vendas'     => $vendasEfetivas->count(),
                'valor_vendido'    => $valorVendido,
                'valor_recebido'   => $valorRecebido,
                'meta'             => $equipe->meta_mensal,
                'percentual_meta'  => $equipe->meta_mensal > 0 ? round(($valorRecebido / $equipe->meta_mensal) * 100, 1) : 0,
            ];
        }

        return $resultado;
    }

    /**
     * 12.5 — Formas de pagamento
     * Regra: Excluir estornados. Usar forma_pagamento_real quando disponível.
     */
    private function getFormasPagamento(array $filtros): array
    {
        // Regra: Somente pagamentos confirmados/pagos entram no rateio por forma
        $query = Pagamento::whereIn('status', ['pago', 'RECEIVED', 'CONFIRMED']);
        $this->applyPagamentoFilters($query, $filtros);
        $pagamentos = $query->get();
        $total = $pagamentos->count();

        $formas = ['pix', 'boleto', 'cartao', 'recorrente'];
        $resultado = [];

        foreach ($formas as $forma) {
            // Usar forma_pagamento_real quando disponível, senão usar forma_pagamento
            $grupo = $pagamentos->filter(function ($p) use ($forma) {
                $formaUsada = $p->forma_pagamento_real ?: $p->forma_pagamento;
                return $formaUsada === $forma;
            });
            $resultado[] = [
                'forma'      => $forma,
                'quantidade' => $grupo->count(),
                'valor_total'=> $grupo->sum('valor'),
                'percentual' => $total > 0 ? round(($grupo->count() / $total) * 100, 1) : 0,
            ];
        }

        return $resultado;
    }

    /**
     * 12.1 — Resumo geral (JSON)
     */
    public function apiResumo(Request $request)
    {
        return response()->json($this->getResumo($this->parseFiltros($request)));
    }

    /**
     * 12.2 — Vendas por vendedor (JSON)
     */
    public function apiVendasPorVendedor(Request $request)
    {
        return response()->json($this->getVendasPorVendedor($this->parseFiltros($request)));
    }

    /**
     * 12.3 — Pagamentos por período (JSON)
     */
    public function apiPagamentos(Request $request)
    {
        return response()->json($this->getPagamentosPorPeriodo($this->parseFiltros($request)));
    }

    /**
     * 12.4 — Churn e renovações (JSON)
     */
    public function apiChurnRenovacoes(Request $request)
    {
        return response()->json($this->getChurnRenovacoes($this->parseFiltros($request)));
    }

    /**
     * 12.5 — Formas de pagamento (JSON)
     */
    public function apiFormasPagamento(Request $request)
    {
        return response()->json($this->getFormasPagamento($this->parseFiltros($request)));
    }

    /**
     * 12.6 — Metas por equipe (JSON)
     */
    public function apiMetasPorEquipe(Request $request)
    {
        return response()->json($this->getMetasPorEquipe($this->parseFiltros($request)));
    }

    /**
     * 12.6 — Exportar CSV
     */
    public function exportar(Request $request)
    {
        $filtros = $this->parseFiltros($request);
        $tipo = $request->get('tipo_relatorio', 'vendas');

        $filename = "relatorio_{$tipo}_" . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $query = Venda::with(['cliente', 'vendedor.user']);
        $this->applyVendaFilters($query, $filtros);
        $vendas = $query->get();

        $callback = function () use ($vendas) {
            $file = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'ID', 'Igreja', 'Pastor', 'Vendedor', 'Plano', 'Valor',
                'Desconto %', 'Comissão', 'Status', 'Forma Pagamento',
                'Tipo Negociação', 'Data Venda'
            ], ';');

            foreach ($vendas as $v) {
                fputcsv($file, [
                    $v->id,
                    $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                    $v->cliente->nome_pastor ?? '—',
                    $v->vendedor->user->name ?? 'N/A',
                    $v->plano ?? 'N/A',
                    number_format($v->valor, 2, ',', '.'),
                    $v->desconto ?? 0,
                    number_format($v->comissao_gerada ?? 0, 2, ',', '.'),
                    $v->status,
                    $v->forma_pagamento ?? '—',
                    $v->tipo_negociacao ?? '—',
                    $v->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
