<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendaRequest;
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

    public function store(StoreVendaRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();
        $vendedorId = $validated['vendedor_id'] ?? null;
        
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);
        if (!$isGestor) {
            $vendedorId = $user->vendedor->id ?? null;
        }

        $venda = Venda::create([
            'cliente_id' => $validated['cliente_id'],
            'vendedor_id' => $vendedorId,
            'valor' => $validated['valor'],
            'valor_final' => $validated['valor_final'] ?? $validated['valor'],
            'plano' => $validated['plano'],
            'status' => 'concluida',
            'forma_pagamento' => $validated['forma_pagamento'],
            'tipo_negociacao' => $validated['tipo_negociacao'] ?? 'mensal',
            'modo_cobranca' => $validated['modo_cobranca'] ?? 'mensal',
            'desconto' => $validated['desconto'] ?? 0,
            'percentual_desconto' => $validated['percentual_desconto'] ?? 0,
            'parcelas' => $validated['parcelas'] ?? 1,
            'observacao' => $validated['observacao'] ?? null,
            'data_venda' => now(),
        ]);

        $venda->load(['cliente', 'vendedor.user']);

        return response()->json([
            'success' => true,
            'message' => 'Venda criada com sucesso.',
            'data' => $venda
        ], 201);
    }
}
