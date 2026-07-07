<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\Validator;

class FornecedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Fornecedor::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $fornecedores = $query->latest()->paginate(15);
        return response()->json($fornecedores);
    }

    public function show($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        return response()->json($fornecedor);
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

        $fornecedor = Fornecedor::create($request->all());
        return response()->json($fornecedor, 201);
    }

    public function update(Request $request, $id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fornecedor->update($request->all());
        return response()->json($fornecedor);
    }

    public function destroy($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        $fornecedor->delete();
        return response()->json(['message' => 'Fornecedor removido com sucesso']);
    }
}
