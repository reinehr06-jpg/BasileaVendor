<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatContact;
use App\Models\ChatConversa;
use App\Models\ChatMensagem;
use App\Models\ChatAtividade;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $distributionService;

    public function __construct()
    {
        $this->distributionService = new \App\Services\Chat\ChatDistributionService();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return redirect()->route('dashboard')->with('error', 'Perfil de vendedor não encontrado.');
        }

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $vendedorId = $user->vendedor->id;
        $filtro = $request->get('aba', 'nao_atendidos');

        $query = ChatConversa::with(['contact', 'ultimoMensagem'])
            ->where('vendedor_id', $vendedorId)
            ->orderBy('pinned', 'desc')
            ->orderBy('last_message_at', 'desc');

        if ($filtro === 'atendidos') {
            $query->where('is_atendido', true);
        } elseif ($filtro === 'nao_atendidos') {
            $query->where('is_atendido', false);
        }

        $conversas = $query->paginate(30);

        $contagem = [
            'atendidos' => ChatConversa::where('vendedor_id', $vendedorId)->where('is_atendido', true)->count(),
            'nao_atendidos' => ChatConversa::where('vendedor_id', $vendedorId)->where('is_atendido', false)->count(),
        ];

        return view('chat.vendedor.index', compact('conversas', 'contagem', 'filtro'));
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return redirect()->route('dashboard')->with('error', 'Perfil de vendedor não encontrado.');
        }

        if (!$this->distributionService->isEnabled()) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::with(['contact', 'mensagens'])
            ->where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $conversa->update([
            'unread_count' => 0,
            'unread_at' => null
        ]);

        ChatMensagem::where('conversa_id', $conversa->id)
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('chat.vendedor.conversa', compact('conversa'));
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil de vendedor não encontrado.'], 403);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['error' => 'Chat desativado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'mensagem' => 'required|string|max:5000',
        ]);

        $mensagem = ChatMensagem::create([
            'conversa_id' => $conversa->id,
            'sender_id' => $vendedorId,
            'sender_type' => 'vendedor',
            'direction' => 'outbound',
            'tipo' => 'texto',
            'conteudo' => $request->mensagem,
            'delivery_status' => 'sent'
        ]);

        $conversa->adicionarMensagemSaida();

        ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'acao' => 'mensagem_enviada',
            'detalhes' => 'Mensagem enviada pelo vendedor'
        ]);

        Log::info('Chat: Mensagem enviada', [
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'mensagem_id' => $mensagem->id
        ]);

        return response()->json([
            'success' => true,
            'mensagem' => $mensagem
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil de vendedor não encontrado.'], 403);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['error' => 'Chat desativado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:aberta,pendente,resolvida'
        ]);

        $conversa->update(['status' => $request->status]);

        ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'acao' => 'status_alterado',
            'detalhes' => "Status alterado para: {$request->status}"
        ]);

        return response()->json(['success' => true]);
    }

    public function pin(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil de vendedor não encontrado.'], 403);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['error' => 'Chat desativado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $conversa->update(['pinned' => !$conversa->pinned]);

        ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'acao' => $conversa->pinned ? 'conversa_fixada' : 'conversa_desfixada',
            'detalhes' => $conversa->pinned ? 'Conversa fixada' : 'Conversa desfixada'
        ]);

        return response()->json(['success' => true, 'pinned' => $conversa->pinned]);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['nao_lidos' => 0, 'nao_atendidos' => 0]);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['nao_lidos' => 0, 'nao_atendidos' => 0]);
        }

        $vendedorId = $user->vendedor->id;

        $naoLidos = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('unread_count', '>', 0)
            ->count();

        $naoAtendidos = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('is_atendido', false)
            ->count();

        return response()->json([
            'nao_lidos' => $naoLidos,
            'nao_atendidos' => $naoAtendidos
        ]);
    }

    public function buscar(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['conversas' => []]);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['conversas' => []]);
        }

        $vendedorId = $user->vendedor->id;
        $busca = $request->get('q', '');

        if (strlen($busca) < 2) {
            return response()->json(['conversas' => []]);
        }

        $conversas = ChatConversa::with(['contact', 'ultimoMensagem'])
            ->where('vendedor_id', $vendedorId)
            ->whereHas('contact', function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            })
            ->orderBy('last_message_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json(['conversas' => $conversas]);
    }

    public function contacts(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['data' => []]);
        }

        $vendedorId = $user->vendedor->id;

        $query = ChatConversa::with(['contact'])
            ->where('vendedor_id', $vendedorId)
            ->orderBy('last_message_at', 'desc');
        
        $conversas = $query->paginate(30);

        return response()->json($conversas);
    }

    public function conversations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['data' => []]);
        }

        if (!$this->distributionService->isEnabled()) {
            return response()->json(['data' => []]);
        }

        $vendedorId = $user->vendedor->id;
        $atendimento = $request->get('atendimento');
        
        $query = ChatConversa::with(['contact', 'ultimoMensagem'])
            ->where('vendedor_id', $vendedorId)
            ->orderBy('pinned', 'desc')
            ->orderBy('last_message_at', 'desc');

        if ($atendimento === 'atendido') {
            $query->where('is_atendido', true);
        } elseif ($atendimento === 'nao_atendido') {
            $query->where('is_atendido', false);
        }

        return response()->json($query->paginate(30));
    }

    public function conversation(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil não encontrado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::with(['contact', 'mensagens'])
            ->where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $conversa->update([
            'unread_count' => 0,
            'unread_at' => null
        ]);

        ChatMensagem::where('conversa_id', $conversa->id)
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'conversation' => $conversa,
            'messages' => ['data' => $conversa->mensagens]
        ]);
    }

    public function stats(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json([
                'total' => 0,
                'open' => 0,
                'closed' => 0,
                'nao_atendido' => 0,
                'atendido' => 0,
                'resolved' => 0
            ]);
        }

        $vendedorId = $user->vendedor->id;

        return response()->json([
            'total' => ChatConversa::where('vendedor_id', $vendedorId)->count(),
            'open' => ChatConversa::where('vendedor_id', $vendedorId)->where('status', 'aberta')->count(),
            'closed' => ChatConversa::where('vendedor_id', $vendedorId)->where('status', '!=', 'aberta')->count(),
            'nao_atendido' => ChatConversa::where('vendedor_id', $vendedorId)->where('is_atendido', false)->count(),
            'atendido' => ChatConversa::where('vendedor_id', $vendedorId)->where('is_atendido', true)->count(),
            'resolved' => ChatConversa::where('vendedor_id', $vendedorId)->where('status', 'resolvida')->count()
        ]);
    }

    public function resolve(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil não encontrado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $conversa->update([
            'status' => 'resolvida',
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $vendedorId
        ]);

        ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'acao' => 'conversa_resolvida',
            'detalhes' => 'Conversa resolvida pelo vendedor'
        ]);

        return response()->json(['success' => true, 'conversation' => $conversa]);
    }

    public function transfer(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil não encontrado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id'
        ]);

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $novoVendedor = Vendedor::find($request->vendedor_id);
        
        $vendedorAnteriorId = $conversa->vendedor_id;
        
        $conversa->update([
            'vendedor_id' => $request->vendedor_id,
            'assigned_at' => now()
        ]);

        ChatAtividade::create([
            'conversa_id' => $conversa->id,
            'vendedor_id' => $vendedorId,
            'acao' => 'conversa_transferida',
            'detalhes' => "Transferida para {$novoVendedor->nome}"
        ]);

        return response()->json(['success' => true, 'conversation' => $conversa]);
    }

    public function markRead(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->vendedor) {
            return response()->json(['error' => 'Perfil não encontrado'], 403);
        }

        $vendedorId = $user->vendedor->id;

        $conversa = ChatConversa::where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->firstOrFail();

        $conversa->update([
            'unread_count' => 0,
            'unread_at' => null
        ]);
        
        ChatMensagem::where('conversa_id', $conversa->id)
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }
}