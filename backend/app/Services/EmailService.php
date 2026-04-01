<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Jobs\SendEmailVendedorJob;
use App\Jobs\SendEmailClienteJob;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Dispara automações de email após confirmação de pagamento
     * Usar após PAYMENT_RECEIVED
     */
    public function dispararAutomacoes(Venda $venda, Pagamento $pagamento): void
    {
        try {
            SendEmailVendedorJob::dispatch($venda, $pagamento);
            Log::info('EmailService: Job vendedor enviado para fila', ['venda_id' => $venda->id]);

            SendEmailClienteJob::dispatch($venda, $pagamento);
            Log::info('EmailService: Job cliente enviado para fila', ['venda_id' => $venda->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Falha ao Disparar automacoes', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar email de confirmação de venda para o vendedor (sync - legacy)
     */
    public function enviarConfirmacaoVendedor(Venda $venda, ?Pagamento $pagamento = null): bool
    {
        try {
            $vendedor = $venda->vendedor;
            
            if (!$vendedor || !$vendedor->user || !$vendedor->user->email) {
                Log::warning('EmailService: Vendedor sem email configurado', ['venda_id' => $venda->id]);
                return false;
            }

            $pagamento = $pagamento ?? $venda->pagamentos()->first();
            if (!$pagamento) {
                Log::warning('EmailService: Pagamento não encontrado', ['venda_id' => $venda->id]);
                return false;
            }

            SendEmailVendedorJob::dispatchSync($venda, $pagamento);
            return true;
        } catch (\Exception $e) {
            Log::error('EmailService: Erro ao enviar email ao vendedor', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar email de boas-vindas para o cliente (sync - legacy)
     */
    public function enviaEmailBoasVindasCliente(Venda $venda, ?Pagamento $pagamento = null): bool
    {
        try {
            $cliente = $venda->cliente;
            
            if (!$cliente || !$cliente->email) {
                Log::warning('EmailService: Cliente sem email configurado', ['venda_id' => $venda->id]);
                return false;
            }

            $pagamento = $pagamento ?? $venda->pagamentos()->first();
            if (!$pagamento) {
                Log::warning('EmailService: Pagamento não encontrado', ['venda_id' => $venda->id]);
                return false;
            }

            SendEmailClienteJob::dispatchSync($venda, $pagamento);
            return true;
        } catch (\Exception $e) {
            Log::error('EmailService: Erro ao enviar email ao cliente', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verificar se emails já foram enviados
     */
    public function emailsJaEnviados(Venda $venda): array
    {
        return [
            'vendedor' => (bool) $venda->email_vendedor_enviado,
            'cliente' => (bool) $venda->email_cliente_enviado,
        ];
    }

    /**
     * Reenviar email do vendedor
     */
    public function reenviarEmailVendedor(Venda $venda, Pagamento $pagamento): bool
    {
        $venda->email_vendedor_enviado = false;
        $venda->save();
        
        return $this->enviarConfirmacaoVendedor($venda, $pagamento);
    }

    /**
     * Reenviar email do cliente
     */
    public function reenviarEmailCliente(Venda $venda, Pagamento $pagamento): bool
    {
        $venda->email_cliente_enviado = false;
        $venda->save();
        
        return $this->enviaEmailBoasVindasCliente($venda, $pagamento);
    }
}