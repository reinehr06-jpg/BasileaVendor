<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
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
        $payload = [
            'customer'    => $customerAsaasId,
            'billingType' => $billingType,
            'value'       => $value,
            'dueDate'     => $dueDate,
            'description' => $description,
        ];

        if ($externalReference) {
            $payload['externalReference'] = $externalReference;
        }
        
        if ($split && !empty($split)) {
            $payload['split'] = $split;
        }

        if ($creditCard && !empty($creditCard)) {
            $payload['creditCard'] = $creditCard;
            if ($creditCardHolderInfo) {
                $payload['creditCardHolderInfo'] = $creditCardHolderInfo;
            }
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Asaas: cobrança criada', [
                'id'     => $data['id'] ?? null,
                'status' => $data['status'] ?? null,
                'value'  => $data['value'] ?? null,
                'split'  => !empty($split) ? 'sim' : 'não',
            ]);
            return $data;
        }

        Log::error('Asaas: erro ao criar cobrança', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao gerar cobrança no Asaas: ' . $response->body());
    }

    public function getPayment(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('[ASAAS_API_GET_PAYMENT_NOT_FOUND] Pagamento não localizado ou erro na API.', [
                'paymentId' => $paymentId,
                'status'    => $response->status(),
                'response'  => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('[ASAAS_API_CONNECTION_ERROR] Erro de conexão ao consultar pagamento.', [
                'paymentId' => $paymentId,
                'error'     => $e->getMessage()
            ]);
        }

        return null;
    }

    public function getPixQrCode(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar QR Code PIX', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function getIdentificationField(string $paymentId): ?string
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/identificationField");

            if ($response->successful()) {
                return $response->json()['identificationField'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar linha digitável', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function getInvoice(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/fiscalInfo");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao consultar nota fiscal', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function refundPayment(string $paymentId, ?float $value = null): array
    {
        $payload = [];
        if ($value && $value > 0) {
            $payload['value'] = $value;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->post("{$this->baseUrl}/payments/{$paymentId}/refund", $payload);

            if ($response->successful()) {
                Log::info('Asaas: cobrança estornada com sucesso', [
                    'payment_id' => $paymentId,
                    'value' => $value,
                    'response' => $response->json(),
                ]);
                return $response->json();
            }

            Log::error('Asaas: falha ao estornar cobrança', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Falha ao estornar no Asaas: ' . $response->body());
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Falha ao estornar')) {
                throw $e;
            }
            Log::error('Asaas: erro de conexão ao estornar', ['error' => $e->getMessage()]);
            throw new \Exception('Erro de conexão com Asaas: ' . $e->getMessage());
        }
    }

    public function cancelPayment(string $paymentId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                Log::info('Asaas: cobrança cancelada', ['paymentId' => $paymentId]);
                return true;
            }

            Log::warning('Asaas: falha ao cancelar cobrança', [
                'paymentId' => $paymentId,
                'response'  => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao cancelar cobrança', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function deletePayment(string $paymentId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                Log::info('Asaas: cobrança excluída definitivamente', ['paymentId' => $paymentId]);
                return true;
            }

            Log::warning('Asaas: falha ao excluir cobrança', [
                'paymentId' => $paymentId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            
            if ($response->status() === 400 && str_contains($response->body(), 'already')) {
                Log::info('Asaas: cobrança já estava cancelada, continuando', ['paymentId' => $paymentId]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao excluir cobrança', [
                'paymentId' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function createPaymentLink(array $data): array
    {
        $payload = [
            'name'                => $data['name'],
            'billingType'         => $data['billingType'] ?? 'UNDEFINED',
            'chargeType'          => $data['chargeType'] ?? 'DETACHED',
            'description'         => $data['description'] ?? null,
            'value'               => $data['value'] ?? null,
            'dueDateLimitDays'    => $data['dueDateLimitDays'] ?? null,
            'notificationEnabled' => (bool) ($data['notificationEnabled'] ?? true),
            'maxAllowedUsage'     => $data['maxAllowedUsage'] ?? null,
            'endDate'             => $data['endDate'] ?? null,
            'maxInstallmentCount' => $data['maxInstallmentCount'] ?? null,
        ];

        if (isset($data['isAddressRequired'])) {
            $payload['isAddressRequired'] = (bool) $data['isAddressRequired'];
        }

        if (!empty($data['redirectUrl'])) {
            $payload['redirectUrl'] = $data['redirectUrl'];
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/paymentLinks", $payload);

        if ($response->successful()) {
            $result = $response->json();
            Log::info('Asaas: link de pagamento criado', ['id' => $result['id'] ?? null, 'url' => $result['url'] ?? null]);
            return $result;
        }

        Log::error('Asaas: erro ao criar link de pagamento', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao gerar link no Asaas: ' . $response->body());
    }

    public function deletePaymentLink(string $id): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->baseUrl}/paymentLinks/{$id}");

            if ($response->successful() || $response->status() === 404) {
                Log::info('Asaas: link de pagamento excluído/arquivado (ou já não existia)', ['id' => $id]);
                return true;
            }

            Log::warning('Asaas: falha crítica ao excluir link de pagamento', [
                'id' => $id,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Asaas: exceção ao excluir link', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getPaymentsByCustomer(
        string $customerId,
        ?\Carbon\Carbon $startDate = null,
        ?\Carbon\Carbon $endDate = null,
        ?string $status = null
    ): array {
        $allPayments = [];
        $offset = 0;
        $limit = 100;
        $maxPages = 10; 

        try {
            for ($page = 0; $page < $maxPages; $page++) {
                $params = [
                    'customer' => $customerId,
                    'offset'   => $offset,
                    'limit'    => $limit,
                ];

                if ($startDate) {
                    $params['dueDate[ge]'] = $startDate->format('Y-m-d');
                }
                if ($endDate) {
                    $params['dueDate[le]'] = $endDate->format('Y-m-d');
                }
                if ($status) {
                    $params['status'] = $status;
                }

                $response = Http::withHeaders($this->headers())
                    ->get("{$this->baseUrl}/payments", $params);

                if (!$response->successful()) {
                    Log::warning('Asaas: falha ao listar cobranças do cliente', [
                        'customer_id' => $customerId,
                        'status'      => $response->status(),
                        'response'    => $response->body(),
                    ]);
                    break;
                }

                $data = $response->json();
                $payments = $data['data'] ?? [];
                $allPayments = array_merge($allPayments, $payments);

                $hasMore = $data['hasMore'] ?? false;
                if (!$hasMore || empty($payments)) {
                    break;
                }

                $offset += $limit;
            }
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao listar cobranças do cliente', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);
        }

        return $allPayments;
    }
}
