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
}
