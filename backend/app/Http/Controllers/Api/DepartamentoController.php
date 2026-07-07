<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller {
    public function index(Request $request) {
        $query = Departamento::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }
        return response()->json($query->latest()->paginate(15));
    }
    public function store(Request $request) {
        $data = $request->validate(['nome' => 'required|string', 'lider' => 'nullable|string', 'status' => 'nullable|string']);
        return response()->json(Departamento::create($data), 201);
    }
    public function show($id) { return response()->json(Departamento::findOrFail($id)); }
    public function update(Request $request, $id) {
        $dep = Departamento::findOrFail($id);
        $dep->update($request->all());
        return response()->json($dep);
    }
    public function destroy($id) {
        Departamento::findOrFail($id)->delete();
        return response()->json(['message' => 'Departamento removido']);
    }
}
