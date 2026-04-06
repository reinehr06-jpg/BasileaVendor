<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionLifecycleService
{
    private AsaasService $asaas;

    public function __construct()
    {
        $this->asaas = new AsaasService();
    }

    public function ativarAssinatura(Venda $venda): void
    {
        $inicio = Carbon::now();
        $vcto = $this->calcularProximoVencimento($inicio, $venda);

        $venda->update([
            'inicio_assinatura' => $inicio->toDateString(),
            'proximo_vencimento' => $vcto->toDateString(),
            'status_assinatura' => 'ativa',
            'renovacao_ativa' => true,
            'ciclo_meses' => 12,
        ]);

        Log::info('[Lifecycle] Assinatura ativada', [
            'venda_id' => $venda->id,
            'inicio' => $inicio->toDateString(),
            'vencimento' => $vcto->toDateString(),
        ]);
    }

    public function marcarInadimplente(Venda $venda, string $motivo = 'Pagamento não confirmado'): void
    {
        if ($venda->status_assinatura === 'inadimplente') {
            return;
        }

        $venda->update([
            'status_assinatura' => 'inadimplente',
            'renovacao_ativa' => false,
        ]);

        // Suspender conta no Church
        $cliente = $venda->cliente;
        if ($cliente && $cliente->church_user_id) {
            try {
                $church = new ChurchProvisioningService();
                $church->suspenderConta($cliente);
            } catch (\Exception $e) {
                Log::error('[Lifecycle] Falha ao suspender no Church', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[Lifecycle] Cliente marcado como inadimplente', [
            'venda_id' => $venda->id,
            'motivo' => $motivo,
        ]);
    }

    public function reativarAssinatura(Venda $venda): bool
    {
        $pagamento = $this->buscarPagamentoRecente($venda);
        if (!$pagamento) {
            Log::warning('[Lifecycle] Tentativa de reativar sem pagamento recente', [
                'venda_id' => $venda->id,
            ]);
            return false;
        }

        // Verificar se o pagamento foi confirmado no cartão de crédito
        if (!$this->isPagamentoConfirmado($pagamento)) {
            Log::info('[Lifecycle] Pagamento ainda não confirmado', [
                'venda_id' => $venda->id,
                'status' => $pagamento->status,
            ]);
            return false;
        }

        // Reativar assinatura e recalcular próximo vencimento
        $inicio = Carbon::now();
        $vcto = $this->calcularProximoVencimento($inicio, $venda);

        $venda->update([
            'inicio_assinatura' => $inicio->toDateString(),
            'proximo_vencimento' => $vcto->toDateString(),
            'status_assinatura' => 'ativa',
            'renovacao_ativa' => true,
            'status' => 'Pago',
        ]);

        // Reativar conta no Church
        $cliente = $venda->cliente;
        if ($cliente && $cliente->church_user_id) {
            try {
                $church = new ChurchProvisioningService();
                $church->reativarConta($cliente);
            } catch (\Exception $e) {
                Log::error('[Lifecycle] Falha ao reativar no Church', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[Lifecycle] Assinatura reativada', [
            'venda_id' => $venda->id,
            'proximo_vencimento' => $vcto->toDateString(),
        ]);

        return true;
    }

    public function verificarInadimplencia(): array
    {
        $resultado = [
            'verificadas' => 0,
            'marcadas_inadimplentes' => 0,
            'reativadas' => 0,
        ];

        // Buscar assinaturas ativas com vencimento no passado
        $assinaturasVencidas = Venda::where('status_assinatura', 'ativa')
            ->where('renovacao_ativa', true)
            ->whereNotNull('proximo_vencimento')
            ->where('proximo_vencimento', '<', Carbon::today()->toDateString())
            ->with(['cliente', 'pagamentos'])
            ->take(20)
            ->get();

        foreach ($assinaturasVencidas as $venda) {
            $resultado['verificadas']++;

            // Verificar se houve pagamento confirmado desde o vencimento
            $pagamento = $this->buscarPagamentoRecente($venda);

            if ($pagamento && $this->isPagamentoConfirmado($pagamento)) {
                // Pagamento recente confirmado - reativar
                if ($this->reativarAssinatura($venda)) {
                    $resultado['reativadas']++;
                }
            } else {
                // Sem pagamento - marcar inadimplente
                $this->marcarInadimplente($venda, 'Vencimento em ' . $venda->proximo_vencimento);
                $resultado['marcadas_inadimplentes']++;
            }
        }

        // Buscar inadimplentes e verificar se têm pagamento recente
        $inadimplentes = Venda::where('status_assinatura', 'inadimplente')
            ->where('renovacao_ativa', false)
            ->with(['cliente', 'pagamentos'])
            ->take(20)
            ->get();

        foreach ($inadimplentes as $venda) {
            $resultado['verificadas']++;

            $pagamento = $this->buscarPagamentoRecente($venda);
            if ($pagamento && $this->isPagamentoConfirmado($pagamento)) {
                if ($this->reativarAssinatura($venda)) {
                    $resultado['reativadas']++;
                }
            }
        }

        Log::info('[Lifecycle] Verificação de inadimplência concluída', $resultado);

        return $resultado;
    }

    private function calcularProximoVencimento(Carbon $data, Venda $venda): Carbon
    {
        $ciclo = $venda->ciclo_meses ?? 12;
        return $data->copy()->addMonths($ciclo);
    }

    private function buscarPagamentoRecente(Venda $venda): ?Pagamento
    {
        return $venda->pagamentos()
            ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO'])
            ->where('data_pagamento', '>=', Carbon::today()->subDays(7)->toDateTimeString())
            ->orderByDesc('data_pagamento')
            ->first();
    }

    private function isPagamentoConfirmado(Pagamento $pagamento): bool
    {
        $status = strtoupper($pagamento->status ?? '');
        return in_array($status, ['RECEIVED', 'CONFIRMED', 'PAGO']);
    }
}
