<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $googleService;

    public function __construct(GoogleCalendarService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function redirect()
    {
        $url = $this->googleService->getAuthUrl();
        return redirect($url);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('dashboard')->with('error', 'A autorização do Google foi negada ou cancelada.');
        }

        if (!$request->has('code')) {
            return redirect()->route('dashboard')->with('error', 'Código de autorização não retornado pelo Google.');
        }

        try {
            $tokenInfo = $this->googleService->authenticate($request->code);

            if (isset($tokenInfo['error'])) {
                Log::error('Erro ao obter token do Google: ' . json_encode($tokenInfo));
                return redirect()->route('dashboard')->with('error', 'Falha ao autenticar com o Google.');
            }

            $user = Auth::user();
            
            $user->update([
                'google_access_token' => $tokenInfo['access_token'],
                'google_refresh_token' => $tokenInfo['refresh_token'] ?? null,
                'google_token_expires_at' => Carbon::now()->addSeconds($tokenInfo['expires_in']),
            ]);

            return redirect()->route('dashboard')->with('success', 'Conta do Google Calendar conectada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Google Auth Exception: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Ocorreu um erro ao conectar ao Google Calendar.');
        }
    }

    public function disconnect()
    {
        $user = Auth::user();
        $user->update([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);

        return redirect()->back()->with('success', 'Google Calendar desconectado com sucesso.');
    }
}
