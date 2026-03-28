<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Vendedor;

class AsaasService
{
    public string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        // Puxa do banco primeiro, caso não tenha, faz fallback pro env
        $ambiente = \App\Models\Setting::get('asaas_environment', config('services.asaas.ambiente', 'sandbox'));
        
        $this->baseUrl = $ambiente === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';

        $this->apiKey = \App\Models\Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));
    }

    public function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    // ============================================
    // 9.2.1 — Criar cliente no Asaas
    // ============================================
    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/customers", [
                    'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data']) && count($data['data']) > 0) {
                    return $data['data'][0]; // Retorna o primeiro cliente encontrado
                }
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar cliente por CPF/CNPJ', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        // ... (existing code remains as is)
        $existing = $this->findCustomerByCpfCnpj($cpfCnpj);
        if ($existing) {
            // Verifica se o nome bate. Se não, atualiza no Asaas
            if (isset($existing['name']) && $existing['name'] !== $name) {
                Log::info('Asaas: cliente existe com nome diferente, atualizando', [
                    'old_name' => $existing['name'],
                    'new_name' => $name,
                ]);
                try {
                    $updatePayload = ['name' => $name];
                    if ($email) $updatePayload['email'] = $email;
                    if ($phone) $updatePayload['phone'] = preg_replace('/\D/', '', $phone);

                    $response = Http::withHeaders($this->headers())
                        ->put("{$this->baseUrl}/customers/{$existing['id']}", $updatePayload);

                    if ($response->successful()) {
                        return $response->json();
                    }
                } catch (\Exception $e) {
                    Log::warning('Asaas: falha ao atualizar nome do cliente', ['error' => $e->getMessage()]);
                }
            }
            return $existing;
        }

        $payload = [
            'name'    => $name,
            'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
        ];

        if ($phone) $payload['phone'] = preg_replace('/\D/', '', $phone);
        if ($email) $payload['email'] = $email;

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/customers", $payload);

        if ($response->successful()) {
            Log::info('Asaas: cliente criado', ['id' => $response->json()['id'] ?? null, 'name' => $name]);
            return $response->json();
        }

        Log::error('Asaas: erro ao criar cliente', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao registrar cliente no Asaas: ' . $response->body());
    }

    /**
     * MÉTODO DE COMPATIBILIDADE PARA O NOVO CHECKOUT
     * Mapeia os dados do formato array para o createPayment
     */
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
        $dueDate = now()->addDays(3)->format('Y-m-d'); // 3 dias de validade por padrão
        
        // Se for cartão, a descrição pode ser mais específica
        if ($billingType === 'CREDIT_CARD') {
            $dueDate = now()->format('Y-m-d');
        }

        // Se houver split configurado para o vendedor
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

        // Mapeia o retorno para o que o CheckoutController espera
        return [
            'asaas_payment_id'  => $asaasResponse['id'],
            'bank_slip_url'     => $asaasResponse['bankSlipUrl'] ?? null,
            'invoice_url'       => $asaasResponse['invoiceUrl'] ?? null,
            'pix_copia_cola'    => $asaasResponse['pixCopiaCola'] ?? null,
            'pix_qrcode'        => $asaasResponse['pixQrCode'] ?? null, // Base64 se houver
            'cartao_token'      => $asaasResponse['creditCardToken'] ?? null,
            'cartao_bandeira'   => $asaasResponse['creditCard']['creditCardBrand'] ?? null,
            'cartao_final'      => $asaasResponse['creditCard']['creditCardNumber'] ?? null,
        ];
    }

    // ============================================
    // 9.2.2 — Criar cobrança
    // ============================================
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
            'billingType' => $billingType, // BOLETO, CREDIT_CARD, PIX
            'value'       => $value,
            'dueDate'     => $dueDate,
            'description' => $description,
        ];

        if ($externalReference) {
            $payload['externalReference'] = $externalReference;
        }
        
        // Adicionar split se fornecido
        if ($split && !empty($split)) {
            $payload['split'] = $split;
        }

        // Adicionar cartão de crédito se fornecido
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

    // ============================================
    // 9.2.3 — Consultar cobrança
    // ============================================
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

    // ============================================
    // 9.2.4 — Consultar QR Code PIX
    // ============================================
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

    // ============================================
    // 9.2.5 — Consultar linha digitável (boleto)
    // ============================================
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

    // ============================================
    // 9.2.6 — Consultar nota fiscal
    // ============================================
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

    // ============================================
    // Estornar cobrança paga (refund)
    // ============================================
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

    // ============================================
    // 9.2.7 — Cancelar/Excluir cobrança (soft delete)
    // ============================================
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

    // ============================================
    // 9.2.8 — Excluir cobrança definitivamente (hard delete)
    // ============================================
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
            
            // Se a cobrança já foi cancelada, considera sucesso
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

    // ============================================
    // Helper: mapear status do Asaas para status local
    // ============================================
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

    // ============================================
    // Validar Wallet ID
    // ============================================
    public function validateWallet(string $walletId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/wallets/{$walletId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'valid' => true,
                    'wallet' => $data,
                    'message' => 'Wallet validado com sucesso.'
                ];
            }

            return [
                'valid' => false,
                'wallet' => null,
                'message' => 'Wallet não encontrado ou inválido.'
            ];
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao validar wallet', ['walletId' => $walletId, 'error' => $e->getMessage()]);
            return [
                'valid' => false,
                'wallet' => null,
                'message' => 'Erro ao validar wallet: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // Criar Split para cobrança
    // ============================================
    public function buildSplitArray(Vendedor $vendedor, float $valorVenda, string $tipoVenda = 'inicial'): array
    {
        if (!$vendedor->isAptoSplit()) {
            return [];
        }

        $split = [];
        
        if ($vendedor->tipo_split === 'percentual') {
            $percentual = $tipoVenda === 'inicial' 
                ? $vendedor->valor_split_inicial 
                : $vendedor->valor_split_recorrencia;
            
            if ($percentual > 0) {
                $split[] = [
                    'walletId' => $vendedor->asaas_wallet_id,
                    'percentualValue' => $percentual,
                ];
            }
        } else {
            // Valor fixo
            $valorFixo = $tipoVenda === 'inicial'
                ? $vendedor->valor_split_inicial
                : $vendedor->valor_split_recorrencia;
            
            if ($valorFixo > 0 && $valorFixo <= $valorVenda) {
                $split[] = [
                    'walletId' => $vendedor->asaas_wallet_id,
                    'fixedValue' => $valorFixo,
                ];
            }
        }

        return $split;
    }
}
