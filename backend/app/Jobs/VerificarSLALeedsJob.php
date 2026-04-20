<?php

namespace App\Jobs;

use App\Models\Contato;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class VerificarSLALeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        $slaMinutos = setting('sla_primeiro_atendimento', 60);
        
        $leadsSemResposta = Contato::where('status', 'lead')
            ->whereNull('agente_id')
            ->whereNull('vendedor_id')
            ->where('entry_date', '<=', now()->subMinutes($slaMinutos))
            ->where('entry_date', '>=', now()->subDays(7))
            ->get();

        if ($leadsSemResposta->isEmpty()) {
            Log::info('SLA_LEEDS_VERIFICADO', ['encontrados' => 0, 'sla_minutos' => $slaMinutos]);
            return;
        }

        Log::warning('SLA_LEEDS_VENCIDOS', [
            'quantidade' => $leadsSemResposta->count(),
            'sla_minutos' => $slaMinutos,
            'leads' => $leadsSemResposta->pluck('id')->toArray(),
        ]);

        $gestores = User::whereIn('perfil', ['gestor', 'master'])
            ->where('notifications_sla', true)
            ->get();

        foreach ($gestores as $gestor) {
            Notification::send($gestor, new \App\Notifications\SLAVencidoNotification($leadsSemResposta, $slaMinutos));
        }
    }
}