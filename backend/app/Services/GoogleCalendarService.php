<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use App\Models\User;
use App\Models\CalendarioEvento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID', ''));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET', ''));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI', config('app.url').'/google/callback'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($code)
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    public function setUserClient(User $user)
    {
        if (!$user->google_access_token) {
            return false;
        }

        $this->client->setAccessToken($user->google_access_token);

        if ($this->client->isAccessTokenExpired()) {
            if ($user->google_refresh_token) {
                $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                if (isset($newAccessToken['error'])) {
                    // Token is invalid, disconnect
                    $user->update([
                        'google_access_token' => null,
                        'google_refresh_token' => null,
                        'google_token_expires_at' => null,
                    ]);
                    return false;
                }
                
                $user->update([
                    'google_access_token' => $newAccessToken['access_token'],
                    // Sometimes refresh_token is not returned in refresh request, keep the old one
                    'google_refresh_token' => $newAccessToken['refresh_token'] ?? $user->google_refresh_token,
                    'google_token_expires_at' => Carbon::now()->addSeconds($newAccessToken['expires_in']),
                ]);
            } else {
                return false;
            }
        }

        return true;
    }

    public function criarEvento(CalendarioEvento $evento)
    {
        if (!$this->setUserClient($evento->usuario)) {
            Log::warning('Google Calendar sync falhou: Usuário não conectado.', ['user_id' => $evento->user_id]);
            return;
        }

        $service = new Calendar($this->client);
        
        $descricao = $evento->descricao ?? '';
        if ($evento->cliente) {
            $descricao .= "\n\nCliente: " . $evento->cliente->nome_igreja;
            if ($evento->cliente->whatsapp) {
                $descricao .= "\nWhatsApp: " . $evento->cliente->whatsapp;
            }
        }

        $googleEvent = new Event([
            'summary' => $evento->titulo,
            'description' => trim($descricao),
            'start' => [
                'dateTime' => $evento->data_hora_inicio->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $evento->data_hora_fim ? $evento->data_hora_fim->toRfc3339String() : $evento->data_hora_inicio->copy()->addHour()->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ],
            'extendedProperties' => [
                'private' => [
                    'basileia_event_id' => $evento->id
                ]
            ]
        ]);

        try {
            $createdEvent = $service->events->insert('primary', $googleEvent);
            $evento->update(['google_event_id' => $createdEvent->id]);
            return $createdEvent;
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento no Google Calendar: ' . $e->getMessage());
        }
    }
    
    public function deletarEvento(CalendarioEvento $evento)
    {
        if (!$evento->google_event_id || !$this->setUserClient($evento->usuario)) {
            return;
        }
        
        $service = new Calendar($this->client);
        try {
            $service->events->delete('primary', $evento->google_event_id);
            $evento->update(['google_event_id' => null]);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar evento no Google Calendar: ' . $e->getMessage());
        }
    }

    public function importarEventos(User $user)
    {
        if (!$this->setUserClient($user)) {
            return [];
        }

        $service = new Calendar($this->client);
        
        // Busca eventos próximos
        $optParams = [
            'timeMin' => Carbon::now()->startOfMonth()->toRfc3339String(),
            'timeMax' => Carbon::now()->addMonths(2)->toRfc3339String(),
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ];

        try {
            $results = $service->events->listEvents('primary', $optParams);
            $importedEvents = [];

            foreach ($results->getItems() as $event) {
                // Evita importar eventos que já foram criados pelo nosso sistema (ciclo infinito)
                if ($event->getExtendedProperties() && isset($event->getExtendedProperties()->getPrivate()['basileia_event_id'])) {
                    continue;
                }

                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                $end = $event->end->dateTime;
                if (empty($end)) {
                    $end = $event->end->date;
                }

                $importedEvents[] = [
                    'google_event_id' => $event->id,
                    'titulo' => $event->getSummary() ?? 'Evento Sem Título',
                    'inicio' => Carbon::parse($start)->format('Y-m-d H:i:s'),
                    'fim' => Carbon::parse($end)->format('Y-m-d H:i:s'),
                ];
            }

            return $importedEvents;
        } catch (\Exception $e) {
            Log::error('Erro ao importar eventos do Google Calendar: ' . $e->getMessage());
            return [];
        }
    }
}
