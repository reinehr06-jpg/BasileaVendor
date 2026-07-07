<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CentroCusto;

class CentroCustoController extends Controller {
    public function index(Request $request) {
        $query = CentroCusto::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
        }
        return response()->json($query->latest()->paginate(15));
    }
    public function store(Request $request) {
        $data = $request->validate(['nome' => 'required|string', 'codigo' => 'required|string|unique:centros_custos', 'responsavel' => 'nullable|string', 'orcamento' => 'numeric', 'status' => 'nullable|string']);
        return response()->json(CentroCusto::create($data), 201);
    }
    public function show($id) { return response()->json(CentroCusto::findOrFail($id)); }
    public function update(Request $request, $id) {
        $cc = CentroCusto::findOrFail($id);
        $cc->update($request->all());
        return response()->json($cc);
    }
    public function destroy($id) {
        CentroCusto::findOrFail($id)->delete();
        return response()->json(['message' => 'Centro de custo removido']);
    }
}
