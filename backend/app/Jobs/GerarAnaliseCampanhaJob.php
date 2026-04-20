<?php

namespace App\Jobs;

use App\Services\AI\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GerarAnaliseCampanhaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    public function __construct(
        public int $campanhaId
    ) {}

    public function handle(AIService $ai): void
    {
        Log::info('GerarAnaliseCampanhaJob: Iniciando', [
            'campanha_id' => $this->campanhaId
        ]);

        // Buscar dados da campanha
        $campanha = \App\Models\Campanha::findOrFail($this->campanhaId);

        // Calcular métricas
        $dados = $this->calcularDados($campanha);

        // Executar análise via IA
        $result = $ai->executar('analise_campanha', $dados, auth()->id());

        if ($result['success']) {
            // Salvar em cache por 24h
            $cacheKey = "analise_campanha_{$this->campanhaId}";
            Cache::put($cacheKey, $result['output'], now()->addHours(24));

            Log::info('GerarAnaliseCampanhaJob: Concluída', [
                'campanha_id' => $this->campanhaId
            ]);
        } else {
            Log::error('GerarAnaliseCampanhaJob: Falhou', [
                'campanha_id' => $this->campanhaId,
                'erro' => $result['error'] ?? 'Erro desconhecido'
            ]);
        }
    }

    private function calcularDados($campanha): array
    {
        // Contagem de leads da campanha
        $leadsTotais = \App\Models\Lead::where('campanha', $campanha->nome)->count();
        
        // Conversões
        $leadsConvertidos = \App\Models\Lead::where('campanha', $campanha->nome)
            ->where('status', 'convertido')
            ->count();

        // Canais (se existirem)
        $canais = [];
        if ($campanha->canais) {
            $canais = is_array($campanha->canais) 
                ? $campanha->canais 
                : json_decode($campanha->canais, true) ?? [];
        }

        return [
            'nome' => $campanha->nome,
            'data_inicio' => $campanha->data_inicio?->format('d/m/Y') ?? '-',
            'data_fim' => $campanha->data_fim?->format('d/m/Y') ?? '-',
            'leads_totais' => $leadsTotais,
            'leads_convertidos' => $leadsConvertidos,
            'investimento' => $campanha->orcamento ?? 0,
            'canais' => $canais,
        ];
    }
}