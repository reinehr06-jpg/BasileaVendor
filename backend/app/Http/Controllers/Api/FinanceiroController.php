<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comissao;
use App\Models\Pagamento;

class FinanceiroController extends Controller
{
    public function comissoes(Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Comissao::with(['vendedor.user', 'cliente', 'venda']);

        if (!$isGestor && $vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('vendedor.user', function($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s);
            });
        }

        // Resumo (KPIs) sobre TODO o conjunto filtrado, não só a página.
        $resumoQuery = (clone $query);
        $totalComissao = (float) (clone $resumoQuery)->sum('valor_comissao');
        $totalGestor   = (float) (clone $resumoQuery)->sum('valor_gerente');
        $totalVendas   = (float) (clone $resumoQuery)->sum('valor_venda');
        $numComissoes  = (clone $resumoQuery)->count();
        $numVendedores = (clone $resumoQuery)->distinct('vendedor_id')->count('vendedor_id');
        $ganhoTotal    = $totalComissao + $totalGestor;

        $comissoes = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $comissoes->items(),
            'resumo' => [
                'num_comissoes'  => $numComissoes,
                'num_vendedores' => $numVendedores,
                'total_comissao' => round($ganhoTotal, 2),
                'total_vendas'   => round($totalVendas, 2),
                'comissao_media' => $totalVendas > 0 ? round(($ganhoTotal / $totalVendas) * 100, 1) : 0,
                'comissao_media_valor' => $numComissoes > 0 ? round($ganhoTotal / $numComissoes, 2) : 0,
            ],
            'meta' => [
                'current_page' => $comissoes->currentPage(),
                'last_page' => $comissoes->lastPage(),
                'total' => $comissoes->total(),
            ]
        ]);
    }

    public function pagamentos(Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Pagamento::with(['cliente', 'venda']);

        if (!$isGestor && $vendedorId) {
            $query->whereHas('venda', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('cliente', function($q) use ($s) {
                $q->where('nome', 'like', $s)
                  ->orWhere('email', 'like', $s);
            });
        }

        // Resumo (KPIs) sobre todo o conjunto filtrado.
        $pagos = ['RECEIVED', 'CONFIRMED', 'PAGO'];
        $totalRecebido = (float) (clone $query)->whereIn('status', $pagos)->sum('valor');
        $numRecebidos  = (clone $query)->whereIn('status', $pagos)->count();
        $totalPendente = (float) (clone $query)->whereNotIn('status', array_merge($pagos, ['CANCELED', 'DELETED']))->sum('valor');

        $pagamentos = $query->orderBy('data_vencimento', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pagamentos->items(),
            'resumo' => [
                'total_recebido' => round($totalRecebido, 2),
                'num_recebidos'  => $numRecebidos,
                'total_pendente' => round($totalPendente, 2),
            ],
            'meta' => [
                'current_page' => $pagamentos->currentPage(),
                'last_page' => $pagamentos->lastPage(),
                'total' => $pagamentos->total(),
            ]
        ]);
    }
}
