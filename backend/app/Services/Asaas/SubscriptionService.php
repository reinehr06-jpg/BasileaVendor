<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createSubscription(array $data): array
    {
        $payload = [
            'customer' => $data['customer'],
            'billingType' => $data['billingType'] ?? 'PIX',
            'value' => $data['value'],
            'nextDueDate' => $data['nextDueDate'] ?? now()->addDays(1)->format('Y-m-d'),
            'cycle' => $data['cycle'] ?? 'MONTHLY',
            'description' => $data['description'] ?? '',
        ];

        if (isset($data['externalReference'])) {
            $payload['externalReference'] = $data['externalReference'];
        }

        if (isset($data['split'])) {
            $payload['split'] = $data['split'];
        }

        if (isset($data['creditCard'])) {
            $payload['creditCard'] = $data['creditCard'];
            if (isset($data['creditCardHolderInfo'])) {
                $payload['creditCardHolderInfo'] = $data['creditCardHolderInfo'];
            }
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/subscriptions", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Falha ao criar assinatura no Asaas: ' . $response->body());
    }

    public function updateSubscription(string $subscriptionId, array $data): array
    {
        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}", $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Falha ao atualizar assinatura no Asaas: ' . $response->body());
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->baseUrl}/subscriptions/{$subscriptionId}");

            if ($response->successful()) {
                return true;
            }

            Log::warning('Asaas: falha ao cancelar assinatura', [
                'subscriptionId' => $subscriptionId,
                'response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao cancelar assinatura', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function getSubscriptionsByCustomer(string $customerId, bool $includeDeleted = false): array
    {
        try {
            $params = [
                'customer' => $customerId,
                'limit'    => 100,
            ];
            
            if ($includeDeleted) {
                $params['includeDeleted'] = 'true';
            }

            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/subscriptions", $params);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::warning('Asaas: falha ao listar assinaturas do cliente', [
                'customer_id' => $customerId,
                'status'      => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao listar assinaturas do cliente', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);
        }

        return [];
    }
}
