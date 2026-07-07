<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaBancaria;

class ContaBancariaController extends Controller {
    public function index(Request $request) {
        $query = ContaBancaria::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }
        return response()->json($query->latest()->paginate(15));
    }
    public function store(Request $request) {
        $data = $request->validate(['nome' => 'required|string', 'tipo' => 'required|string', 'saldo' => 'numeric', 'status' => 'nullable|string']);
        return response()->json(ContaBancaria::create($data), 201);
    }
    public function show($id) { return response()->json(ContaBancaria::findOrFail($id)); }
    public function update(Request $request, $id) {
        $conta = ContaBancaria::findOrFail($id);
        $conta->update($request->all());
        return response()->json($conta);
    }
    public function destroy($id) {
        ContaBancaria::findOrFail($id)->delete();
        return response()->json(['message' => 'Conta removida']);
    }
}
