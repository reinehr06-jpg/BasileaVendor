<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\LegacyCommission;
use App\Models\LegacyCustomerImport;
use App\Models\LegacyCustomerPayment;
use App\Models\Setting;
use App\Models\Vendedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    public function importCustomerByCpfCnpj(string $cpfCnpj, ?int $vendedorId = null, ?int $gestorId = null, ?int $planoId = null): LegacyCustomerImport
    {
        $cpfCnpjClean = preg_replace('/\D/', '', $cpfCnpj);

        $import = LegacyCustomerImport::create([
            'local_cliente_cpf_cnpj' => $cpfCnpjClean,
            'import_status' => 'PROCESSING',
            'vendedor_id' => $vendedorId,
            'gestor_id' => $gestorId,
            'plano_id' => $planoId,
        ]);

        try {
            $asaasCustomer = $this->asaasService->findCustomerByCpfCnpj($cpfCnpjClean);

            if (! $asaasCustomer) {
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
                'nome' => $customerData['name'] ?? null,
                'documento' => $customerData['cpfCnpj'] ?? $cpfCnpjClean,
                'email' => $customerData['email'] ?? null,
                'telefone' => $customerData['phone'] ?? null,
                'customer_status' => $this->mapCustomerStatus($customerData['status'] ?? null),
            ]);

            $this->importPayments($import, $asaasCustomerId);
            $this->importSubscriptions($import, $asaasCustomerId);

            $import->update([
                'import_status' => 'IMPORTED',
                'last_sync_at' => now(),
                'notes' => 'Importação concluída com sucesso',
            ]);

        } catch (\Exception $e) {
            Log::error('LegacyImport: erro ao importar cliente', [
                'cpf_cnpj' => $cpfCnpjClean,
                'error' => $e->getMessage(),
            ]);
            $import->update([
                'import_status' => 'ERROR',
                'notes' => 'Erro: '.$e->getMessage(),
            ]);
        }

        return $import;
    }

    protected function importPayments(LegacyCustomerImport $import, string $asaasCustomerId): void
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments", [
                    'customer' => $asaasCustomerId,
                    'limit' => 100,
                ]);

            if ($response->successful()) {
                $paymentsData = $response->json()['data'] ?? [];

                foreach ($paymentsData as $payment) {
                    $paidAt = null;
                    if (! empty($payment['paymentDate'])) {
                        $paidAt = Carbon::parse($payment['paymentDate'])->format('Y-m-d');
                    }

                    $isRecurring = ! empty($payment['subscription']);

                    LegacyCustomerPayment::updateOrCreate(
                        [
                            'legacy_import_id' => $import->id,
                            'asaas_payment_id' => $payment['id'],
                        ],
                        [
                            'asaas_subscription_id' => $payment['subscription'] ?? null,
                            'billing_type' => $this->mapBillingType($payment['billingType'] ?? null),
                            'value' => $payment['value'] ?? 0,
                            'due_date' => ! empty($payment['dueDate']) ? Carbon::parse($payment['dueDate'])->format('Y-m-d') : null,
                            'paid_at' => $paidAt,
                            'status' => $payment['status'] ?? null,
                            'description' => $payment['description'] ?? null,
                            'is_recurring' => $isRecurring,
                            'reference_month' => ! empty($payment['dueDate'])
                                ? Carbon::parse($payment['dueDate'])->format('Y-m')
                                : null,
                            'raw_payload' => $payment,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::warning('LegacyImport: erro ao importar pagamentos', [
                'asaas_customer_id' => $asaasCustomerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function importSubscriptions(LegacyCustomerImport $import, string $asaasCustomerId): void
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/subscriptions", [
                    'customer' => $asaasCustomerId,
                    'limit' => 10,
                ]);

            if ($response->successful()) {
                $subscriptionsData = $response->json()['data'] ?? [];

                if (! empty($subscriptionsData)) {
                    $subscription = $subscriptionsData[0];

                    $import->update([
                        'subscription_status' => $this->mapSubscriptionStatus($subscription['status'] ?? null),
                        'plano_valor_recorrente' => $subscription['value'] ?? ($subscription['billingType'] === 'RECURRING' ? ($subscription['amount'] ?? 0) : 0),
                    ]);
                } else {
                    $import->update([
                        'subscription_status' => 'NONE',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('LegacyImport: erro ao importar assinaturas', [
                'asaas_customer_id' => $asaasCustomerId,
                'error' => $e->getMessage(),
            ]);
            $import->update([
                'subscription_status' => 'NONE',
            ]);
        }
    }

    public function importAllFromLocalDatabase(?int $vendedorId = null): array
    {
        $query = Cliente::whereNotNull('documento')
            ->where('documento', '!=', '')
            ->whereHas('vendas');

        if ($vendedorId) {
            $query->whereHas('vendas', function ($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        $clientes = $query->get();

        $stats = [
            'total' => $clientes->count(),
            'imported' => 0,
            'not_found' => 0,
            'error' => 0,
            'conflict' => 0,
        ];

        foreach ($clientes as $cliente) {
            $vendedorId = $cliente->vendas->first()?->vendedor_id;
            $gestorId = null;
            $planoId = $cliente->vendas->first()?->plano_id;

            if ($vendedorId) {
                $vendedor = Vendedor::find($vendedorId);
                $gestorId = $vendedor?->gestor_id;
            }

            $import = $this->importCustomerByCpfCnpj(
                $cliente->documento,
                $vendedorId,
                $gestorId,
                $planoId
            );

            if ($import->local_cliente_id !== $cliente->id) {
                $import->update(['local_cliente_id' => $cliente->id]);
            }

            match ($import->import_status) {
                'IMPORTED' => $stats['imported']++,
                'NOT_FOUND' => $stats['not_found']++,
                'CONFLICT' => $stats['conflict']++,
                'ERROR' => $stats['error']++,
                default => null,
            };
        }

        return $stats;
    }

    public function syncFromAsaas(LegacyCustomerImport $import): void
    {
        if (! $import->asaas_customer_id) {
            return;
        }

        $import->update(['import_status' => 'PROCESSING']);

        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/customers/{$import->asaas_customer_id}");

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

            $import->update([
                'import_status' => 'IMPORTED',
                'last_sync_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('LegacyImport: erro ao sincronizar cliente', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);
            $import->update([
                'import_status' => 'ERROR',
                'notes' => 'Erro na sincronização: '.$e->getMessage(),
            ]);
        }
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
            'UNDEFINED' => 'UNDEFINED',
            default => 'UNDEFINED',
        };
    }

    public function generateCommissions(LegacyCustomerImport $import): array
    {
        $results = [
            'old_sale' => 0,
            'recurring' => 0,
            'errors' => [],
        ];

        if (! $import->hasValidCommercialLink()) {
            $results['errors'][] = 'Vínculo comercial inválido (vendedor ou plano não definido)';

            return $results;
        }

        $vendedor = $import->vendedor;
        if (! $vendedor) {
            $results['errors'][] = 'Vendedor não encontrado';

            return $results;
        }

        if ($import->generate_old_sale_commission && $import->plano_valor_original) {
            $existsOldSale = LegacyCommission::where('legacy_import_id', $import->id)
                ->where('commission_type', 'OLD_SALE')
                ->exists();

            if (! $existsOldSale) {
                $percentual = $vendedor->comissao_inicial ?? $vendedor->comissao ?? 10;
                $comissao = ($import->plano_valor_original * $percentual) / 100;

                $gestorComissao = 0;
                if ($import->gestor_id && $vendedor->comissao_gestor_primeira) {
                    $gestorComissao = ($import->plano_valor_original * $vendedor->comissao_gestor_primeira) / 100;
                }

                LegacyCommission::create([
                    'legacy_import_id' => $import->id,
                    'vendedor_id' => $import->vendedor_id,
                    'gestor_id' => $import->gestor_id,
                    'cliente_id' => $import->local_cliente_id,
                    'commission_type' => 'OLD_SALE',
                    'reference_month' => $import->data_venda_original?->format('Y-m') ?? now()->format('Y-m'),
                    'base_amount' => $import->plano_valor_original,
                    'seller_commission_amount' => $comissao,
                    'gestor_commission_amount' => $gestorComissao,
                    'status' => 'GENERATED',
                    'generated_at' => now(),
                    'source' => 'LEGACY_IMPORT',
                ]);

                $results['old_sale']++;
            }
        }

        if ($import->generate_recurring_commission) {
            $currentMonth = now()->format('Y-m');

            $existsRecurring = LegacyCommission::where('legacy_import_id', $import->id)
                ->where('commission_type', 'RECURRING')
                ->where('reference_month', $currentMonth)
                ->exists();

            if (! $existsRecurring && $import->subscription_status === 'ACTIVE') {
                $paidThisMonth = $import->payments()
                    ->where('reference_month', $currentMonth)
                    ->where('status', 'RECEIVED')
                    ->exists();

                if ($paidThisMonth && $import->plano_valor_recorrente) {
                    $percentual = $vendedor->comissao_recorrencia ?? $vendedor->comissao ?? 10;
                    $comissao = ($import->plano_valor_recorrente * $percentual) / 100;

                    $gestorComissao = 0;
                    if ($import->gestor_id && $vendedor->comissao_gestor_recorrencia) {
                        $gestorComissao = ($import->plano_valor_recorrente * $vendedor->comissao_gestor_recorrencia) / 100;
                    }

                    $payment = $import->payments()
                        ->where('reference_month', $currentMonth)
                        ->where('status', 'RECEIVED')
                        ->first();

                    LegacyCommission::create([
                        'legacy_import_id' => $import->id,
                        'legacy_payment_id' => $payment?->id,
                        'vendedor_id' => $import->vendedor_id,
                        'gestor_id' => $import->gestor_id,
                        'cliente_id' => $import->local_cliente_id,
                        'commission_type' => 'RECURRING',
                        'reference_month' => $currentMonth,
                        'base_amount' => $import->plano_valor_recorrente,
                        'seller_commission_amount' => $comissao,
                        'gestor_commission_amount' => $gestorComissao,
                        'status' => 'GENERATED',
                        'generated_at' => now(),
                        'asaas_reference_id' => $payment?->asaas_payment_id,
                        'source' => 'LEGACY_IMPORT',
                    ]);

                    $results['recurring']++;
                }
            }
        }

        return $results;
    }
}
