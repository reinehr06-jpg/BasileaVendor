<?php

namespace App\Services\Checkout;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class CheckoutClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('checkout-integration.base_url', Setting::get('checkout_external_url', '')), '/');
        $this->apiKey = config('checkout-integration.api_key', Setting::get('checkout_api_key', ''));
        $this->timeout = config('checkout-integration.timeout', 30);
    }

    // ─── Transações ──────────────────────────────────────────────

    public function createTransaction(array $data): array
    {
        $response = $this->request('POST', '/api/v1/transactions', $data);

        Log::info('Checkout: Transação criada', [
            'external_id' => $data['external_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'response' => $response,
        ]);

        return $response;
    }

    public function getTransaction(string $transactionUuid): array
    {
        return $this->request('GET', "/api/v1/transactions/{$transactionUuid}");
    }

    public function getTransactionByExternalId(string $externalId): array
    {
        return $this->request('GET', '/api/v1/transactions', ['external_id' => $externalId]);
    }

    public function cancelTransaction(string $transactionUuid): array
    {
        return $this->request('POST', "/api/v1/transactions/{$transactionUuid}/cancel");
    }

    public function refundTransaction(string $transactionUuid, ?float $amount = null): array
    {
        return $this->request('POST', "/api/v1/transactions/{$transactionUuid}/refund", [
            'amount' => $amount,
        ]);
    }

    // ─── Pagamentos ──────────────────────────────────────────────

    public function processPayment(string $transactionUuid, array $paymentData): array
    {
        $response = $this->request('POST', '/api/v1/payments/process', array_merge([
            'transaction_uuid' => $transactionUuid,
        ], $paymentData));

        return $response;
    }

    public function getPaymentStatus(string $paymentUuid): array
    {
        return $this->request('GET', "/api/v1/payments/{$paymentUuid}/status");
    }

    public function getPixData(string $paymentUuid): array
    {
        return $this->request('GET', "/api/v1/payments/{$paymentUuid}/pix");
    }

    public function getBoletoData(string $paymentUuid): array
    {
        return $this->request('GET', "/api/v1/payments/{$paymentUuid}/boleto");
    }

    // ─── Clientes ────────────────────────────────────────────────

    public function createCustomer(array $data): array
    {
        return $this->request('POST', '/api/v1/customers', $data);
    }

    public function getCustomer(string $customerId): array
    {
        return $this->request('GET', "/api/v1/customers/{$customerId}");
    }

    public function updateCustomer(string $customerId, array $data): array
    {
        return $this->request('PUT', "/api/v1/customers/{$customerId}", $data);
    }

    // ─── Assinaturas ─────────────────────────────────────────────

    public function createSubscription(array $data): array
    {
        return $this->request('POST', '/api/v1/subscriptions', $data);
    }

    public function getSubscription(string $subscriptionId): array
    {
        return $this->request('GET', "/api/v1/subscriptions/{$subscriptionId}");
    }

    public function pauseSubscription(string $subscriptionId): array
    {
        return $this->request('POST', "/api/v1/subscriptions/{$subscriptionId}/pause");
    }

    public function resumeSubscription(string $subscriptionId): array
    {
        return $this->request('POST', "/api/v1/subscriptions/{$subscriptionId}/resume");
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->request('DELETE', "/api/v1/subscriptions/{$subscriptionId}");
    }

    // ─── Relatórios ──────────────────────────────────────────────

    public function getSummary(array $filters = []): array
    {
        return $this->request('GET', '/api/v1/reports/summary', $filters);
    }

    // ─── HTTP Client ─────────────────────────────────────────────

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $http = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson();

            $response = match (strtoupper($method)) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Checkout API error', [
                'method' => $method,
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'error' => true,
                'message' => $response->json('message') ?? 'Erro na comunicação com o Checkout',
                'status' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Checkout API exception', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'Não foi possível conectar ao serviço de Checkout',
            ];
        }
    }

    // ─── Health Check ────────────────────────────────────────────

    public function ping(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/v1/auth/login");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
