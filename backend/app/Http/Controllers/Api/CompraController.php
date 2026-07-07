<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compra;
use Illuminate\Support\Facades\Validator;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with('fornecedor');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('solicitante', 'like', "%{$search}%")
                  ->orWhereHas('fornecedor', function($qF) use ($search) {
                      $qF->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $compras = $query->latest()->paginate(15);
        return response()->json($compras);
    }

    public function show($id)
    {
        $compra = Compra::with('fornecedor')->findOrFail($id);
        return response()->json($compra);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'solicitante' => 'required|string|max:255',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'valor' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['numero'] = 'COMP-' . date('Y') . '-' . str_pad(Compra::count() + 1, 3, '0', STR_PAD_LEFT);
        $data['data_solicitacao'] = date('Y-m-d');
        
        $compra = Compra::create($data);
        return response()->json($compra, 201);
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'valor' => 'sometimes|required|numeric',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $compra->update($request->all());
        return response()->json($compra);
    }

    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);
        $compra->delete();
        return response()->json(['message' => 'Compra removida com sucesso']);
    }
}
