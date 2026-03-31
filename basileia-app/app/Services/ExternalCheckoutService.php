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
            'external_id' => (string) $venda->id,
            'amount' => (float) ($venda->valor_final ?? $venda->valor),
            'description' => $venda->plano ?? 'Plano',
            'payment_method' => $this->mapPaymentMethod($venda->forma_pagamento),
            'installments' => (int) ($venda->parcelas ?? 1),
            'customer' => [
                'name' => $cliente->nome,
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
            // A API key pode ser enviada no Header de Autorização caso o seu Checkout implemente Sanctum ou Passport.
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}"
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $uuid = $data['transaction']['uuid'] ?? null;
                $paymentUrl = $data['transaction']['payment_url'] ?? null;

                // Se o UUID não vier mas na documentação estiver em outro campo, ele adapta.
                if ($uuid) {
                    $venda->update([
                        'checkout_transaction_uuid' => $uuid,
                        'checkout_payment_link' => $paymentUrl
                    ]);
                    
                    return $uuid;
                }
            }

            Log::error("Falha ao criar transação no checkout externo para Venda {$venda->id}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Exceção ao ligar para o Checkout externo (Venda {$venda->id}): " . $e->getMessage());
            return null;
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
}
