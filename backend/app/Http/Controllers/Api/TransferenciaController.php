<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transferencia;

class TransferenciaController extends Controller {
    public function index(Request $request) {
        $query = Transferencia::with(['origem', 'destino']);
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%")
                  ->orWhere('origem_nome', 'like', "%{$search}%")
                  ->orWhere('destino_nome', 'like', "%{$search}%");
        }
        return response()->json($query->latest()->paginate(15));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'data' => 'required|date', 
            'valor' => 'required|numeric', 
            'origem_id' => 'nullable|integer',
            'destino_id' => 'nullable|integer',
            'origem_nome' => 'nullable|string',
            'destino_nome' => 'nullable|string',
            'taxa' => 'numeric', 
            'descricao' => 'nullable|string', 
            'status' => 'nullable|string'
        ]);
        return response()->json(Transferencia::create($data), 201);
    }
    public function show($id) { return response()->json(Transferencia::with(['origem', 'destino'])->findOrFail($id)); }
    public function update(Request $request, $id) {
        $transf = Transferencia::findOrFail($id);
        $transf->update($request->all());
        return response()->json($transf);
    }
    public function destroy($id) {
        Transferencia::findOrFail($id)->delete();
        return response()->json(['message' => 'Transferência removida']);
    }
}
