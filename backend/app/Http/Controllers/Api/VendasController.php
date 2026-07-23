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
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

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

    public function show($id, Request $request)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $venda = Venda::with(['cliente', 'vendedor.user'])->findOrFail($id);

        if (!$isGestor && $vendedorId && $venda->vendedor_id !== $vendedorId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $venda
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'valor' => 'required|numeric|min:0',
            'plano' => 'nullable|string',
            'forma_pagamento' => 'nullable|string',
            'modo_cobranca' => 'nullable|string',
            'parcelas' => 'nullable|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        $user = $request->user();
        $vendedorId = $request->input('vendedor_id');
        
        // Se não for gestor, força a venda a ser do próprio vendedor logado
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);
        if (!$isGestor) {
            $vendedorId = $user->vendedor->id ?? null;
        }

        $venda = Venda::create([
            'cliente_id' => $request->cliente_id,
            'vendedor_id' => $vendedorId,
            'valor' => $request->valor,
            'valor_final' => $request->valor,
            'plano' => $request->plano,
            'status' => 'concluida', // Status padrão local
            'forma_pagamento' => $request->forma_pagamento,
            'modo_cobranca' => $request->modo_cobranca ?? 'mensal',
            'parcelas' => $request->parcelas ?? 1,
            'observacao' => $request->observacao,
            'data_venda' => now(),
        ]);

        // Carregar relacionamentos
        $venda->load(['cliente', 'vendedor.user']);

        return response()->json([
            'success' => true,
            'message' => 'Venda criada com sucesso (registro local).',
            'data' => $venda
        ], 201);
    }
}
