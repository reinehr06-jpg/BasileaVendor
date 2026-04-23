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
        try {
            $meusEventos = CalendarioEvento::where('user_id', Auth::id())
                ->agendados()->orderBy('data_hora_inicio')->get();

            $ids = Vendedor::where('gestor_id', Auth::id())->pluck('id');
            if ($ids->isNotEmpty()) {
                $eventosEquipe = CalendarioEvento::whereIn('user_id', $ids)
                    ->agendados()->with('usuario', 'contato')->orderBy('data_hora_inicio')->get();
            } else {
                $eventosEquipe = collect([]);
            }

            $vendedores = Vendedor::where('gestor_id', Auth::id())->with('user')->get();
        } catch (\Exception $e) {
            $meusEventos = collect([]);
            $eventosEquipe = collect([]);
            $vendedores = collect([]);
        }

        return view('gestor.calendario.index', compact('meusEventos', 'eventosEquipe', 'vendedores'));
    }

    public function vendedorIndex()
    {
        try {
            $eventos = CalendarioEvento::where('user_id', Auth::id())
                ->with('cliente', 'contato')->orderBy('data_hora_inicio')->get();
        } catch (\Exception $e) {
            $eventos = collect([]);
        }
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

        // Sincroniza Google Calendar se conectado
        if (Auth::user()->google_access_token) {
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
        $user = Auth::user();
        if (!$user->google_access_token) {
            return back()->with('error', 'Conecte sua conta do Google primeiro.');
        }

        $eventos = app(GoogleCalendarService::class)->importarEventos($user);

        foreach ($eventos as $e) {
            CalendarioEvento::updateOrCreate(
                ['google_event_id' => $e['google_event_id']],
                [
                    'user_id' => $user->id, 
                    'titulo' => $e['titulo'],
                    'data_hora_inicio' => $e['inicio'], 
                    'data_hora_fim' => $e['fim'],
                    'status' => 'agendado', 
                    'criado_por' => $user->id, 
                    'tipo' => 'reuniao'
                ]
            );
        }

        return back()->with('success', 'Eventos sincronizados com Google Calendar!');
    }

    /**
     * Download .ics file for a calendar event (RFC 5545).
     * Works natively on macOS (Apple Calendar), Windows (Outlook),
     * and Linux (GNOME Calendar / Thunderbird).
     * Includes a 5-minute reminder (VALARM).
     */
    public function downloadIcs(CalendarioEvento $evento)
    {
        $uid = 'basileia-' . $evento->id . '@basileia.global';
        $now = gmdate('Ymd\THis\Z');
        $dtstart = $evento->data_hora_inicio->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtend = $evento->data_hora_fim
            ? $evento->data_hora_fim->setTimezone('UTC')->format('Ymd\THis\Z')
            : $evento->data_hora_inicio->addHour()->setTimezone('UTC')->format('Ymd\THis\Z');

        $summary = str_replace(["\r", "\n", ",", ";"], [' ', ' ', '\,', '\;'], $evento->titulo);
        $description = str_replace(["\r", "\n", ",", ";"], [' ', ' ', '\,', '\;'], $evento->descricao ?? '');

        $tipoLabel = match($evento->tipo) {
            'follow_up' => 'Follow-up',
            'reuniao' => 'Reunião',
            'lembrete' => 'Lembrete',
            'vencimento' => 'Vencimento',
            default => ucfirst($evento->tipo),
        };

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Basileia Vendas//Calendar//PT\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:Basiléia Vendas\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$now}\r\n";
        $ics .= "DTSTART:{$dtstart}\r\n";
        $ics .= "DTEND:{$dtend}\r\n";
        $ics .= "SUMMARY:[{$tipoLabel}] {$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "BEGIN:VALARM\r\n";
        $ics .= "TRIGGER:-PT5M\r\n";
        $ics .= "ACTION:DISPLAY\r\n";
        $ics .= "DESCRIPTION:Lembrete: {$summary} em 5 minutos\r\n";
        $ics .= "END:VALARM\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        $filename = 'evento-' . \Str::slug($evento->titulo) . '.ics';

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

