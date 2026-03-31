<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\LegacyCommission;
use App\Models\LegacyCustomerImport;
use App\Models\LegacyCustomerPayment;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Models\Plano;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LegacyImportService
{
    protected AsaasService $asaasService;
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->asaasService = new AsaasService;
        $ambiente = Setting::get('asaas_environment', config('services.asaas.ambiente', 'sandbox'));

        $this->baseUrl = $ambiente === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';

        $this->apiKey = Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Importa um cliente específico pelo CPF/CNPJ.
     */
    public function importCustomerByCpfCnpj(string $cpfCnpj, ?int $vendedorId = null, ?int $gestorId = null, ?int $planoId = null): LegacyCustomerImport
    {
        $cpfCnpjClean = preg_replace('/\D/', '', $cpfCnpj);

        $import = LegacyCustomerImport::where('documento', $cpfCnpjClean)->first();
        
        if (!$import) {
            $import = LegacyCustomerImport::create([
                'local_cliente_cpf_cnpj' => $cpfCnpjClean,
                'import_status' => 'PROCESSING',
                'vendedor_id' => $vendedorId,
                'gestor_id' => $gestorId,
                'plano_id' => $planoId,
                'documento' => $cpfCnpjClean
            ]);
        }

        try {
            $asaasCustomer = $this->asaasService->findCustomerByCpfCnpj($cpfCnpjClean);

            if (!$asaasCustomer) {
                $import->update([
                    'import_status' => 'NOT_FOUND',
                    'notes' => 'Cliente não encontrado no Asaas',
                ]);
                return $import;
            }

            $customerData = $asaasCustomer;
            $asaasCustomerId = $customerData['id'] ?? null;

            $import->update([
                'asaas_customer_id' => $asaasCustomerId,
                'asaas_customer_data' => $customerData,
                'nome' => $customerData['name'] ?? $import->nome,
                'email' => $customerData['email'] ?? $import->email,
                'telefone' => $customerData['phone'] ?? $import->telefone,
                'customer_status' => $this->mapCustomerStatus($customerData['status'] ?? null),
            ]);

            $this->importPayments($import, $asaasCustomerId);
            $this->importSubscriptions($import, $asaasCustomerId);
            
            // Automatic seller and plan discovery
            $this->discoverSeller($import);
            $this->discoverPlan($import);
            
            // Mirror logic if seller and plan are assigned (discovered or manual)
            if ($import->hasValidCommercialLink()) {
                $this->mirrorToLocalTables($import);
                $this->generateCommissions($import);
            }

            $import->update([
                'import_status' => 'IMPORTED',
                'last_sync_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('LegacyImport: erro ao importar cliente', [
                'cpf_cnpj' => $cpfCnpjClean,
                'error' => $e->getMessage(),
            ]);
            $import->update(['import_status' => 'ERROR', 'notes' => 'Erro: '.$e->getMessage()]);
        }

        return $import;
    }

    /**
     * Importa TODOS os pagamentos do cliente no Asaas com paginação.
     */
    protected function importPayments(LegacyCustomerImport $import, string $asaasCustomerId): void
    {
        try {
            $offset = 0;
            $limit = 100;
            $hasMore = true;

            while ($hasMore) {
                $response = Http::withHeaders($this->headers())
                    ->get("{$this->baseUrl}/payments", [
                        'customer' => $asaasCustomerId,
                        'limit' => $limit,
                        'offset' => $offset
                    ]);

                if (!$response->successful()) break;

                $data = $response->json();
                $paymentsData = $data['data'] ?? [];

                foreach ($paymentsData as $payment) {
                    $paidAt = !empty($payment['paymentDate']) ? Carbon::parse($payment['paymentDate'])->format('Y-m-d') : null;
                    $isRecurring = !empty($payment['subscription']);

                    // Identificar método amigável (Cartão 12x, etc)
                    $metodo = $this->mapBillingType($payment['billingType'] ?? null);
                    if ($metodo === 'CREDIT_CARD' && !empty($payment['installmentNumber'])) {
                        $metodo = "Cartão " . ($payment['installmentNumber'] ?? '1') . "/" . ($payment['totalInstallments'] ?? '1');
                    }

                    LegacyCustomerPayment::updateOrCreate(
                        ['legacy_import_id' => $import->id, 'asaas_payment_id' => $payment['id']],
                        [
                            'asaas_subscription_id' => $payment['subscription'] ?? null,
                            'asaas_installment_id' => $payment['installment'] ?? null,
                            'installment_number' => $payment['installmentNumber'] ?? null,
                            'total_installments' => $payment['totalInstallments'] ?? null,
                            'billing_type' => $this->mapBillingType($payment['billingType'] ?? null),
                            'payment_method' => $metodo,
                            'value' => $payment['value'] ?? 0,
                            'due_date' => !empty($payment['dueDate']) ? Carbon::parse($payment['dueDate'])->format('Y-m-d') : null,
                            'paid_at' => $paidAt,
                            'status' => $payment['status'] ?? null,
                            'description' => $payment['description'] ?? null,
                            'is_recurring' => $isRecurring,
                            'reference_month' => !empty($payment['dueDate']) ? Carbon::parse($payment['dueDate'])->format('Y-m') : null,
                            'raw_payload' => $payment,
                        ]
                    );
                }

                $offset += $limit;
                $hasMore = !empty($data['hasMore']);
            }
        } catch (\Exception $e) {
            Log::warning('LegacyImport: erro ao importar pagamentos pagination', ['customer' => $asaasCustomerId, 'error' => $e->getMessage()]);
        }
    }

    protected function importSubscriptions(LegacyCustomerImport $import, string $asaasCustomerId): void
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/subscriptions", ['customer' => $asaasCustomerId, 'limit' => 10]);

            if ($response->successful()) {
                $subscriptionsData = $response->json()['data'] ?? [];
                if (!empty($subscriptionsData)) {
                    // Ordenar por status (ACTIVE primeiro) e pegar a mais relevante
                    $subscription = collect($subscriptionsData)
                        ->sortBy(fn($s) => $s['status'] === 'ACTIVE' ? 0 : 1)
                        ->first();

                    $import->update([
                        'subscription_status' => $this->mapSubscriptionStatus($subscription['status'] ?? null),
                        'plano_valor_recorrente' => $subscription['value'] ?? ($subscription['billingType'] === 'RECURRING' ? ($subscription['amount'] ?? 0) : 0),
                        'notes' => ($import->notes ? $import->notes . " | " : "") . "Assinatura: " . ($subscription['description'] ?? 'Sem descrição')
                    ]);
                } else {
                    $import->update(['subscription_status' => 'NONE']);
                }
            }
        } catch (\Exception $e) {
            Log::warning('LegacyImport: erro ao importar assinaturas', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Sincroniza dados do Asaas para o modelo de Importação.
     */
    public function syncFromAsaas(LegacyCustomerImport $import): void
    {
        if (!$import->asaas_customer_id) return;

        $import->update(['import_status' => 'PROCESSING']);

        try {
            $response = Http::withHeaders($this->headers())->get("{$this->baseUrl}/customers/{$import->asaas_customer_id}");
            if ($response->successful()) {
                $customerData = $response->json();
                $import->update([
                    'asaas_customer_data' => $customerData,
                    'nome' => $customerData['name'] ?? $import->nome,
                    'email' => $customerData['email'] ?? $import->email,
                    'telefone' => $customerData['phone'] ?? $import->telefone,
                    'customer_status' => $this->mapCustomerStatus($customerData['status'] ?? null),
                ]);
            }

            $this->importPayments($import, $import->asaas_customer_id);
            $this->importSubscriptions($import, $import->asaas_customer_id);

            // Automated discovery after data pull
            $this->discoverSeller($import);
            $this->discoverPlan($import);

            // Se tiver vendedor e plano, espelha para as tabelas principais
            if ($import->hasValidCommercialLink()) {
                $this->mirrorToLocalTables($import);
                $this->generateCommissions($import);
            }

            $import->update(['import_status' => 'IMPORTED', 'last_sync_at' => now()]);
        } catch (\Exception $e) {
            Log::error('LegacyImport: sync error', ['id' => $import->id, 'error' => $e->getMessage()]);
            $import->update(['import_status' => 'ERROR']);
        }
    }

    /**
     * ESPELHAMENTO: Cria/Atualiza Cliente, Venda e Pagamentos locais.
     */
    public function mirrorToLocalTables(LegacyCustomerImport $import): void
    {
        DB::transaction(function () use ($import) {
            // 1. Mirror Cliente
            $cliente = Cliente::updateOrCreate(
                ['documento' => $import->documento],
                [
                    'nome' => $import->nome,
                    'email' => $import->email,
                    'telefone' => $import->telefone,
                    'status' => mb_strtolower($import->customer_status),
                    'asaas_customer_id' => $import->asaas_customer_id
                ]
            );

            $import->update(['local_cliente_id' => $cliente->id]);

            // 2. Mirror Venda
            $plano = Plano::find($import->plano_id);
            $venda = Venda::updateOrCreate(
                ['cliente_id' => $cliente->id, 'origem' => 'legado'],
                [
                    'vendedor_id' => $import->vendedor_id,
                    'plano_id' => $import->plano_id,
                    'plano' => $plano?->nome ?? 'Plano Legado',
                    'valor' => $import->plano_valor_recorrente ?? $import->plano_valor_original ?? 0,
                    'valor_final' => $import->plano_valor_recorrente ?? $import->plano_valor_original ?? 0,
                    'status' => 'PAGO',
                    'data_venda' => $import->data_venda_original ?? now(),
                    'asaas_subscription_id' => $import->payments()->orderByDesc('created_at')->first()?->asaas_subscription_id,
                ]
            );

            // 3. Mirror Pagamentos (Todos, incluindo pendentes e vencidos)
            foreach ($import->payments()->get() as $legacyPayment) {
                Pagamento::updateOrCreate(
                    ['venda_id' => $venda->id, 'asaas_id' => $legacyPayment->asaas_payment_id],
                    [
                        'valor' => $legacyPayment->value,
                        'data_vencimento' => $legacyPayment->due_date,
                        'data_pagamento' => $legacyPayment->paid_at,
                        'status' => $legacyPayment->status,
                        'forma_pagamento' => $legacyPayment->billing_type,
                        'forma_pagamento_real' => $legacyPayment->payment_method
                    ]
                );
            }
            
            // Atualizar status do cliente via service
            ClienteStatusService::atualizarCliente($cliente);
        });
    }

    /**
     * Tenta descobrir o vendedor através dos dados de split ou observações.
     */
    public function discoverSeller(LegacyCustomerImport $import): void
    {
        if ($import->vendedor_id) return; // Já tem

        // 1. Tentar por Wallet ID nos splits de TODOS os pagamentos
        $payments = $import->payments()->whereNotNull('raw_payload')->get();
        
        foreach ($payments as $p) {
            if (!empty($p->raw_payload['split'])) {
                foreach ($p->raw_payload['split'] as $split) {
                    $vendedor = Vendedor::where('asaas_wallet_id', $split['walletId'])->first();
                    if ($vendedor) {
                        $import->update([
                            'vendedor_id' => $vendedor->id,
                            'gestor_id' => $vendedor->gestor_id,
                            'notes' => ($import->notes ? $import->notes . " | " : "") . "Vendedor descoberto via Split ID: " . $vendedor->user->name
                        ]);
                        return;
                    }
                }
            }
        }

        // 2. Tentar por nome/email nas observações do cliente
        $obs = mb_strtolower($import->asaas_customer_data['observations'] ?? '', 'UTF-8');
        if (!empty($obs)) {
            $vendedores = Vendedor::with('user')->get();
            foreach ($vendedores as $v) {
                $nome = mb_strtolower($v->user->name ?? '', 'UTF-8');
                $email = mb_strtolower($v->user->email ?? '', 'UTF-8');
                
                if (str_contains($obs, $nome) || (!empty($email) && str_contains($obs, $email))) {
                    $import->update([
                        'vendedor_id' => $v->id,
                        'gestor_id' => $v->gestor_id,
                        'notes' => ($import->notes ? $import->notes . " | " : "") . "Vendedor descoberto via Observações: " . $v->user->name
                    ]);
                    return;
                }
            }
        }
    }

    /**
     * Tenta descobrir o plano baseado no valor da assinatura.
     */
    public function discoverPlan(LegacyCustomerImport $import): void
    {
        if ($import->plano_id) return; // Já tem

        $valor = $import->plano_valor_recorrente ?? $import->plano_valor_original ?? 0;
        if ($valor <= 0) return;

        // Tentar match exato nos planos configurados
        $plano = Plano::where('valor_mensal', $valor)
            ->orWhere('valor_anual', $valor)
            ->first();

        if ($plano) {
            $import->update([
                'plano_id' => $plano->id,
                'notes' => ($import->notes ? $import->notes . " | " : "") . "Plano identificado por valor: " . $plano->nome
            ]);
            return;
        }

        // Tentar por descrição da nota/assinatura se houver
        $notes = mb_strtolower($import->notes ?? '', 'UTF-8');
        $planos = Plano::all();
        foreach ($planos as $p) {
            if (str_contains($notes, mb_strtolower($p->nome, 'UTF-8'))) {
                $import->update([
                    'plano_id' => $p->id,
                    'notes' => ($import->notes ? $import->notes . " | " : "") . "Plano identificado por descrição: " . $p->nome
                ]);
                return;
            }
        }
    }

    public function generateCommissions(LegacyCustomerImport $import, ?string $month = null): array
    {
        $results = ['old_sale' => 0, 'recurring' => 0, 'errors' => []];
        if (!$import->vendedor_id) return $results;

        $vendedor = $import->vendedor;
        $targetMonth = $month ?? '2026-03';

        // Buscar pagamento correspondente ao mês alvo
        $payment = $import->payments()
            ->where('reference_month', $targetMonth)
            ->whereIn('status', ['RECEIVED', 'CONFIRMED'])
            ->first();

        if ($payment) {
            $exists = LegacyCommission::where('legacy_import_id', $import->id)
                ->where('reference_month', $targetMonth)
                ->exists();

            if (!$exists) {
                // Se for a primeira parcela de um parcelamento ou uma venda única nova
                $isInitial = ($payment->installment_number == 1) || (!$payment->is_recurring && !$payment->asaas_installment_id);
                
                $percentual = $isInitial 
                    ? ($vendedor->comissao_inicial ?? $vendedor->comissao ?? 10)
                    : ($vendedor->comissao_recorrencia ?? $vendedor->comissao ?? 10);

                $comissao = ($payment->value * $percentual) / 100;

                LegacyCommission::create([
                    'legacy_import_id' => $import->id,
                    'legacy_payment_id' => $payment->id,
                    'vendedor_id' => $import->vendedor_id,
                    'gestor_id' => $import->gestor_id,
                    'cliente_id' => $import->local_cliente_id,
                    'commission_type' => $isInitial ? 'OLD_SALE' : 'RECURRING',
                    'reference_month' => $targetMonth,
                    'base_amount' => $payment->value,
                    'seller_commission_amount' => $comissao,
                    'status' => 'GENERATED',
                    'generated_at' => now(),
                    'source' => 'LEGACY_IMPORT',
                ]);
                $results['recurring']++;
            }
        }

        return $results;
    }

    protected function mapCustomerStatus(?string $status): string
    {
        return match ($status) {
            'ACTIVE' => 'ACTIVE',
            'INACTIVE' => 'INACTIVE',
            'DEFAULT' => 'OVERDUE',
            'DELETED' => 'CANCELLED',
            default => 'NONE',
        };
    }

    protected function mapSubscriptionStatus(?string $status): string
    {
        return match ($status) {
            'ACTIVE' => 'ACTIVE',
            'INACTIVE' => 'INACTIVE',
            'CANCELLED' => 'CANCELLED',
            'PAST_DUE' => 'OVERDUE',
            default => 'NONE',
        };
    }

    protected function mapBillingType(?string $type): string
    {
        return match ($type) {
            'PIX' => 'PIX',
            'BOLETO' => 'BOLETO',
            'CREDIT_CARD' => 'CREDIT_CARD',
            default => 'UNDEFINED',
        };
    }
}
