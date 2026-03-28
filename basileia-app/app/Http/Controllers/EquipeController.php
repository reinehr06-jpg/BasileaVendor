<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Equipe;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Meta;
use App\Models\Venda;
use App\Models\Pagamento;
use Carbon\Carbon;

class EquipeController extends Controller
{
    public function index(Request $request)
    {
        $equipes = Equipe::with(['gestor', 'vendedores.user'])->where('status', 'ativa')->get()->map(function ($equipe) {
            $equipe->total_vendedores = $equipe->vendedores->where('user.status', 'ativo')->count();
            $equipe->total_vendedores_todos = $equipe->vendedores->count();

            $mes = Carbon::now()->format('Y-m');
            $dataInicio = Carbon::now()->startOfMonth();
            $dataFim = Carbon::now();

            $vendasEfetivas = Venda::whereIn('vendedor_id', $equipe->vendedores->pluck('id'))
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->whereNotIn('status', ['Cancelado', 'Expirado'])
                ->get();

            $equipe->valor_vendido = $vendasEfetivas->sum('valor');
            $equipe->valor_recebido = $vendasEfetivas->where('status', 'PAGO')->sum('valor');
            $equipe->total_vendas_periodo = $vendasEfetivas->count();
            $equipe->percentual_meta = $equipe->meta_mensal > 0
                ? round(($equipe->valor_recebido / $equipe->meta_mensal) * 100, 1)
                : 0;

            return $equipe;
        });

        $gestores = User::where('perfil', 'gestor')
            ->where('status', 'ativo')
            ->whereHas('vendedor', function ($q) { $q->where('is_gestor', true); })
            ->with('vendedor')
            ->get();

        $gestoresDisponiveis = $gestores->filter(function ($g) {
            return !$g->equipeLiderada()->exists();
        });

        $vendedoresSemEquipe = Vendedor::with('user')
            ->whereNull('equipe_id')
            ->whereHas('user', function ($q) { $q->where('status', 'ativo'); })
            ->get();

        return view('master.equipes.index', compact('equipes', 'gestoresDisponiveis', 'vendedoresSemEquipe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'gestor_id' => 'required|exists:users,id',
            'meta_mensal' => 'nullable|numeric|min:0',
            'cor' => 'nullable|string|max:7',
        ]);

        $exists = Equipe::where('gestor_id', $request->gestor_id)->exists();
        if ($exists) {
            return back()->withErrors(['error' => 'Este gestor já possui uma equipe.'])->withInput();
        }

        Equipe::create([
            'nome' => $request->nome,
            'gestor_id' => $request->gestor_id,
            'meta_mensal' => $request->meta_mensal ?? 0,
            'cor' => $request->cor ?? '#4C1D95',
        ]);

        return redirect()->route('master.equipes')->with('success', 'Equipe criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $equipe = Equipe::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'meta_mensal' => 'nullable|numeric|min:0',
            'cor' => 'nullable|string|max:7',
            'status' => 'nullable|in:ativa,inativa',
        ]);

        $equipe->update($request->only(['nome', 'meta_mensal', 'cor', 'status']));

        return redirect()->route('master.equipes')->with('success', 'Equipe atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $equipe = Equipe::findOrFail($id);

        Vendedor::where('equipe_id', $equipe->id)->update(['equipe_id' => null]);
        $equipe->delete();

        return redirect()->route('master.equipes')->with('success', 'Equipe removida com sucesso!');
    }

    public function adicionarMembro(Request $request, $id)
    {
        $equipe = Equipe::findOrFail($id);

        $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id',
        ]);

        $vendedor = Vendedor::findOrFail($request->vendedor_id);
        $vendedor->update([
            'equipe_id' => $equipe->id,
            'gestor_id' => $equipe->gestor_id,
        ]);

        return back()->with('success', 'Vendedor adicionado à equipe com sucesso!');
    }

    public function removerMembro($equipeId, $vendedorId)
    {
        $vendedor = Vendedor::findOrFail($vendedorId);
        $vendedor->update(['equipe_id' => null]);

        return back()->with('success', 'Vendedor removido da equipe com sucesso!');
    }

    public function apiListar(Request $request)
    {
        $equipes = Equipe::with(['gestor', 'vendedores.user'])->where('status', 'ativa')->get()->map(function ($equipe) {
            $equipe->total_vendedores = $equipe->vendedores->where('user.status', 'ativo')->count();
            return $equipe;
        });

        return response()->json($equipes);
    }

    public static function autoCriarEquipe($gestorId)
    {
        if (Equipe::where('gestor_id', $gestorId)->exists()) {
            return Equipe::where('gestor_id', $gestorId)->first();
        }

        $gestor = User::find($gestorId);
        if (!$gestor) return null;

        return Equipe::create([
            'nome' => 'Equipe ' . $gestor->name,
            'gestor_id' => $gestorId,
            'meta_mensal' => 0,
        ]);
    }
}
