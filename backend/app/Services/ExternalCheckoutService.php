<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalCheckoutService
{
    private int $maxRetries = 3;
    private int $timeoutSeconds = 15;

    /**
     * Tenta criar uma transação no sistema de Checkout externo.
     * Com retry automático e fallback para Asaas em caso de falha.
     */
    public function createTransactionForVenda(Venda $venda, Cliente $cliente)
    {
        $apiUrl = env('CHECKOUT_API_URL');
        $apiKey = env('CHECKOUT_API_KEY', '');

        if (!$apiUrl) {
            Log::warning("Checkout: API URL não configurada. Usando fallback Asaas.");
            return $this->fallbackToAsaas($venda, 'transaction');
        }

        $endpoint = rtrim($apiUrl, '/') . '/api/v1/transactions';
        $callbackUrl = env('CHECKOUT_WEBHOOK_URL', url('/api/webhook/checkout'));

        $document = preg_replace('/[^0-9]/', '', $cliente->documento);
        $phone = preg_replace('/[^0-9]/', '', $cliente->contato ?? $cliente->whatsapp ?? '');

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

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info("Checkout: Tentativa {$attempt}/{$this->maxRetries} de criar transação", [
                    'venda_id' => $venda->id,
                    'endpoint' => $endpoint,
                ]);

                $response = Http::timeout($this->timeoutSeconds)
                    ->withHeaders([
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
                        
                        Log::info("Checkout: Transação criada com sucesso", [
                            'venda_id' => $venda->id,
                            'uuid' => $uuid,
                            'attempt' => $attempt,
                        ]);
                        
                        return ['success' => true, 'uuid' => $uuid, 'payment_url' => $paymentUrl];
                    }
                }

                $errorBody = $response->json();
                $lastError = $errorBody['message'] ?? $errorBody['error'] ?? $response->body();
                
                Log::warning("Checkout: Tentativa {$attempt} falhou", [
                    'venda_id' => $venda->id,
                    'status' => $response->status(),
                    'error' => $lastError,
                ]);

                // Se for erro de validação (422), não tentar novamente
                if ($response->status() === 422) {
                    break;
                }

            } catch (\Exception $e) {
                $lastError = 'Erro de conexão: ' . $e->getMessage();
                Log::warning("Checkout: Tentativa {$attempt} exception", [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Aguardar antes de tentar novamente (exceto na última tentativa)
            if ($attempt < $this->maxRetries) {
                usleep(500000 * $attempt); // 0.5s, 1s, 1.5s
            }
        }

        // Todas as tentativas falharam, usar fallback
        Log::error("Checkout: Todas as tentativas falharam. Usando fallback Asaas.", [
            'venda_id' => $venda->id,
            'last_error' => $lastError,
        ]);
        
        return $this->fallbackToAsaas($venda, 'transaction');

        // return ['success' => false, 'message' => $lastError ?? 'Erro desconhecido', 'status' => 500];
    }

    /**
     * Cria uma assinatura no sistema de Checkout externo.
     * Com retry automático e fallback para Asaas.
     */
    public function createSubscriptionForVenda(Venda $venda, Cliente $cliente)
    {
        $apiUrl = env('CHECKOUT_API_URL');
        $apiKey = env('CHECKOUT_API_KEY', '');

        if (!$apiUrl) {
            Log::warning("Checkout: API URL não configurada para assinatura. Usando fallback Asaas.");
            return $this->fallbackToAsaas($venda, 'subscription');
        }

        $endpoint = rtrim($apiUrl, '/') . '/api/v1/subscriptions';
        $callbackUrl = env('CHECKOUT_WEBHOOK_URL', url('/api/webhook/checkout'));

        $document = preg_replace('/[^0-9]/', '', $cliente->documento);
        $phone = preg_replace('/[^0-9]/', '', $cliente->contato ?? $cliente->whatsapp ?? '');
        $billingCycle = $this->mapBillingCycle($venda->tipo_negociacao);
        $valor = (float) ($venda->valor_final ?? $venda->valor);

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

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                Log::info("Checkout: Tentativa {$attempt}/{$this->maxRetries} de criar assinatura", [
                    'venda_id' => $venda->id,
                    'endpoint' => $endpoint,
                    'billing_cycle' => $billingCycle,
                ]);

                $response = Http::timeout($this->timeoutSeconds)
                    ->withHeaders([
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
                            'attempt' => $attempt,
                        ]);
                        
                        return ['success' => true, 'uuid' => $uuid, 'payment_url' => $paymentUrl];
                    }
                }

                $errorBody = $response->json();
                $lastError = $errorBody['message'] ?? $errorBody['error'] ?? $response->body();

                Log::warning("Checkout: Tentativa {$attempt} de assinatura falhou", [
                    'venda_id' => $venda->id,
                    'status' => $response->status(),
                    'error' => $lastError,
                ]);

                if ($response->status() === 422) {
                    break;
                }

            } catch (\Exception $e) {
                $lastError = 'Erro de conexão: ' . $e->getMessage();
                Log::warning("Checkout: Tentativa {$attempt} de assinatura exception", [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($attempt < $this->maxRetries) {
                usleep(500000 * $attempt);
            }
        }

        Log::error("Checkout: Todas as tentativas de assinatura falharam. Usando fallback Asaas.", [
            'venda_id' => $venda->id,
            'last_error' => $lastError,
        ]);

        return $this->fallbackToAsaas($venda, 'subscription');
    }

    /**
     * Fallback: cria link de pagamento direto no Asaas quando o checkout falha.
     */
    private function fallbackToAsaas(Venda $venda, string $tipo): array
    {
        try {
            $asaasService = new AsaasService();
            
            $billingType = match ($venda->forma_pagamento) {
                'pix', 'PIX' => 'PIX',
                'boleto', 'BOLETO' => 'BOLETO_BANCARIO',
                default => 'CREDIT_CARD'
            };

            $chargeType = $tipo === 'subscription' ? 'RECURRING' : 'DETACHED';

            $asaasData = [
                'billingType' => $billingType,
                'chargeType' => $chargeType,
                'name' => 'Plano ' . ($venda->plano ?? 'Basileia'),
                'value' => (float) ($venda->valor_final ?? $venda->valor),
                'externalReference' => 'venda_' . $venda->id,
                'dueDate' => now()->format('Y-m-d'),
            ];

            if ($venda->cliente?->asaas_customer_id) {
                $asaasData['client'] = $venda->cliente->asaas_customer_id;
            }

            $result = $asaasService->createPaymentLink($asaasData);

            if (!empty($result['id'])) {
                $asaasUrl = $asaasService->baseUrl . '/payment/link/' . $result['id'];
                $venda->update([
                    'asaas_payment_link_id' => $result['id'],
                    'checkout_payment_link' => $asaasUrl,
                    'modo_cobranca_asaas' => $tipo === 'subscription' ? 'SUBSCRIPTION' : 'TRANSACTION',
                ]);

                Log::info("Fallback Asaas: Link criado com sucesso", [
                    'venda_id' => $venda->id,
                    'asaas_id' => $result['id'],
                    'tipo' => $tipo,
                ]);

                return [
                    'success' => true,
                    'uuid' => $result['id'],
                    'payment_url' => $asaasUrl,
                    'fallback' => true,
                    'provider' => 'asaas',
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao criar link no Asaas: resposta vazia',
                'status' => 500,
            ];

        } catch (\Exception $e) {
            Log::error("Fallback Asaas: Erro ao criar link", [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Checkout e Asaas indisponíveis: ' . $e->getMessage(),
                'status' => 503,
            ];
        }
    }

    private function mapPaymentMethod(?string $method): string
    {
        $method = strtolower($method ?? '');
        if (str_contains($method, 'cart') || str_contains($method, 'credit')) return 'credit_card';
        if (str_contains($method, 'pix')) return 'pix';
        if (str_contains($method, 'bolet')) return 'boleto';
        return 'credit_card';
    }

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
