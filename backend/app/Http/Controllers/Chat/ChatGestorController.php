<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatContact;
use App\Models\ChatConversa;
use App\Models\ChatWhatsappConfig;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatGestorController extends Controller
{
    protected $distributionService;

    public function __construct()
    {
        $this->distributionService = new \App\Services\Chat\ChatDistributionService();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $vendedorId = $request->get('vendedor');
        $filtro = $request->get('aba', 'nao_atendidos');
        $busca = $request->get('q', '');

        $query = ChatConversa::with(['contact', 'vendedor', 'ultimoMensagem'])
            ->where('gestor_id', $gestorId)
            ->orderBy('pinned', 'desc')
            ->orderBy('last_message_at', 'desc');

        if ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        if ($filtro === 'atendidos') {
            $query->where('is_atendido', true);
        } elseif ($filtro === 'nao_atendidos') {
            $query->where('is_atendido', false);
        }

        if ($busca) {
            $query->whereHas('contact', function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        $conversas = $query->paginate(30);

        $contagem = [
            'atendidos' => ChatConversa::where('gestor_id', $gestorId)->where('is_atendido', true)->count(),
            'nao_atendidos' => ChatConversa::where('gestor_id', $gestorId)->where('is_atendido', false)->count(),
        ];

        $vendedores = Vendedor::where('gestor_id', $gestorId)
            ->where('status', 'ativo')
            ->get();

        return view('chat.gestor.index', compact('conversas', 'contagem', 'filtro', 'vendedores', 'vendedorId', 'busca'));
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $conversa = ChatConversa::with(['contact', 'vendedor', 'mensagens'])
            ->where('gestor_id', $gestorId)
            ->where('id', $id)
            ->firstOrFail();

        return view('chat.gestor.conversa', compact('conversa'));
    }

    public function config(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $config = ChatWhatsappConfig::byGestor($gestorId)->first();

        if (!$config) {
            $config = ChatWhatsappConfig::create([
                'gestor_id' => $gestorId,
                'is_active' => false
            ]);
        }

        return view('chat.gestor.config', compact('config'));
    }

    public function updateWhatsappConfig(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return back()->with('error', 'Módulo de chat está desativado.');
        }

        $request->validate([
            'numero_telefone' => 'required|string',
            'provider' => 'required|in:meta,Take,WppConnect,Evolution',
            'api_token' => 'required|string',
            'webhook_verify_token' => 'nullable|string',
        ]);

        $config = ChatWhatsappConfig::byGestor($gestorId)->firstOrCreate(
            ['gestor_id' => $gestorId],
            ['is_active' => false]
        );

        $config->update([
            'numero_telefone' => $request->numero_telefone,
            'provider' => $request->provider,
            'api_token' => $request->api_token,
            'webhook_verify_token' => $request->webhook_verify_token ?? Str::random(32),
            'is_active' => $request->has('is_active') ? $request->is_active : false,
        ]);

        Log::info('ChatGestor: Configuração do WhatsApp atualizada', [
            'gestor_id' => $gestorId,
            'provider' => $request->provider
        ]);

        return back()->with('success', 'Configuração do WhatsApp salva com sucesso.');
    }

    public function distribuicao(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $vendedores = Vendedor::where('gestor_id', $gestorId)
            ->where('status', 'ativo')
            ->get();

        $fila = DB::table('chat_distribuicao_fila')
            ->where('gestor_id', $gestorId)
            ->where('is_active', true)
            ->orderBy('ordem')
            ->get();

        $filaArray = $fila->keyBy('vendedor_id')->toArray();

        $vendedores->each(function ($v) use ($filaArray) {
            $v->fila_status = $filaArray[$v->id] ?? null;
        });

        $contagem = [
            'abertas' => ChatConversa::where('gestor_id', $gestorId)->where('status', 'aberta')->count(),
            'pendentes' => ChatConversa::where('gestor_id', $gestorId)->where('status', 'pendente')->count(),
            'resolvidas' => ChatConversa::where('gestor_id', $gestorId)->where('status', 'resolvida')->count(),
            'atendidos' => ChatConversa::where('gestor_id', $gestorId)->where('is_atendido', true)->count(),
            'nao_atendidos' => ChatConversa::where('gestor_id', $gestorId)->where('is_atendido', false)->count(),
        ];

        return view('chat.gestor.distribuicao', compact('vendedores', 'contagem'));
    }

    public function reorderQueue(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['error' => 'Chat desativado'], 403);
        }

        $request->validate([
            'ordem' => 'required|array',
        ]);

        foreach ($request->ordem as $vendedorId => $ordem) {
            DB::table('chat_distribuicao_fila')
                ->where('gestor_id', $gestorId)
                ->where('vendedor_id', $vendedorId)
                ->update(['ordem' => $ordem]);
        }

        Log::info('ChatGestor: Fila reordenada', [
            'gestor_id' => $gestorId,
            'ordem' => $request->ordem
        ]);

        return response()->json(['success' => true]);
    }

    public function initQueue(Request $request)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return back()->with('error', 'Módulo de chat está desativado.');
        }

        $this->distributionService->initQueueForGestor($gestorId);

        return back()->with('success', 'Fila de distribuição inicializada com sucesso.');
    }

    public function atribuir(Request $request, $conversaId)
    {
        $user = Auth::user();
        $gestorId = $user->id;

        if (!$this->distributionService->isEnabled()) {
            return back()->with('error', 'Módulo de chat está desativado.');
        }

        $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id'
        ]);

        $conversa = ChatConversa::where('gestor_id', $gestorId)
            ->where('id', $conversaId)
            ->firstOrFail();

        $vendedorAnteriorId = $conversa->vendedor_id;
        $vendedor = Vendedor::find($request->vendedor_id);

        $conversa->update([
            'vendedor_id' => $request->vendedor_id,
            'assigned_at' => now(),
            'unread_count' => 0,
            'unread_at' => null,
        ]);

        \App\Models\ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $request->vendedor_id,
            'acao' => 'atribuicao_vendedor',
            'detalhes' => "Conversa atribuída manualmente pelo gestor para {$vendedor->nome}"
        ]);

        if ($vendedorAnteriorId) {
            DB::table('chat_distribuicao_fila')
                ->where('gestor_id', $gestorId)
                ->where('vendedor_id', $vendedorAnteriorId)
                ->decrement('total_atendidos', 1);
        }

        DB::table('chat_distribuicao_fila')
            ->where('gestor_id', $gestorId)
            ->where('vendedor_id', $request->vendedor_id)
            ->increment('total_atendidos', 1, [
                'ultimo_atendimento_at' => now()
            ]);

        return back()->with('success', 'Conversa atribuída com sucesso.');
    }
}