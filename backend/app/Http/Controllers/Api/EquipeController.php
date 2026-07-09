<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Equipe;
use App\Models\Venda;
use Carbon\Carbon;

class EquipeController extends Controller
{
    public function index(Request $request)
    {
        $equipes = Equipe::with(['gestor', 'vendedores.user'])->where('status', 'ativa')->get()->map(function ($equipe) {
            $membros = $equipe->vendedores->where('user.status', 'ativo')->count();

            $dataInicio = Carbon::now()->startOfMonth();
            $dataFim = Carbon::now();

            $vendasEfetivas = Venda::whereIn('vendedor_id', $equipe->vendedores->pluck('id'))
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->whereNotIn(DB::raw('UPPER(status)'), ['CANCELADO', 'EXPIRADO', 'ESTORNADO'])
                ->get();

            $valorVendido = $vendasEfetivas->sum('valor');
            $valorRecebido = $vendasEfetivas->filter(fn($v) => in_array(strtoupper($v->status), ['PAGO', 'RECEIVED', 'CONFIRMED']))->sum('valor');
            
            $metaPercentual = $equipe->meta_mensal > 0
                ? round(($valorRecebido / $equipe->meta_mensal) * 100, 1)
                : 0;

            // Formatação para bater com o frontend
            return [
                'id' => $equipe->id,
                'nome' => $equipe->nome,
                'lider' => $equipe->gestor->name ?? 'Sem Líder',
                'membros' => $membros,
                'vendas' => 'R$ ' . number_format($valorVendido, 2, ',', '.'),
                'meta' => $metaPercentual . '%',
                'status' => 'Ativo',
            ];
        });

        return response()->json($equipes);
    }

    public function show($id)
    {
        $equipe = Equipe::with(['gestor', 'vendedores.user'])->findOrFail($id);
        
        return response()->json([
            'id' => $equipe->id,
            'nome' => $equipe->nome,
            'gestor_id' => $equipe->gestor_id,
            'meta_mensal' => $equipe->meta_mensal,
            'cor' => $equipe->cor,
            'status' => $equipe->status,
            'vendedores' => $equipe->vendedores->map(function($v) {
                return [
                    'id' => $v->id,
                    'nome' => $v->user->name ?? '',
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'gestor_id' => 'nullable|exists:users,id|unique:equipes,gestor_id',
            'meta_mensal' => 'nullable|numeric',
            'cor' => 'nullable|string',
        ]);

        $equipe = Equipe::create([
            'nome' => $validated['nome'],
            'gestor_id' => $validated['gestor_id'] ?? null,
            'meta_mensal' => $validated['meta_mensal'] ?? 0,
            'cor' => $validated['cor'] ?? '#6D28D9',
            'status' => 'ativa',
        ]);

        return response()->json(['success' => true, 'id' => $equipe->id], 201);
    }

    public function update(Request $request, $id)
    {
        $equipe = Equipe::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'gestor_id' => 'nullable|exists:users,id|unique:equipes,gestor_id,' . $equipe->id,
            'meta_mensal' => 'nullable|numeric',
            'cor' => 'nullable|string',
            'status' => 'string'
        ]);

        \Log::info('Tentando atualizar equipe', ['id' => $id, 'data' => $validated]);

        try {
            $equipe->update($validated);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar equipe', ['erro' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'id' => $equipe->id]);
    }

    public function destroy($id)
    {
        $equipe = Equipe::findOrFail($id);
        
        // Mover todos os vendedores dessa equipe para 'Sem Equipe'
        $equipe->vendedores()->update(['equipe_id' => null]);
        
        // Desativar equipe e remover vinculo do gestor
        $equipe->update([
            'status' => 'inativa',
            'gestor_id' => null
        ]);

        return response()->json(['success' => true]);
    }
}
