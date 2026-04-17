<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatContact;
use App\Models\Chat\ChatConversation;
use App\Models\Chat\ChatMessage;
use App\Models\Vendedor;
use App\Services\Chat\ChatMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatMessageService $messageService;

    public function __construct(ChatMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function contacts(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $contacts = ChatContact::where('tenant_id', $tenantId)
            ->when($perfil !== 'admin' && $perfil !== 'gestor', function ($q) use ($user) {
                $vendedor = $user->vendedor;
                if ($vendedor) {
                    $q->whereHas('conversations', function ($cq) use ($vendedor) {
                        $cq->where('vendedor_id', $vendedor->id);
                    });
                }
            })
            ->with(['conversations' => function ($q) {
                $q->where('status', 'open')->with('vendedor.user');
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(50);

        return response()->json($contacts);
    }

    public function conversations(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;
        $status = $request->get('status', 'open');
        $atendimento = $request->get('atendimento');

        $query = ChatConversation::where('tenant_id', $tenantId)
            ->with(['contact', 'vendedor.user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }]);

        if ($perfil === 'vendedor') {
            $vendedor = $user->vendedor;
            if ($vendedor) {
                $query->where('vendedor_id', $vendedor->id);
            }
        } elseif ($perfil === 'gestor') {
            $vendedor = $user->vendedor;
            if ($vendedor && $vendedor->equipe_id) {
                $query->whereHas('vendedor', fn($q) => $q->where('equipe_id', $vendedor->equipe_id));
            }
        }

        $query->where('status', $status);

        if ($atendimento) {
            $query->where('atendimento_status', $atendimento);
        }

        $conversations = $query->orderBy('updated_at', 'desc')->paginate(30);

        return response()->json($conversations);
    }

    public function conversation($id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $conversation = ChatConversation::where('tenant_id', $tenantId)
            ->with(['contact', 'vendedor.user'])
            ->find($id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversa não encontrada'], 404);
        }

        if ($perfil === 'vendedor' && $conversation->vendedor_id !== $user->vendedor?->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        if ($perfil === 'gestor' && $conversation->vendedor?->equipe_id !== $user->vendedor?->equipe_id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $messages = ChatMessage::where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->paginate(100);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string',
            'media_url' => 'nullable|url',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $conversation = ChatConversation::where('tenant_id', $tenantId)
            ->find($conversationId);

        if (!$conversation) {
            return response()->json(['error' => 'Conversa não encontrada'], 404);
        }

        if ($user->perfil === 'vendedor' && $conversation->vendedor_id !== $user->vendedor?->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $message = $this->messageService->sendMessage(
            $tenantId,
            $conversationId,
            $request->input('message'),
            $request->input('media_url')
        );

        if (!$message) {
            return response()->json(['error' => 'Falha ao enviar mensagem'], 500);
        }

        return response()->json($message, 201);
    }

    public function resolve(Request $request, $id)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $conversation = ChatConversation::where('tenant_id', $tenantId)->find($id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversa não encontrada'], 404);
        }

        if ($user->perfil === 'vendedor' && $conversation->vendedor_id !== $user->vendedor?->id) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $conversation->is_resolved = true;
        $conversation->status = 'closed';
        $conversation->save();

        return response()->json($conversation);
    }

    public function transfer(Request $request, $id)
    {
        $request->validate([
            'vendedor_id' => 'required|exists:vendedors,id',
        ]);

        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;

        $conversation = ChatConversation::where('tenant_id', $tenantId)->find($id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversa não encontrada'], 404);
        }

        if (!in_array($user->perfil, ['admin', 'gestor'])) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $newVendedor = Vendedor::find($request->input('vendedor_id'));
        if (!$newVendedor || $newVendedor->user?->status !== 'active') {
            return response()->json(['error' => 'Vendedor inválido ou inativo'], 400);
        }

        $conversation->vendedor_id = $newVendedor->id;
        $conversation->assigned_at = now();
        $conversation->save();

        return response()->json($conversation);
    }

    public function stats()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id ?? 1;
        $perfil = $user->perfil;

        $query = ChatConversation::where('tenant_id', $tenantId);

        if ($perfil === 'vendedor') {
            $vendedor = $user->vendedor;
            if ($vendedor) {
                $query->where('vendedor_id', $vendedor->id);
            }
        } elseif ($perfil === 'gestor') {
            $vendedor = $user->vendedor;
            if ($vendedor && $vendedor->equipe_id) {
                $query->whereHas('vendedor', fn($q) => $q->where('equipe_id', $vendedor->equipe_id));
            }
        }

        $stats = [
            'total' => $query->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'nao_atendido' => (clone $query)->where('status', 'open')->where('atendimento_status', 'nao_atendido')->count(),
            'atendido' => (clone $query)->where('status', 'open')->where('atendimento_status', 'atendido')->count(),
            'resolved' => (clone $query)->where('is_resolved', true)->count(),
        ];

        return response()->json($stats);
    }

    public function markRead(Request $request, $id)
    {
        $request->validate([
            'vendedor_id' => 'required|exists:vendedors,id',
        ]);

        $messages = ChatMessage::where('conversation_id', $id)
            ->where('direction', 'inbound')
            ->where('is_read', false)
            ->get();

        foreach ($messages as $message) {
            $this->messageService->markAsRead($message->id, $request->input('vendedor_id'));
        }

        return response()->json(['marked' => $messages->count()]);
    }
}