<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Termo;

class TermoController extends Controller
{
    public function index()
    {
        $termos = Termo::orderBy('id', 'desc')->get();
        return response()->json(['data' => $termos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'versao' => 'nullable|string',
            'tipo' => 'nullable|string',
        ]);

        $termo = Termo::create($request->all());
        return response()->json(['message' => 'Termo criado com sucesso', 'data' => $termo], 201);
    }

    public function show($id)
    {
        $termo = Termo::findOrFail($id);
        return response()->json(['data' => $termo]);
    }

    public function update(Request $request, $id)
    {
        $termo = Termo::findOrFail($id);
        $termo->update($request->all());
        return response()->json(['message' => 'Termo atualizado com sucesso', 'data' => $termo]);
    }

    public function destroy($id)
    {
        $termo = Termo::findOrFail($id);
        $termo->delete();
        return response()->json(['message' => 'Termo excluído com sucesso']);
    }
}
