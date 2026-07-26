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

class GerarAnaliseVendedorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    public function __construct(
        public int $vendedorId,
        public string $mes
    ) {}

    public function handle(AIService $ai): void
    {
        Log::info('GerarAnaliseVendedorJob: Iniciando', [
            'vendedor_id' => $this->vendedorId,
            'mes' => $this->mes
        ]);

        // Calcular estatísticas do vendedor
        $stats = $this->calcularStats();

        // Executar análise via IA
        $result = $ai->executar('analise_vendedor', $stats, auth()->id());

        if ($result['success']) {
            // Salvar em cache por 24h
            $cacheKey = "analise_vendedor_{$this->vendedorId}_{$this->mes}";
            Cache::put($cacheKey, $result['output'], now()->addHours(24));

            Log::info('GerarAnaliseVendedorJob: Concluída', [
                'vendedor_id' => $this->vendedorId,
                'mes' => $this->mes
            ]);
        } else {
            Log::error('GerarAnaliseVendedorJob: Falhou', [
                'vendedor_id' => $this->vendedorId,
                'erro' => $result['error'] ?? 'Erro desconhecido'
            ]);
        }
    }

    private function calcularStats(): array
    {
        $vendedor = \App\Models\Vendedor::with('user')->findOrFail($this->vendedorId);
        
        // Calcular métricas do período
        $dataInicio = \Carbon\Carbon::parse($this->mes . '-01');
        $dataFim = $dataInicio->copy()->endOfMonth();

        // Leads atendidos no período
        $leadsAtendidos = \App\Models\Lead::where('seller_id', $this->vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->count();

        // Conversões no período
        $conversoes = \App\Models\Lead::where('seller_id', $this->vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->where('status', 'convertido')
            ->count();

        // Ticket médio (calcular média de vendas)
        $ticketMedio = \App\Models\Venda::where('vendedor_id', $this->vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->avg('valor') ?? 0;

        return [
            'nome' => $vendedor->user->name ?? 'Vendedor',
            'mes' => $this->mes,
            'leads_atendidos' => $leadsAtendidos,
            'conversoes' => $conversoes,
            'ticket_medio' => number_format($ticketMedio, 2, ',', '.'),
            'tempo_medio_resposta' => $this->calcularTempoMedioResposta($this->vendedorId, $dataInicio, $dataFim),
        ];
    }

    /**
     * Calcula o tempo médio de resposta do vendedor no período com base nas
     * mensagens de chat. Para cada conversa do vendedor no período, busca o
     * delta entre a primeira mensagem inbound (do lead) e a primeira mensagem
     * outbound subsequente (do vendedor). Retorna string legível ou null se
     * não há dados suficientes.
     */
    private function calcularTempoMedioResposta(int $vendedorId, \Carbon\Carbon $inicio, \Carbon\Carbon $fim): ?string
    {
        // Conversas do vendedor com mensagens no período
        $conversas = \App\Models\ChatConversa::where('vendedor_id', $vendedorId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->pluck('id');

        if ($conversas->isEmpty()) {
            return null;
        }

        $tempos = [];

        foreach ($conversas as $conversaId) {
            // Primeira mensagem inbound (do lead/cliente)
            $inbound = \App\Models\ChatMensagem::where('conversa_id', $conversaId)
                ->where('direction', 'inbound')
                ->orderBy('created_at')
                ->first();

            if (!$inbound) {
                continue;
            }

            // Primeira mensagem outbound (do vendedor) após o inbound
            $outbound = \App\Models\ChatMensagem::where('conversa_id', $conversaId)
                ->where('direction', 'outbound')
                ->where('created_at', '>', $inbound->created_at)
                ->orderBy('created_at')
                ->first();

            if (!$outbound) {
                continue;
            }

            $tempos[] = $outbound->created_at->diffInSeconds($inbound->created_at);
        }

        if (empty($tempos)) {
            return null;
        }

        $mediaSeg = (int) round(array_sum($tempos) / count($tempos));

        if ($mediaSeg < 60) {
            return "{$mediaSeg} seg";
        }

        $minutos = (int) round($mediaSeg / 60);
        if ($minutos < 60) {
            return "{$minutos} min";
        }

        $horas = round($minutos / 60, 1);
        return "{$horas} h";
    }
}