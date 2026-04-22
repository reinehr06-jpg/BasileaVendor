<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatContact;
use App\Models\ChatConversa;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatAdminController extends Controller
{
    public function contatos(Request $request)
    {
        $enabled = (bool) \App\Models\Setting::get('chat_enabled', false);
        
        $busca = $request->get('q', '');
        $tag = $request->get('tag', '');
        $gestorId = $request->get('gestor');

        $query = ChatContact::query();

        if ($busca) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        if ($tag) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($gestorId) {
            $query->where('gestor_id', $gestorId);
        }

        $contatos = $query->orderBy('created_at', 'desc')->paginate(30);

        $gestores = User::where('perfil', 'gestor')->get();

        $todasTags = DB::table('chat_contacts')
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->values();

        return view('chat.admin.contatos', compact('contatos', 'busca', 'tag', 'gestorId', 'gestores', 'todasTags'));
    }

    public function chatIndex(Request $request)
    {
        $enabled = (bool) \App\Models\Setting::get('chat_enabled', false);
        
        if (!$enabled) {
            return redirect()->route('dashboard')->with('warning', 'Módulo de chat está desativado.');
        }

        $gestorId = $request->get('gestor');
        $vendedorId = $request->get('vendedor');
        $filtro = $request->get('aba', 'nao_atendidos');
        $busca = $request->get('q', '');

        $query = ChatConversa::with(['contact', 'vendedor', 'gestor', 'ultimoMensagem'])
            ->orderBy('pinned', 'desc')
            ->orderBy('last_message_at', 'desc');

        if ($gestorId) {
            $query->where('gestor_id', $gestorId);
        }

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
            'total' => ChatConversa::count(),
            'atendidos' => ChatConversa::where('is_atendido', true)->count(),
            'nao_atendidos' => ChatConversa::where('is_atendido', false)->count(),
            'abertas' => ChatConversa::where('status', 'aberta')->count(),
            'pendentes' => ChatConversa::where('status', 'pendente')->count(),
            'resolvidas' => ChatConversa::where('status', 'resolvida')->count(),
        ];

        $gestores = User::where('perfil', 'gestor')->get();
        $vendedores = Vendedor::where('status', 'ativo')->get();

        return view('chat.admin.index', compact(
            'conversas', 'contagem', 'filtro', 
            'gestorId', 'vendedorId', 'busca',
            'gestores', 'vendedores'
        ));
    }

    public function show(Request $request, $id)
    {
        $conversa = ChatConversa::with(['contact', 'vendedor', 'gestor', 'mensagens'])
            ->where('id', $id)
            ->firstOrFail();

        return view('chat.admin.conversa', compact('conversa'));
    }

    public function atualizarTags(Request $request, $contactId)
    {
        $request->validate([
            'tags' => 'nullable|array',
        ]);

        $contact = ChatContact::findOrFail($contactId);
        
        $tags = $request->tags ?? [];
        $contact->update(['tags' => $tags]);

        return response()->json(['success' => true, 'tags' => $tags]);
    }

    public function estatisticas(Request $request)
    {
        $periodo = $request->get('periodo', '7');

        $dataInicio = now()->subDays((int)$periodo);

        $contatos = ChatContact::where('created_at', '>=', $dataInicio)->count();
        
        $conversas = ChatConversa::where('created_at', '>=', $dataInicio)->count();
        
        $mensagens = DB::table('chat_mensagens')
            ->where('created_at', '>=', $dataInicio)
            ->count();

        $atendidos = ChatConversa::where('created_at', '>=', $dataInicio)
            ->where('is_atendido', true)
            ->count();

        $tempoMedioResposta = DB::table('chat_conversas')
            ->where('created_at', '>=', $dataInicio)
            ->whereNotNull('first_response_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_response_at - created_at)) / 60) as tempo_medio')
            ->value('tempo_medio');

        $topVendedores = ChatConversa::where('created_at', '>=', $dataInicio)
            ->select('vendedor_id', DB::raw('COUNT(*) as total'))
            ->groupBy('vendedor_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('vendedor')
            ->get();

        $fontes = ChatContact::where('created_at', '>=', $dataInicio)
            ->select('source', DB::raw('COUNT(*) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'contatos' => $contatos,
            'conversas' => $conversas,
            'mensagens' => $mensagens,
            'atendidos' => $atendidos,
            'tempo_medio_resposta' => round($tempoMedioResposta ?? 0, 1),
            'top_vendedores' => $topVendedores,
            'fontes' => $fontes,
        ]);
    }

    public function exportarContatos(Request $request)
    {
        $contatos = ChatContact::orderBy('created_at', 'desc')->get();

        $csv = "Nome,Telefone,Email,Source,Tags,Criado em\n";
        
        foreach ($contatos as $c) {
            $tags = is_array($c->tags) ? implode(';', $c->tags) : '';
            $csv .= "\"{$c->nome}\",\"{$c->telefone}\",\"{$c->email}\",\"{$c->source}\",\"{$tags}\",\"{$c->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=contatos_chat_' . date('Ymd') . '.csv',
        ]);
    }
}