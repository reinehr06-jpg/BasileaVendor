<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricasVendasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = $user->role === 'gestor' || $user->role === 'admin' || $user->role === 'master';

        $query = Venda::query();
        if (!$isGestor && $vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        // Métricas Totais
        $totalVendas = (clone $query)->count();
        $receitaTotal = (clone $query)->sum('valor_final');
        $ticketMedio = $totalVendas > 0 ? $receitaTotal / $totalVendas : 0;

        // Receita Mensal (Últimos 6 meses)
        $seisMesesAtras = Carbon::now()->subMonths(5)->startOfMonth();
        $receitaMensalRaw = (clone $query)
            ->select(
                DB::raw('DATE_FORMAT(data_venda, "%Y-%m") as mes'),
                DB::raw('SUM(valor_final) as total')
            )
            ->where('data_venda', '>=', $seisMesesAtras)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $receitaMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $mesKey = $data->format('Y-m');
            $mesNome = $data->locale('pt_BR')->translatedFormat('M');
            
            $item = $receitaMensalRaw->firstWhere('mes', $mesKey);
            $receitaMensal[] = [
                'name' => ucfirst($mesNome),
                'total' => $item ? (float)$item->total : 0
            ];
        }

        // Vendas por Status (Gráfico de Pizza)
        $vendasPorStatusRaw = (clone $query)
            ->select('status', DB::raw('COUNT(*) as quantidade'))
            ->groupBy('status')
            ->get();
            
        $vendasPorStatus = $vendasPorStatusRaw->map(function($item) {
            $colors = [
                'concluida' => '#059669',
                'pendente' => '#D97706',
                'cancelada' => '#DC2626',
            ];
            return [
                'name' => ucfirst($item->status),
                'value' => (int)$item->quantidade,
                'color' => $colors[$item->status] ?? '#6B7280'
            ];
        });

        // Top Vendedores (apenas se for gestor, ou omitir para vendedor)
        $topVendedores = [];
        if ($isGestor) {
            $topVendedores = (clone $query)
                ->join('vendedores', 'vendas.vendedor_id', '=', 'vendedores.id')
                ->join('users', 'vendedores.user_id', '=', 'users.id')
                ->select('users.name', DB::raw('SUM(vendas.valor_final) as total_vendas'))
                ->groupBy('vendedores.id', 'users.name')
                ->orderByDesc('total_vendas')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'name' => $item->name,
                        'total' => (float)$item->total_vendas
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'resumo' => [
                    'totalVendas' => $totalVendas,
                    'receitaTotal' => $receitaTotal,
                    'ticketMedio' => $ticketMedio
                ],
                'receitaMensal' => $receitaMensal,
                'vendasPorStatus' => $vendasPorStatus,
                'topVendedores' => $topVendedores
            ]
        ]);
    }
}
