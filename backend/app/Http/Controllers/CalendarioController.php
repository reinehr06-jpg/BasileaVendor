<?php

namespace App\Http\Controllers;

use App\Models\CalendarioEvento;
use App\Models\Vendedor;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarioController extends Controller
{
    public function adminIndex()
    {
        $eventos = CalendarioEvento::with('usuario', 'contato', 'cliente')
            ->orderBy('data_hora_inicio')->get();
        $equipes = Vendedor::with('user')->get()->groupBy('gestor_id');
        return view('master.calendario.index', compact('eventos', 'equipes'));
    }

    public function gestorIndex()
    {
        $meusEventos = CalendarioEvento::where('user_id', Auth::id())
            ->agendados()->orderBy('data_hora_inicio')->get();

        $ids = Vendedor::where('gestor_id', Auth::id())->pluck('user_id');
        $eventosEquipe = CalendarioEvento::whereIn('user_id', $ids)
            ->agendados()->with('usuario', 'contato')->orderBy('data_hora_inicio')->get();

        $vendedores = Vendedor::where('gestor_id', Auth::id())->with('user')->get();

        return view('gestor.calendario.index', compact('meusEventos', 'eventosEquipe', 'vendedores'));
    }

    public function vendedorIndex()
    {
        $eventos = CalendarioEvento::where('user_id', Auth::id())
            ->with('cliente', 'contato')->orderBy('data_hora_inicio')->get();
        return view('vendedor.calendario.index', compact('eventos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'           => 'required|string|max:255',
            'tipo'             => 'required|in:follow_up,reuniao,lembrete,vencimento',
            'data_hora_inicio' => 'required|date',
            'data_hora_fim'    => 'nullable|date|after:data_hora_inicio',
        ]);

        $evento = CalendarioEvento::create([
            ...$request->only([
                'tipo', 'titulo', 'descricao', 'data_hora_inicio',
                'data_hora_fim', 'cliente_id', 'contato_id',
            ]),
            'user_id'    => Auth::id(),
            'status'     => 'agendado',
            'criado_por' => Auth::id(),
        ]);

        // Sincroniza Google Calendar se habilitado
        if (setting('google_calendar_ativo')) {
            app(GoogleCalendarService::class)->criarEvento($evento);
        }

        return back()->with('success', 'Evento agendado!');
    }

    public function concluir(CalendarioEvento $evento)
    {
        $evento->update(['status' => 'concluido']);
        return back()->with('success', 'Marcado como concluído!');
    }

    public function marcarFaltou(CalendarioEvento $evento)
    {
        $evento->update(['status' => 'faltou']);
        // Notifica gestor
        // TODO: dispatch(new NotificarGestorFollowUpPerdido($evento));
        return back()->with('warning', 'Follow-up marcado como não realizado.');
    }

    public function sincronizar()
    {
        $eventos = app(GoogleCalendarService::class)->importarEventos();

        foreach ($eventos as $e) {
            CalendarioEvento::updateOrCreate(
                ['google_event_id' => $e['google_event_id']],
                ['user_id' => Auth::id(), 'titulo' => $e['titulo'],
                 'data_hora_inicio' => $e['inicio'], 'data_hora_fim' => $e['fim'],
                 'status' => 'agendado', 'criado_por' => Auth::id(), 'tipo' => 'reuniao']
            );
        }

        return back()->with('success', 'Eventos sincronizados com Google Calendar!');
    }
}
