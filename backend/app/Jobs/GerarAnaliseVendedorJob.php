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
            'tempo_medio_resposta' => '5 min', // TODO: calcular tempo real
        ];
    }
}