<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalCheckoutService
{
    /**
     * Tenta criar uma transação no sistema de Checkout externo.
     * Retorna o UUID da transação ou nulo se falhar.
     */
    public function createTransactionForVenda(Venda $venda, Cliente $cliente)
    {
        $apiUrl = env('CHECKOUT_API_URL');
        $apiKey = env('CHECKOUT_API_KEY', '');

        if (!$apiUrl) {
            Log::warning("Checkout API URL não está configurada no .env.");
            return null;
        }

        $endpoint = rtrim($apiUrl, '/') . '/api/v1/transactions';
        // Você pode configurar customizadamente o webhook ou abstrair usando a url do sistema local
        $callbackUrl = env('CHECKOUT_WEBHOOK_URL', url('/api/webhook/checkout')); 

        // Converte limpar máscaras
        $document = preg_replace('/[^0-9]/', '', $cliente->documento);
        $phone = preg_replace('/[^0-9]/', '', $cliente->contato ?? $cliente->whatsapp ?? '');

        // Formato pedido pelo payload do Checkout Externo
        $payload = [
            'external_id' => 'venda_' . $venda->id,
            'amount' => (float) ($venda->valor_final ?? $venda->valor),
            'description' => $venda->plano ?? 'Plano',
            'payment_method' => $this->mapPaymentMethod($venda->forma_pagamento),
            'installments' => (int) ($venda->parcelas ?? 1),
            'customer' => [
                'name' => $cliente->nome_igreja ?? $cliente->nome,
                'email' => $cliente->email ?? '',
                'phone' => $phone,
                'document' => $document,
                'address' => [
                    'street' => $cliente->endereco ?? '',
                    'number' => $cliente->numero ?? '',
                    'neighborhood' => $cliente->bairro ?? '',
                    'city' => $cliente->cidade ?? '',
                    'state' => strtoupper($cliente->estado ?? ''),
                    'postalCode' => preg_replace('/[^0-9]/', '', $cliente->cep ?? '')
                ]
            ],
            'metadata' => [
                'venda_id' => $venda->id,
                'plano' => $venda->plano ?? '',
                'ciclo' => $venda->tipo_negociacao ?? ''
            ],
            'callback_url' => $callbackUrl,
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $uuid = $data['transaction']['uuid'] ?? null;
                $paymentUrl = $data['transaction']['payment_url'] ?? null;

                if ($uuid) {
                    $venda->update([
                        'checkout_transaction_uuid' => $uuid,
                        'checkout_payment_link' => $paymentUrl
                    ]);
                    
                    return ['success' => true, 'uuid' => $uuid, 'payment_url' => $paymentUrl];
                }
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? $response->body();

            Log::error("Falha ao criar transação no checkout externo para Venda {$venda->id}", [
                'status' => $response->status(),
                'body' => $errorBody,
                'payload' => $payload,
            ]);

            return ['success' => false, 'message' => $errorMessage, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error("Exceção ao ligar para o Checkout externo (Venda {$venda->id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro de conexão com o checkout: ' . $e->getMessage(), 'status' => 0];
        }
    }

    /**
     * Mapeia nomenclatura interna para a API externa
     */
    private function mapPaymentMethod(?string $method): string
    {
        $method = strtolower($method ?? '');
        if (str_contains($method, 'cart') || str_contains($method, 'credit')) return 'credit_card';
        if (str_contains($method, 'pix')) return 'pix';
        if (str_contains($method, 'bolet')) return 'boleto';
        
        return 'credit_card'; // default de fallback
    }

    /**
     * Cria uma assinatura (subscription) no sistema de Checkout externo.
     * Para vendas mensais/recorrentes.
     */
    public function createSubscriptionForVenda(Venda $venda, Cliente $cliente)
    {
        $apiUrl = env('CHECKOUT_API_URL');
        $apiKey = env('CHECKOUT_API_KEY', '');

        if (!$apiUrl) {
            Log::warning("Checkout API URL não está configurada no .env.");
            return null;
        }

        $endpoint = rtrim($apiUrl, '/') . '/api/v1/subscriptions';
        $callbackUrl = env('CHECKOUT_WEBHOOK_URL', url('/api/webhook/checkout'));

        $document = preg_replace('/[^0-9]/', '', $cliente->documento);
        $phone = preg_replace('/[^0-9]/', '', $cliente->contato ?? $cliente->whatsapp ?? '');

        $billingCycle = $this->mapBillingCycle($venda->tipo_negociacao);
        $valor = (float) ($venda->valor_final ?? $venda->valor);

        // Para plano anual com parcelas, usar valor anual
        if ($venda->tipo_negociacao === 'anual' && $venda->parcelas > 1) {
            $plano = \App\Models\Plano::where('nome', $venda->plano)->first();
            if ($plano && $plano->valor_anual > 0) {
                $valor = (float) $plano->valor_anual;
            }
        }

        $payload = [
            'plan_name' => $venda->plano ?? 'Plano',
            'amount' => $valor,
            'billing_cycle' => $billingCycle,
            'customer' => [
                'name' => $cliente->nome_igreja ?? $cliente->nome,
                'email' => $cliente->email ?? '',
                'document' => $document,
                'phone' => $phone,
                'address' => [
                    'street' => $cliente->endereco ?? '',
                    'number' => $cliente->numero ?? '',
                    'neighborhood' => $cliente->bairro ?? '',
                    'city' => $cliente->cidade ?? '',
                    'state' => strtoupper($cliente->estado ?? ''),
                    'postalCode' => preg_replace('/[^0-9]/', '', $cliente->cep ?? ''),
                ],
            ],
            'metadata' => [
                'venda_id' => $venda->id,
                'plano' => $venda->plano ?? '',
                'ciclo' => $venda->tipo_negociacao ?? '',
                'forma_pagamento' => $venda->forma_pagamento ?? '',
            ],
            'callback_url' => $callbackUrl,
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $uuid = $data['subscription']['uuid'] ?? $data['uuid'] ?? null;
                $paymentUrl = $data['subscription']['payment_url'] ?? $data['payment_url'] ?? null;

                if ($uuid) {
                    $venda->update([
                        'checkout_transaction_uuid' => $uuid,
                        'checkout_payment_link' => $paymentUrl,
                        'modo_cobranca_asaas' => 'SUBSCRIPTION',
                    ]);
                    
                    Log::info("Checkout: Assinatura criada com sucesso", [
                        'venda_id' => $venda->id,
                        'uuid' => $uuid,
                        'billing_cycle' => $billingCycle,
                    ]);
                    
                    return ['success' => true, 'uuid' => $uuid, 'payment_url' => $paymentUrl];
                }
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? $response->body();

            Log::error("Falha ao criar assinatura no checkout externo para Venda {$venda->id}", [
                'status' => $response->status(),
                'body' => $errorBody,
                'payload' => $payload,
            ]);

            return ['success' => false, 'message' => $errorMessage, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error("Exceção ao criar assinatura no checkout externo (Venda {$venda->id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro de conexão com o checkout: ' . $e->getMessage(), 'status' => 0];
        }
    }

    /**
     * Mapeia tipo de negociação para billing cycle do checkout.
     */
    private function mapBillingCycle(?string $tipoNegociacao): string
    {
        return match (strtolower($tipoNegociacao ?? '')) {
            'anual', 'annual', 'yearly' => 'yearly',
            'trimestral', 'quarterly' => 'quarterly',
            'semestral', 'semiannual' => 'semiannual',
            default => 'monthly',
        };
    }
}
