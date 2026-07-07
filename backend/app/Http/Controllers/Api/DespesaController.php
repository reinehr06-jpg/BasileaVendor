<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Despesa;
use Illuminate\Support\Facades\Validator;

class DespesaController extends Controller
{
    public function index(Request $request)
    {
        $query = Despesa::with('fornecedor');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%")
                  ->orWhere('fornecedor_nome', 'like', "%{$search}%");
        }

        $despesas = $query->latest()->paginate(15);
        return response()->json($despesas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'data_vencimento' => 'required|date',
            'status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $despesa = Despesa::create($request->all());
        return response()->json($despesa, 201);
    }

    public function show($id)
    {
        $despesa = Despesa::with('fornecedor')->findOrFail($id);
        return response()->json($despesa);
    }

    public function update(Request $request, $id)
    {
        $despesa = Despesa::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'descricao' => 'sometimes|required|string|max:255',
            'valor' => 'sometimes|required|numeric',
            'data_vencimento' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $despesa->update($request->all());
        return response()->json($despesa);
    }

    public function destroy($id)
    {
        $despesa = Despesa::findOrFail($id);
        $despesa->delete();
        return response()->json(['message' => 'Despesa removida']);
    }
}
