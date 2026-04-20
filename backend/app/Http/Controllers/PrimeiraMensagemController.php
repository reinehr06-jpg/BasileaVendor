<?php

namespace App\Http\Controllers;

use App\Models\PrimeiraMensagem;
use App\Models\Vendedor;
use App\Services\AI\PrimeiraMensagemIAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrimeiraMensagemController extends Controller
{
    // Vendedor — listagem e formulário
    public function index()
    {
        $mensagens = PrimeiraMensagem::where('user_id', Auth::id())
            ->orderByDesc('created_at')->get();

        return view('configuracoes.primeira-mensagem', compact('mensagens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'   => 'required|string|max:255',
            'mensagem' => 'required|string|max:500',
        ]);

        PrimeiraMensagem::create([
            'user_id' => Auth::id(),
            'perfil'  => Auth::user()->perfil,
            'titulo'  => $request->titulo,
            'mensagem'=> $request->mensagem,
            'status'  => 'rascunho',
        ]);

        return back()->with('success', 'Mensagem salva como rascunho!');
    }

    public function enviarParaAprovacao(PrimeiraMensagem $mensagem)
    {
        abort_unless($mensagem->user_id === Auth::id(), 403);
        $mensagem->update(['status' => 'pendente_aprovacao']);
        return back()->with('success', 'Enviada para aprovação!');
    }

    // Gestor — ver pendentes
    public function pendentes()
    {
        try {
            $ids = Vendedor::where('gestor_id', Auth::id())->pluck('id');

            if ($ids->isEmpty()) {
                $pendentes = collect([]);
            } else {
                $pendentes = PrimeiraMensagem::whereIn('user_id', $ids)
                    ->where('status', 'pendente_aprovacao')
                    ->with('usuario')
                    ->get();
            }
        } catch (\Exception $e) {
            $pendentes = collect([]);
        }

        return view('gestor.aprovar-mensagem', compact('pendentes'));
    }

    // Gestor — aprovar
    public function aprovar(PrimeiraMensagem $mensagem)
    {
        $mensagem->aprovada_por = Auth::id();
        $mensagem->save();
        $mensagem->ativar(); // desativa as outras e ativa esta

        return back()->with('success', 'Mensagem aprovada e ativada!');
    }

    // Gestor — rejeitar
    public function rejeitar(Request $request, PrimeiraMensagem $mensagem)
    {
        $request->validate(['motivo' => 'required|string']);

        $mensagem->update([
            'status'           => 'rejeitada',
            'rejeitada_por'    => Auth::id(),
            'motivo_rejeicao'  => $request->motivo,
        ]);

        return back()->with('success', 'Mensagem rejeitada!');
    }

    // IA local — gerar sugestões
    public function gerarComIA(Request $request)
    {
        $iaService = new PrimeiraMensagemIAService();
        $sugestoes = $iaService->gerarSugestoes($request->contexto ?? '', 5);

        return response()->json(['sugestoes' => $sugestoes]);
    }
}
