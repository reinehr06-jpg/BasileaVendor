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
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = $user->role === 'gestor' || $user->role === 'admin' || $user->role === 'master';

        $queryVendas = Venda::query();
        $queryClientes = Cliente::query();

        if (!$isGestor && $vendedorId) {
            $queryVendas->where('vendedor_id', $vendedorId);
            $queryClientes->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        // Estatísticas Gerais (Semelhante ao que existia no Blade)
        $totalVendas = (clone $queryVendas)->count();
        $vendasAtivas = (clone $queryVendas)->whereNotIn('status', ['CANCELADO', 'EXPIRADO'])->count();
        
        $receitaBruta = (clone $queryVendas)->whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('valor');
        $comissaoTotal = (clone $queryVendas)->whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('comissao_vendedor_valor');
        
        // Total de Clientes Ativos
        $totalClientes = (clone $queryClientes)->count();

        // Dados Mockados para os Gráficos (Para acelerar, depois podemos puxar do banco)
        $chartReceitaData = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            'data' => [12000, 19000, 15000, 22000, 25000, $receitaBruta]
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
