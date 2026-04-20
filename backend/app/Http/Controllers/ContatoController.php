<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use App\Models\Campanha;
use App\Models\User;
use Illuminate\Http\Request;

class ContatoController extends Controller
{
    public function index(Request $request)
    {
        $query = Contato::with('campanha', 'agente', 'vendedor')->orderByDesc('entry_date');

        // Filtros encadeados usando os scopes do Model
        if ($request->campanha_id) $query->porCampanha($request->campanha_id);
        if ($request->canal)       $query->porCanal($request->canal);
        if ($request->status)      $query->porStatus($request->status);
        if ($request->agente_id)   $query->porAgente($request->agente_id);
        if ($request->data_inicio && $request->data_fim) {
            $query->porPeriodo($request->data_inicio, $request->data_fim);
        }
        if ($request->busca) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%$busca%")
                  ->orWhere('telefone', 'like', "%$busca%")
                  ->orWhere('whatsapp', 'like', "%$busca%")
                  ->orWhere('email', 'like', "%$busca%");
            });
        }

        $contatos  = $query->paginate(50)->withQueryString();
        $campanhas = Campanha::orderBy('nome')->get();
        $agentes   = User::orderBy('name')->get();

        return view('admin.contatos.index', compact('contatos', 'campanhas', 'agentes'));
    }

    // Drawer — retorna dados completos do contato em JSON para o painel lateral
    public function drawer(Contato $contato)
    {
        $contato->load([
            'campanha',
            'agente',
            'vendedor',
            'statusLogs.usuario',
            'eventos.criador',
        ]);

        return response()->json($contato);
    }

    // Mudar status com motivo
    public function mudarStatus(Request $request, Contato $contato)
    {
        $request->validate([
            'status' => 'required|in:lead,convertido,perdido,lead_ruim',
            'motivo' => 'nullable|string|max:500',
        ]);

        $contato->cambiarStatus($request->status, $request->motivo);

        return response()->json(['success' => true, 'status' => $contato->status]);
    }

    public function show(Contato $contato)
    {
        $contato->load(['campanha', 'agente', 'vendedor', 'statusLogs.usuario', 'eventos']);
        return view('admin.contatos.show', compact('contato'));
    }

    public function update(Request $request, Contato $contato)
    {
        $contato->update($request->only([
            'nome', 'email', 'telefone', 'whatsapp', 'documento',
            'tags', 'observacoes', 'agente_id', 'vendedor_id',
        ]));

        return back()->with('success', 'Contato atualizado!');
    }
}
