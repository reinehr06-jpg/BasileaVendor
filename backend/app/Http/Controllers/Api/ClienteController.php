<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Cliente::query();
        
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        if (!$isGestor && $vendedorId) {
            $query->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('nome_igreja', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clientes = $query->with('vendas.vendedor.user')->latest()->paginate(15);
        
        // Formatar para o frontend
        $clientes->getCollection()->transform(function($c) {
            // Obter o vendedor principal do cliente com base na venda mais recente
            $venda = $c->vendas->first();
            $vendedor = $venda ? ($venda->vendedor->user->name ?? $venda->vendedor->nome) : 'N/A';

            return [
                'id' => $c->id,
                'nome' => $c->nome_igreja ?? $c->nome,
                'cpfCnpj' => $c->documento ?? 'Não informado',
                'responsavel' => $c->nome_responsavel ?? $c->nome,
                'vendedor' => $vendedor,
                'financeiro' => $c->recorrencia_status === 'ACTIVE' ? 'Em dia' : ($c->recorrencia_status ?? 'Pendente'),
                'status' => $c->status,
                'email' => $c->email,
                'telefone' => $c->telefone ?? $c->whatsapp,
            ];
        });

        return response()->json($clientes);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Cliente::where('id', $id)->with('vendas.vendedor');

        if (!$isGestor && $vendedorId) {
            $query->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        $cliente = $query->firstOrFail();
        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(['message' => 'Cliente removido com sucesso']);
    }
}
