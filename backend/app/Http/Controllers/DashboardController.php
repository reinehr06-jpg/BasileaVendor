<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Pagamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $periodo = $request->get('periodo', 'month');
        
        switch ($periodo) {
            case 'week':
                $dataInicio = Carbon::now()->startOfWeek();
                $dataFim = Carbon::now();
                $dataInicioComp = Carbon::now()->subWeek()->startOfWeek();
                $dataFimComp = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'year':
                $dataInicio = Carbon::now()->startOfYear();
                $dataFim = Carbon::now();
                $dataInicioComp = Carbon::now()->subYear()->startOfYear();
                $dataFimComp = Carbon::now()->subYear()->endOfYear();
                break;
            default:
                $dataInicio = Carbon::now()->startOfMonth();
                $dataFim = Carbon::now();
                $dataInicioComp = Carbon::now()->subMonth()->startOfMonth();
                $dataFimComp = Carbon::now()->subMonth()->endOfMonth();
        }

        $vendedorIds = null;
        $isPersonal = false;
        
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$user->vendedor->id ?? 0];
            $isPersonal = true;
        } elseif ($user->perfil === 'gestor') {
            $vendedorIds = Vendedor::where('gestor_id', $user->id)
                ->orWhere('usuario_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        $queryVendasAtivas = Venda::whereRaw('UPPER(status) IN (?, ?)', ['PAGO', 'PAGO_ASAAS'])
            ->whereBetween('updated_at', [$dataInicio, $dataFim]);
        if ($vendedorIds) $queryVendasAtivas->whereIn('vendedor_id', $vendedorIds);
        $vendasAtivas = $queryVendasAtivas->count();

        $queryVendasPassado = Venda::whereRaw('UPPER(status) IN (?, ?)', ['PAGO', 'PAGO_ASAAS'])
            ->whereBetween('updated_at', [$dataInicioComp, $dataFimComp]);
        if ($vendedorIds) $queryVendasPassado->whereIn('vendedor_id', $vendedorIds);
        $vendasPassado = $queryVendasPassado->count();
        $vendasTrend = $vendasPassado > 0 ? (($vendasAtivas - $vendasPassado) / $vendasPassado) * 100 : 0;

        $vendedoresAtivos = 0;
        if ($user->perfil === 'master') {
            $vendedoresAtivos = User::where('perfil', 'vendedor')->whereRaw('UPPER(status) = ?', ['ATIVO'])->count();
        } elseif ($user->perfil === 'gestor') {
            $vendedoresAtivos = Vendedor::where('gestor_id', $user->id)->count();
        }

        $queryComisPend = Venda::whereRaw('UPPER(status) IN (?, ?)', ['PAGO', 'PAGO_ASAAS'])
            ->whereBetween('updated_at', [$dataInicio, $dataFim]);
        if ($vendedorIds) $queryComisPend->whereIn('vendedor_id', $vendedorIds);
        $comissoesPendentes = $queryComisPend->sum('comissao_gerada');
        $queryContagemPendentes = clone $queryComisPend;
        $contagemPendentes = $queryContagemPendentes->where('comissao_gerada', '>', 0)->count();

        $queryPagamentos = Pagamento::whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
            ->whereBetween('pagamentos.updated_at', [$dataInicio, $dataFim])
            ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
            ->whereRaw('UPPER(vendas.status) NOT IN (?, ?, ?)', ['ESTORNADO', 'CANCELADO', 'EXPIRADO']);
        if ($vendedorIds) $queryPagamentos->whereIn('vendas.vendedor_id', $vendedorIds);
        $totalRecebido = $queryPagamentos->sum('pagamentos.valor');

        $queryPagamentosPassado = Pagamento::whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
            ->whereBetween('pagamentos.updated_at', [$dataInicioComp, $dataFimComp])
            ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
            ->whereRaw('UPPER(vendas.status) NOT IN (?, ?, ?)', ['ESTORNADO', 'CANCELADO', 'EXPIRADO']);
        if ($vendedorIds) $queryPagamentosPassado->whereIn('vendas.vendedor_id', $vendedorIds);
        $recebidoPassado = $queryPagamentosPassado->sum('pagamentos.valor');
        $recebidoTrend = $recebidoPassado > 0 ? (($totalRecebido - $recebidoPassado) / $recebidoPassado) * 100 : 0;

        $queryClientes = Cliente::whereHas('vendas.pagamentos', function($q) use ($vendedorIds, $dataInicio, $dataFim) {
            $q->whereIn('status', ['RECEIVED', 'CONFIRMED', 'pago', 'PAGO'])
              ->whereBetween('pagamentos.updated_at', [$dataInicio, $dataFim]);
            if ($vendedorIds) $q->whereIn('vendas.vendedor_id', $vendedorIds);
        });
        
        // Integrar clientes legados ativos
        $queryLegacyAtivos = DB::table('legacy_customer_imports')
            ->where('diagnostico_status', 'ATIVO')
            ->whereNull('local_cliente_id')
            ->whereNotNull('primeiro_pagamento_at');
        if ($vendedorIds) $queryLegacyAtivos->whereIn('vendedor_id', $vendedorIds);
        
        $legacyCount = $queryLegacyAtivos->count();
        $clientesAtivos = $queryClientes->count() + $legacyCount;

        // Integrar faturamento legado (Mês atual/selecionado)
        $totalLegacyRecebido = 0;
        $legacyRows = DB::table('legacy_customer_imports')
            ->where('diagnostico_status', 'ATIVO')
            ->whereNull('local_cliente_id')
            ->whereNotNull('primeiro_pagamento_at');
        if ($vendedorIds) $legacyRows->whereIn('vendedor_id', $vendedorIds);
        $legacyRows = $legacyRows->get();

        foreach ($legacyRows as $row) {
            if (($row->tipo_cobranca ?? '') === 'installment') {
                $totalLegacyRecebido += (float) ($row->valor_total_cobranca ?? (($row->valor_plano_mensal ?? 0) * ($row->parcelas_total ?? 1)));
            } else {
                $totalLegacyRecebido += (float) ($row->valor_plano_mensal ?? 0);
            }
        }
        $totalRecebido = (float) $totalRecebido + $totalLegacyRecebido;

        $queryChurn = Venda::whereIn('status', ['Estornado', 'Cancelado', 'Expirado', 'Vencido'])
            ->where('comissao_gerada', '>', 0)
            ->whereBetween('updated_at', [$dataInicio, $dataFim]);
        if ($vendedorIds) $queryChurn->whereIn('vendedor_id', $vendedorIds);
        $churnMes = $queryChurn->count();

        $queryRenov = Cobranca::whereIn('cobrancas.status', ['RECEIVED', 'pago', 'PAGO'])
            ->whereBetween('cobrancas.created_at', [$dataInicio, $dataFim])
            ->join('vendas', 'cobrancas.venda_id', '=', 'vendas.id');
        if ($vendedorIds) $queryRenov->whereIn('vendas.vendedor_id', $vendedorIds);
        $renovacoesMes = $queryRenov->count();

        $ticketMedio = ($vendasAtivas + $legacyCount) > 0 
            ? $totalRecebido / ($vendasAtivas + $legacyCount) 
            : 0;

        $driver = DB::getDriverName();
        $graficoData = [];
        
        if ($periodo === 'week') {
            $dayFormat = $driver === 'pgsql' ? "TO_CHAR(pagamentos.updated_at, 'YYYY-MM-DD')" : "DATE(pagamentos.updated_at)";
            $graficoRaw = Pagamento::selectRaw("$dayFormat as dia, sum(pagamentos.valor) as total")
                ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
                ->whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
                ->whereRaw('UPPER(vendas.status) NOT IN (?, ?, ?)', ['ESTORNADO', 'CANCELADO', 'EXPIRADO'])
                ->whereBetween('pagamentos.updated_at', [$dataInicio, $dataFim]);
            if ($vendedorIds) $graficoRaw->whereIn('vendas.vendedor_id', $vendedorIds);
            $graficoData = $graficoRaw->groupByRaw($dayFormat)->orderByRaw($dayFormat)->get()->map(function($row) {
                return ['label' => Carbon::parse($row->dia)->format('d/m'), 'total' => $row->total];
            });
        } elseif ($periodo === 'year') {
            $monthFormat = $driver === 'pgsql' ? "TO_CHAR(pagamentos.updated_at, 'YYYY-MM')" : "strftime('%Y-%m', pagamentos.updated_at)";
            $graficoRaw = Pagamento::selectRaw("$monthFormat as mes, sum(pagamentos.valor) as total")
                ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
                ->whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
                ->whereRaw('UPPER(vendas.status) NOT IN (?, ?, ?)', ['ESTORNADO', 'CANCELADO', 'EXPIRADO'])
                ->whereBetween('pagamentos.updated_at', [$dataInicio, $dataFim]);
            if ($vendedorIds) $graficoRaw->whereIn('vendas.vendedor_id', $vendedorIds);
            $graficoData = $graficoRaw->groupByRaw($monthFormat)->orderByRaw($monthFormat)->get()->map(function($row) {
                return ['label' => Carbon::parse($row->mes . '-01')->format('M/Y'), 'total' => $row->total];
            });
        } else {
            $weekFormat = $driver === 'pgsql' ? "TO_CHAR(pagamentos.updated_at, 'IW')" : "strftime('%W', pagamentos.updated_at)";
            $graficoRaw = Pagamento::selectRaw("$weekFormat as semana, sum(pagamentos.valor) as total")
                ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
                ->whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
                ->whereRaw('UPPER(vendas.status) NOT IN (?, ?, ?)', ['ESTORNADO', 'CANCELADO', 'EXPIRADO'])
                ->whereBetween('pagamentos.updated_at', [$dataInicio, $dataFim]);
            if ($vendedorIds) $graficoRaw->whereIn('vendas.vendedor_id', $vendedorIds);
            $graficoData = $graficoRaw->groupByRaw($weekFormat)->orderByRaw($weekFormat)->get()->map(function($row) {
                return ['label' => 'Sem ' . $row->semana, 'total' => $row->total];
            });
        }

        $dayFormat = $driver === 'pgsql' ? "TO_CHAR(cobrancas.updated_at, 'DD')" : "strftime('%d', cobrancas.updated_at)";
        $queryFaixa = Cobranca::selectRaw("$dayFormat as dia, count(*) as total")
            ->join('vendas', 'cobrancas.venda_id', '=', 'vendas.id')
            ->whereBetween('cobrancas.updated_at', [$dataInicio, $dataFim])
            ->where('cobrancas.status', 'RECEIVED');
        if ($vendedorIds) $queryFaixa->whereIn('vendas.vendedor_id', $vendedorIds);
        $historicoDias = $queryFaixa->groupByRaw($dayFormat)->orderByDesc('total')->first();
        
        $melhorFaixa = "Sem dados";
        if ($historicoDias && $historicoDias->dia) {
            $dia = (int)$historicoDias->dia;
            if ($dia <= 10) $melhorFaixa = "Dias 01 a 10";
            elseif ($dia <= 20) $melhorFaixa = "Dias 11 a 20";
            else $melhorFaixa = "Dias 21 a 31";
        }

        $tituloSessao = match($user->perfil) {
            'master' => 'Visão Global da Operação',
            'gestor' => 'Performance da Equipe',
            default => 'Minha Performance Individual'
        };

        $periodoLabel = match($periodo) {
            'week' => 'Última Semana',
            'year' => 'Último Ano',
            default => 'Último Mês'
        };

        return view('dashboard', compact(
            'vendasAtivas', 'vendedoresAtivos', 'comissoesPendentes', 
            'totalRecebido', 'clientesAtivos', 'churnMes',
            'melhorFaixa', 'renovacoesMes', 'vendasTrend', 'recebidoTrend',
            'contagemPendentes', 'graficoData', 'tituloSessao', 'isPersonal',
            'periodo', 'periodoLabel', 'ticketMedio'
        ));
    }
}
