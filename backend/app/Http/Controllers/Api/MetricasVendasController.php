<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Cliente;
use Carbon\Carbon;

class MetricasVendasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Venda::query();
        if (! $isGestor && $vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        // Filtro opcional por vendedor (para gestor/master).
        if ($isGestor && $request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->input('vendedor_id'));
        }
        // Filtro opcional por equipe.
        if ($isGestor && $request->filled('equipe_id')) {
            $query->whereHas('vendedor', fn ($q) => $q->where('equipe_id', $request->input('equipe_id')));
        }

        // ── Métricas totais ──
        $totalVendas  = (clone $query)->count();
        $receitaTotal = (float) (clone $query)->sum('valor_final');
        $ticketMedio  = $totalVendas > 0 ? $receitaTotal / $totalVendas : 0;

        // ── Receita dos últimos 6 meses (agrupado em PHP p/ ser DB-agnóstico) ──
        $seisMesesAtras = Carbon::now()->subMonths(5)->startOfMonth();
        $vendasPeriodo = (clone $query)
            ->where('data_venda', '>=', $seisMesesAtras)
            ->get(['data_venda', 'valor_final']);

        $porMes = [];
        foreach ($vendasPeriodo as $v) {
            if (! $v->data_venda) {
                continue;
            }
            $mesKey = Carbon::parse($v->data_venda)->format('Y-m');
            $porMes[$mesKey] = ($porMes[$mesKey] ?? 0) + (float) $v->valor_final;
        }

        $receitaMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $dataMes = Carbon::now()->subMonths($i);
            $mesKey  = $dataMes->format('Y-m');
            $receitaMensal[] = [
                'name'  => ucfirst($dataMes->locale('pt_BR')->translatedFormat('M')),
                'total' => round($porMes[$mesKey] ?? 0, 2),
            ];
        }

        // ── Vendas por status ──
        $vendasPorStatus = (clone $query)
            ->get(['status'])
            ->groupBy('status')
            ->map(fn ($grupo, $status) => [
                'name'  => ucfirst((string) $status),
                'value' => $grupo->count(),
                'color' => [
                    'Pago'        => '#059669',
                    'concluida'   => '#059669',
                    'pendente'    => '#D97706',
                    'Aguardando pagamento' => '#D97706',
                    'cancelada'   => '#DC2626',
                    'Cancelada'   => '#DC2626',
                ][$status] ?? '#6B7280',
            ])
            ->values();

        // ── Top vendedores (só para gestor/master) ──
        $topVendedores = [];
        if ($isGestor) {
            $topVendedores = (clone $query)
                ->join('vendedores', 'vendas.vendedor_id', '=', 'vendedores.id')
                ->join('users', 'vendedores.usuario_id', '=', 'users.id')
                ->selectRaw('users.name as name, SUM(vendas.valor_final) as total_vendas')
                ->groupBy('vendedores.id', 'users.name')
                ->orderByDesc('total_vendas')
                ->limit(5)
                ->get();

            $maiorTotal = $topVendedores->max('total_vendas') ?: 1;
            $topVendedores = $topVendedores->map(fn ($item) => [
                'name'    => $item->name,
                'total'   => (float) $item->total_vendas,
                'percent' => round(((float) $item->total_vendas / $maiorTotal) * 100),
            ])->values();
        }

        // ── Churn (clientes inativos / total, no escopo) ──
        $clienteQuery = Cliente::query();
        if (! $isGestor && $vendedorId) {
            $clienteQuery->whereHas('vendas', fn ($q) => $q->where('vendedor_id', $vendedorId));
        }
        $totalClientes = (clone $clienteQuery)->count();
        $inativos = (clone $clienteQuery)
            ->whereIn('status', ['inadimplente', 'cancelado', 'churn', 'inativo'])
            ->count();
        $churn = $totalClientes > 0 ? round(($inativos / $totalClientes) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'resumo' => [
                    'totalVendas'  => $totalVendas,
                    'receitaTotal' => round($receitaTotal, 2),
                    'ticketMedio'  => round($ticketMedio, 2),
                    'churn'        => $churn,
                ],
                'receitaMensal'   => $receitaMensal,
                'vendasPorStatus' => $vendasPorStatus,
                'topVendedores'   => $topVendedores,
            ],
        ]);
    }
}
