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
        $isGestor = $user->role === 'gestor' || $user->role === 'admin' || $user->role === 'master';

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

        $comissoes = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $comissoes->items(),
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
        $isGestor = $user->role === 'gestor' || $user->role === 'admin' || $user->role === 'master';

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

        $pagamentos = $query->orderBy('data_vencimento', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pagamentos->items(),
            'meta' => [
                'current_page' => $pagamentos->currentPage(),
                'last_page' => $pagamentos->lastPage(),
                'total' => $pagamentos->total(),
            ]
        ]);
    }
}
