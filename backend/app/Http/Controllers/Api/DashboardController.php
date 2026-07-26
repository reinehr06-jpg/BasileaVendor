<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Vendedor;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Verifica as permissões (vendedor vs gestor vs admin)
        // Para simplificar, vou assumir que o Admin vê tudo e Vendedor vê só o seu
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = $user->perfil === 'gestor' || $user->perfil === 'admin' || $user->perfil === 'master';

        $queryVendas = Venda::query();
        $queryClientes = Cliente::query();

        if (!$isGestor && $vendedorId) {
            $queryVendas->where('vendedor_id', $vendedorId);
            $queryClientes->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        // Estatísticas Gerais
        $totalVendas = (clone $queryVendas)->count();
        $vendasAtivas = (clone $queryVendas)->whereNotIn('status', ['CANCELADO', 'EXPIRADO'])->count();
        
        $receitaBruta = (clone $queryVendas)->whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('valor');
        $comissaoTotal = (clone $queryVendas)->whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('comissao_vendedor_valor');
        
        // Total de Clientes Ativos
        $totalClientes = (clone $queryClientes)->count();

        // ── Receita real dos últimos 6 meses (agrupado em PHP para ser DB-agnóstico) ──
        $seisMesesAtras = \Carbon\Carbon::now()->subMonths(5)->startOfMonth();
        $vendasPeriodo = (clone $queryVendas)
            ->whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])
            ->where('created_at', '>=', $seisMesesAtras)
            ->get(['created_at', 'valor']);

        $porMes = [];
        foreach ($vendasPeriodo as $v) {
            $mesKey = \Carbon\Carbon::parse($v->created_at)->format('Y-m');
            $porMes[$mesKey] = ($porMes[$mesKey] ?? 0) + (float) $v->valor;
        }

        $receitaMensalLabels = [];
        $receitaMensalData = [];
        for ($i = 5; $i >= 0; $i--) {
            $dataMes = \Carbon\Carbon::now()->subMonths($i);
            $mesKey  = $dataMes->format('Y-m');
            $receitaMensalLabels[] = ucfirst($dataMes->locale('pt_BR')->translatedFormat('M'));
            $receitaMensalData[]   = round($porMes[$mesKey] ?? 0, 2);
        }

        $chartReceitaData = [
            'labels' => $receitaMensalLabels,
            'data'   => $receitaMensalData,
        ];

        return response()->json([
            'success' => true,
            'kpis' => [
                'total_vendas' => $totalVendas,
                'vendas_ativas' => $vendasAtivas,
                'receita_bruta' => $receitaBruta,
                'comissao_total' => $comissaoTotal,
                'total_clientes' => $totalClientes,
            ],
            'charts' => [
                'receita_mensal' => $chartReceitaData,
            ],
            'recent_sales' => (clone $queryVendas)->with('cliente')->latest()->take(5)->get()->map(function($v) {
                return [
                    'id' => $v->id,
                    'cliente_nome' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                    'valor' => $v->valor,
                    'status' => $v->status,
                    'data' => $v->created_at->format('d/m/Y')
                ];
            })
        ]);
    }
}
