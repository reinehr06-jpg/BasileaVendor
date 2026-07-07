<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receita;
use Illuminate\Support\Facades\Validator;

class ReceitaController extends Controller
{
    public function index(Request $request)
    {
        $query = Receita::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%")
                  ->orWhere('origem', 'like', "%{$search}%");
        }

        $receitas = $query->latest()->paginate(15);
        return response()->json($receitas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'data' => 'required|date',
            'status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $receita = Receita::create($request->all());
        return response()->json($receita, 201);
    }

    public function show($id)
    {
        $receita = Receita::findOrFail($id);
        return response()->json($receita);
    }

    public function update(Request $request, $id)
    {
        $receita = Receita::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'descricao' => 'sometimes|required|string|max:255',
            'valor' => 'sometimes|required|numeric',
            'data' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $receita->update($request->all());
        return response()->json($receita);
    }

    public function destroy($id)
    {
        $receita = Receita::findOrFail($id);
        $receita->delete();
        return response()->json(['message' => 'Receita removida']);
    }
}
