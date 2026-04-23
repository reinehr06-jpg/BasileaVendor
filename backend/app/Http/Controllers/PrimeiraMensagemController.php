<?php

namespace App\Http\Controllers;

use App\Models\PrimeiraMensagem;
use App\Models\Vendedor;
use App\Models\Setting;
use App\Services\AI\PrimeiraMensagemIAService;
use App\Services\AI\StrictPromptValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrimeiraMensagemController extends Controller
{
    private StrictPromptValidator $aiValidator;

    public function __construct(StrictPromptValidator $aiValidator)
    {
        $this->aiValidator = $aiValidator;
    }

    public function index()
    {
        try {
            $mensagens = PrimeiraMensagem::where('user_id', Auth::id())
                ->orderByDesc('created_at')->get();

            return view('vendedor.primeira-mensagem.index', compact('mensagens'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PRIMEIRA_MENSAGEM_INDEX_ERROR: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (config('app.debug')) {
                throw $e;
            }

            return redirect()->route('dashboard')->with('error', 'Ocorreu um erro ao carregar suas mensagens. Tentamos reparar o banco de dados, por favor tente novamente em alguns instantes.');
        }
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

    public function pendentes()
    {
        try {
            // Pegar os USUARIOS que este gestor gerencia
            $usuarioIds = Vendedor::where('gestor_id', Auth::id())->pluck('usuario_id');
            
            $pendentes = $usuarioIds->isEmpty() ? collect([]) : PrimeiraMensagem::whereIn('user_id', $usuarioIds)
                ->where('status', 'pendente_aprovacao')
                ->with('usuario')
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao carregar pendentes: ' . $e->getMessage());
            $pendentes = collect([]);
        }

        return view('gestor.aprovar-mensagem', compact('pendentes'));
    }

    public function aprovar(PrimeiraMensagem $mensagem)
    {
        $mensagem->aprovada_por = Auth::id();
        $mensagem->save();
        $mensagem->ativar();

        return back()->with('success', 'Mensagem aprovada e ativada!');
    }

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

    /**
     * IA local — gerar sugestões COM VALIDAÇÃO DE PROMPT
     */
    public function gerarComIA(Request $request)
    {
        $request->validate([
            'contexto' => 'nullable|string',
            'lead_id' => 'nullable|exists:contatos,id'
        ]);

        // VALIDAR PROMPT ANTES DE TUDO
        $prompt = Setting::get('ia_prompt_primeira_mensagem');
        
        try {
            $this->aiValidator->assertPromptExists($prompt, 'primeira_mensagem', [
                'contexto' => $request->input('contexto'),
                'lead_id' => $request->input('lead_id'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }

        // Se Prompt válido, chamar o serviço de IA
        $iaService = new PrimeiraMensagemIAService();
        $sugestoes = $iaService->gerarSugestoes($request->input('contexto') ?? '', 5);

        return response()->json(['sugestoes' => $sugestoes, 'success' => true]);
    }
}
