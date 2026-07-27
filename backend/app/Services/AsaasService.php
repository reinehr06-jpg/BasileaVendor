<?php

namespace App\Services;

use App\Models\Vendedor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Asaas\CustomerService;
use App\Services\Asaas\PaymentService;
use App\Services\Asaas\SubscriptionService;
use App\Services\Asaas\SplitService;

class AsaasService
{
    public string $baseUrl;
    protected string $apiKey;

    protected CustomerService $customerService;
    protected PaymentService $paymentService;
    protected SubscriptionService $subscriptionService;
    protected SplitService $splitService;

    public function __construct()
    {
        $ambiente = \App\Models\Setting::get('asaas_environment', config('services.asaas.ambiente', env('ASAAS_ENVIRONMENT', 'sandbox')));
        
        $this->baseUrl = $ambiente === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://api-sandbox.asaas.com/v3';

        $this->apiKey = \App\Models\Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));

        if (empty($this->apiKey)) {
            Log::warning('AsaasService: API Key não configurada. As requisições irão falhar.');
        } else {
            if ($ambiente === 'production' && !str_starts_with($this->apiKey, '$aact_prod_')) {
                Log::error('AsaasService: Chave de API de PRODUÇÃO parece incorreta (não começa com $aact_prod_).');
            } elseif ($ambiente === 'sandbox' && !str_starts_with($this->apiKey, '$aact_hmlg_')) {
                Log::error('AsaasService: Chave de API de SANDBOX parece incorreta (não começa com $aact_hmlg_).');
            }
        }

        $this->customerService = new CustomerService($this->baseUrl, $this->apiKey);
        $this->paymentService = new PaymentService($this->baseUrl, $this->apiKey);
        $this->subscriptionService = new SubscriptionService($this->baseUrl, $this->apiKey);
        $this->splitService = new SplitService($this->baseUrl, $this->apiKey);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'User-Agent'   => env('ASAAS_USER_AGENT', 'BasileaVendor/1.0'),
        ];
    }

    // ============================================
    // Generic request for custom payloads
    // ============================================
    public function requestAsaas(string $method, string $endpoint, array $payload = []): array
    {
        $response = Http::withHeaders($this->headers());
        $method = strtoupper($method);
        
        if ($method === 'POST') {
            $response = $response->post("{$this->baseUrl}{$endpoint}", $payload);
        } else if ($method === 'GET') {
            $response = $response->get("{$this->baseUrl}{$endpoint}", $payload);
        } else if ($method === 'PUT') {
            $response = $response->put("{$this->baseUrl}{$endpoint}", $payload);
        } else if ($method === 'DELETE') {
            $response = $response->delete("{$this->baseUrl}{$endpoint}", $payload);
        } else {
            throw new \Exception("Método HTTP não suportado: {$method}");
        }

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        Log::error("Asaas: erro ao realizar {$method} {$endpoint}", [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception("Falha na requisição para o Asaas ($endpoint): " . $response->body());
    }

    public static function mapStatus(string $asaasStatus): string
    {
        return match (strtoupper($asaasStatus)) {
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'Aguardando pagamento',
            'CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH' => 'Pago',
            'OVERDUE' => 'Vencido',
            'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE' => 'Estornado',
            'DUNNING_REQUESTED', 'DUNNING_RECEIVED' => 'Inadimplente',
            'CANCELED', 'DELETED' => 'Cancelado',
            default => 'Cancelado',
        };
    }

    // Delegations to CustomerService
    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        return $this->customerService->findCustomerByCpfCnpj($cpfCnpj);
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        return $this->customerService->createCustomer($name, $cpfCnpj, $phone, $email);
    }

    // Delegations to SplitService
    public function validateWallet(string $walletId): array
    {
        return $this->splitService->validateWallet($walletId);
    }

    public function buildSplitArray(Vendedor $vendedor, float $valorVenda, string $tipoVenda = 'inicial'): array
    {
        return $this->splitService->buildSplitArray($vendedor, $valorVenda, $tipoVenda);
    }

    // Delegations to SubscriptionService
    public function createSubscription(array $data): array
    {
        return $this->subscriptionService->createSubscription($data);
    }

    public function updateSubscription(string $subscriptionId, array $data): array
    {
        return $this->subscriptionService->updateSubscription($subscriptionId, $data);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        return $this->subscriptionService->cancelSubscription($subscriptionId);
    }

    public function getSubscriptionsByCustomer(string $customerId, bool $includeDeleted = false): array
    {
        return $this->subscriptionService->getSubscriptionsByCustomer($customerId, $includeDeleted);
    }

    // Delegations to PaymentService
    public function createPayment(
        string $customerAsaasId,
        float $value,
        string $dueDate,
        string $billingType,
        string $description,
        ?string $externalReference = null,
        ?array $split = null,
        ?array $creditCard = null,
        ?array $creditCardHolderInfo = null
    ): array {
        return $this->paymentService->createPayment(
            $customerAsaasId, $value, $dueDate, $billingType, $description,
            $externalReference, $split, $creditCard, $creditCardHolderInfo
        );
    }

    public function getPayment(string $paymentId): ?array
    {
        return $this->paymentService->getPayment($paymentId);
    }

    public function getPixQrCode(string $paymentId): ?array
    {
        return $this->paymentService->getPixQrCode($paymentId);
    }

    public function getIdentificationField(string $paymentId): ?string
    {
        return $this->paymentService->getIdentificationField($paymentId);
    }

    public function getInvoice(string $paymentId): ?array
    {
        return $this->paymentService->getInvoice($paymentId);
    }

    public function refundPayment(string $paymentId, ?float $value = null): array
    {
        return $this->paymentService->refundPayment($paymentId, $value);
    }

    public function cancelPayment(string $paymentId): bool
    {
        return $this->paymentService->cancelPayment($paymentId);
    }

    public function deletePayment(string $paymentId): bool
    {
        return $this->paymentService->deletePayment($paymentId);
    }

    public function createPaymentLink(array $data): array
    {
        return $this->paymentService->createPaymentLink($data);
    }

    public function deletePaymentLink(string $id): bool
    {
        return $this->paymentService->deletePaymentLink($id);
    }

    public function getPaymentsByCustomer(
        string $customerId,
        ?\Carbon\Carbon $startDate = null,
        ?\Carbon\Carbon $endDate = null,
        ?string $status = null
    ): array {
        return $this->paymentService->getPaymentsByCustomer($customerId, $startDate, $endDate, $status);
    }

    // Custom Checkout compatibility
    public function criarCobranca(string $customerAsaasId, array $dadosVenda, ?array $creditCard = null): array
    {
        $venda = \App\Models\Venda::find($dadosVenda['id']);
        
        $billingType = match($dadosVenda['tipo_pagamento'] ?? 'pix') {
            'cartao' => 'CREDIT_CARD',
            'pix'    => 'PIX',
            'boleto' => 'BOLETO',
            default  => 'PIX'
        };

        $description = "Pagamento - " . ($venda->plano ?? 'Venda #' . $venda->id);
        
        $isBoleto = $billingType === 'BOLETO';
        $isAnual = $venda && in_array(strtolower($venda->tipo_negociacao ?? ''), ['anual', 'annual']);
        
        if ($isAnual) {
            $dueDate = now()->addDays($isBoleto ? 5 : 15)->format('Y-m-d');
        } else {
            $dueDate = now()->addDays(5)->format('Y-m-d');
        }
        
        if ($billingType === 'CREDIT_CARD') {
            $dueDate = now()->format('Y-m-d');
        }

        $split = [];
        if ($venda && $venda->vendedor) {
            $split = $this->buildSplitArray($venda->vendedor, $venda->valor, $venda->tipo_negociacao ?? 'inicial');
        }

        $asaasResponse = $this->createPayment(
            $customerAsaasId,
            (float) $dadosVenda['valor_total'],
            $dueDate,
            $billingType,
            $description,
            (string) $dadosVenda['id'],
            $split,
            $creditCard
        );

        return [
            'asaas_payment_id'  => $asaasResponse['id'],
            'bank_slip_url'     => $asaasResponse['bankSlipUrl'] ?? null,
            'invoice_url'       => $asaasResponse['invoiceUrl'] ?? null,
            'pix_copia_cola'    => $asaasResponse['pixCopiaCola'] ?? null,
            'pix_qrcode'        => $asaasResponse['pixQrCode'] ?? null,
            'cartao_token'      => $asaasResponse['creditCardToken'] ?? null,
            'cartao_bandeira'   => $asaasResponse['creditCard']['creditCardBrand'] ?? null,
            'cartao_final'      => $asaasResponse['creditCard']['creditCardNumber'] ?? null,
        ];
    }
}
