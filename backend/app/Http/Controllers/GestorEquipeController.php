<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Equipe;
use App\Models\Vendedor;
use App\Models\User;
use App\Models\Venda;
use Carbon\Carbon;

class GestorEquipeController extends Controller
{
    /**
     * Mostra a equipe do gestor
     */
    public function index()
    {
        $user = Auth::user();
        
        // Verifica se é gestor
        if ($user->perfil !== 'gestor') {
            return redirect()->route('vendedor.dashboard')->with('error', 'Acesso não autorizado.');
        }
        
        $vendedorGestor = $user->vendedor;
        
        if (!$vendedorGestor || !$vendedorGestor->is_gestor) {
            return redirect()->route('vendedor.dashboard')->with('error', 'Você não tem permissão de gestor.');
        }
        
        // Busca a equipe do gestor
        $equipe = Equipe::where('gestor_id', $user->id)->first();
        
        if (!$equipe) {
            // Auto-criar equipe se não existir
            $equipe = \App\Http\Controllers\EquipeController::autoCriarEquipe($user->id);
        }
        
        // Busca vendedores da equipe
        $vendedores = Vendedor::where('equipe_id', $equipe->id)
            ->with('user')
            ->get();
        
        // Estatísticas da equipe
        $mesAtual = Carbon::now()->format('Y-m');
        $dataInicio = Carbon::now()->startOfMonth();
        $dataFim = Carbon::now();
        
        $vendasEfetivas = Venda::whereIn('vendedor_id', $vendedores->pluck('id'))
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->whereNotIn(DB::raw('UPPER(status)'), ['CANCELADO', 'EXPIRADO', 'ESTORNADO'])
            ->get();
        
        $stats = [
            'total_vendedores' => $vendedores->count(),
            'valor_vendido' => $vendasEfetivas->sum('valor'),
            'valor_recebido' => $vendasEfetivas->where('status', 'PAGO')->sum('valor'),
            'total_vendas' => $vendasEfetivas->count(),
            'meta_mensal' => $equipe->meta_mensal,
            'percentual_meta' => $equipe->meta_mensal > 0 
                ? round(($vendasEfetivas->where('status', 'PAGO')->sum('valor') / $equipe->meta_mensal) * 100, 1) 
                : 0,
        ];
        
        // Vendedores disponíveis (sem equipe)
        $vendedoresDisponiveis = Vendedor::whereNull('equipe_id')
            ->whereHas('user', function ($q) { 
                $q->whereIn('status', ['ativo', '1', 1]); 
            })
            ->with('user')
            ->get();
        
        return view('vendedor.equipe.index', compact('equipe', 'vendedores', 'stats', 'vendedoresDisponiveis'));
    }
    
    /**
     * Adiciona vendedor à equipe do gestor
     */
    public function adicionarMembro(Request $request)
    {
        $user = Auth::user();
        
        if ($user->perfil !== 'gestor') {
            return back()->with('error', 'Acesso não autorizado.');
        }
        
        $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id',
        ]);
        
        $equipe = Equipe::where('gestor_id', $user->id)->first();
        
        if (!$equipe) {
            return back()->with('error', 'Equipe não encontrada.');
        }
        
        $vendedor = Vendedor::findOrFail($request->vendedor_id);
        
        // Verifica se o vendedor já pertence a outra equipe
        if ($vendedor->equipe_id && $vendedor->equipe_id !== $equipe->id) {
            return back()->with('error', 'Este vendedor já pertence a outra equipe.');
        }
        
        $vendedor->update([
            'equipe_id' => $equipe->id,
            'gestor_id' => $user->id,
        ]);
        
        return back()->with('success', 'Vendedor adicionado à equipe com sucesso!');
    }
    
    /**
     * Remove vendedor da equipe do gestor
     */
    public function removerMembro($vendedorId)
    {
        $user = Auth::user();
        
        if ($user->perfil !== 'gestor') {
            return back()->with('error', 'Acesso não autorizado.');
        }
        
        $equipe = Equipe::where('gestor_id', $user->id)->first();
        
        if (!$equipe) {
            return back()->with('error', 'Equipe não encontrada.');
        }
        
        $vendedor = Vendedor::where('id', $vendedorId)
            ->where('equipe_id', $equipe->id)
            ->firstOrFail();
        
        $vendedor->update(['equipe_id' => null]);
        
        return back()->with('success', 'Vendedor removido da equipe com sucesso!');
    }
    
    /**
     * Atualiza meta da equipe
     */
    public function atualizarMeta(Request $request)
    {
        $user = Auth::user();
        
        if ($user->perfil !== 'gestor') {
            return back()->with('error', 'Acesso não autorizado.');
        }
        
        $request->validate([
            'meta_mensal' => 'required|numeric|min:0',
        ]);
        
        $equipe = Equipe::where('gestor_id', $user->id)->first();
        
        if (!$equipe) {
            return back()->with('error', 'Equipe não encontrada.');
        }
        
        $equipe->update(['meta_mensal' => $request->meta_mensal]);
        
        return back()->with('success', 'Meta da equipe atualizada com sucesso!');
    }
    
    /**
     * API para obter detalhes de um vendedor da equipe
     */
    public function vendedorDetalhes($vendedorId)
    {
        $user = Auth::user();
        
        if ($user->perfil !== 'gestor') {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }
        
        $equipe = Equipe::where('gestor_id', $user->id)->first();
        
        if (!$equipe) {
            return response()->json(['error' => 'Equipe não encontrada.'], 404);
        }
        
        $vendedor = Vendedor::where('id', $vendedorId)
            ->where('equipe_id', $equipe->id)
            ->with('user', 'vendas', 'comissoes')
            ->firstOrFail();
        
        $mesAtual = Carbon::now()->format('Y-m');
        $dataInicio = Carbon::now()->startOfMonth();
        
        $vendasMes = $vendedor->vendas()
            ->whereBetween('created_at', [$dataInicio, Carbon::now()])
            ->whereNotIn(DB::raw('UPPER(status)'), ['CANCELADO', 'EXPIRADO', 'ESTORNADO'])
            ->get();
        
        return response()->json([
            'vendedor' => $vendedor,
            'vendas_mes' => [
                'total' => $vendasMes->count(),
                'valor' => $vendasMes->sum('valor'),
                'pago' => $vendasMes->where('status', 'PAGO')->sum('valor'),
            ],
        ]);
    }
}