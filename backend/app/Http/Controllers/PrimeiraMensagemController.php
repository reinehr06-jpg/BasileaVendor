<?php

namespace App\Http\Controllers;

use App\Models\PrimeiraMensagem;
use App\Models\Vendedor;
use App\Models\Setting;
use App\Services\AI\PrimeiraMensagemIAService;
use App\Services\AI\StrictPromptValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

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
            // SELF-HEALING: Se a tabela estiver com schema errado, repara na hora
            if (Schema::hasTable('primeira_mensagens') && !Schema::hasColumn('primeira_mensagens', 'user_id')) {
                Log::warning('REPARANDO_TABELA_PRIMEIRA_MENSAGENS_ON_THE_FLY');
                Schema::drop('primeira_mensagens');
            }

            if (!Schema::hasTable('primeira_mensagens')) {
                Schema::create('primeira_mensagens', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                    $table->string('perfil')->nullable();
                    $table->string('titulo');
                    $table->text('mensagem');
                    $table->boolean('ativa')->default(false);
                    $table->enum('status', ['rascunho', 'pendente_aprovacao', 'aprovada', 'rejeitada'])->default('rascunho');
                    $table->foreignId('aprovada_por')->nullable()->constrained('users')->nullOnDelete();
                    $table->foreignId('rejeitada_por')->nullable()->constrained('users')->nullOnDelete();
                    $table->text('motivo_rejeicao')->nullable();
                    $table->timestamps();
                    $table->index(['user_id', 'status']);
                    $table->unique(['user_id', 'ativa']);
                });
            }

            $mensagens = PrimeiraMensagem::where('user_id', Auth::id())
                ->orderByDesc('created_at')->get();

            return view('vendedor.primeira-mensagem.index', compact('mensagens'));
        } catch (\Throwable $e) {
            Log::error('PRIMEIRA_MENSAGEM_INDEX_ERROR: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Erro ao carregar mensagens. Por favor, tente recarregar a página.');
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
