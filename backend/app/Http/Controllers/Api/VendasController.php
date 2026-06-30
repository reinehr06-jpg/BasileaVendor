<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;

class VendasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = $user->role === 'gestor' || $user->role === 'admin' || $user->role === 'master';

        $query = Venda::with(['cliente', 'vendedor.user']);

        if (!$isGestor && $vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('cliente', function($q) use ($s) {
                $q->where('nome', 'like', $s)
                  ->orWhere('nome_igreja', 'like', $s);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendas = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $vendas->items(),
            'meta' => [
                'current_page' => $vendas->currentPage(),
                'last_page' => $vendas->lastPage(),
                'total' => $vendas->total(),
            ]
        ]);
    }
}
