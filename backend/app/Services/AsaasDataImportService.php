<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AsaasCustomer;
use App\Models\AsaasSubscription;
use App\Models\AsaasPayment;
use Carbon\Carbon;

class AsaasDataImportService
{
    protected AsaasService $asaasService;

    public function __construct()
    {
        $this->asaasService = new AsaasService();
    }

    /**
     * Importa todos os clientes do Asaas (paginado)
     */
    public function importCustomers()
    {
        $limit = 100;
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withHeaders($this->asaasService->headers())
                ->get("{$this->asaasService->baseUrl}/customers", [
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $customers = $data['data'] ?? [];

                foreach ($customers as $cust) {
                    AsaasCustomer::updateOrCreate(
                        ['asaas_customer_id' => $cust['id']],
                        [
                            'financial_status' => $cust['financialStatus'] ?? null,
                            'asaas_raw_data'   => $cust,
                        ]
                    );
                }

                $hasMore = $data['hasMore'] ?? false;
                $offset += $limit;
            } else {
                Log::error('AsaasDataImportService: Falha ao importar customers', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }
        }
    }

    /**
     * Importa todas as assinaturas do Asaas (paginado)
     */
    public function importSubscriptions()
    {
        $limit = 100;
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withHeaders($this->asaasService->headers())
                ->get("{$this->asaasService->baseUrl}/subscriptions", [
                    'limit' => $limit,
                    'offset' => $offset,
                    'includeDeleted' => 'true'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $subscriptions = $data['data'] ?? [];

                foreach ($subscriptions as $sub) {
                    AsaasSubscription::updateOrCreate(
                        ['asaas_subscription_id' => $sub['id']],
                        [
                            'asaas_customer_id' => $sub['customer'] ?? null,
                            'status' => $sub['status'] ?? null,
                            'next_due_date' => $sub['nextDueDate'] ?? null,
                            'deleted' => $sub['deleted'] ?? false,
                            'asaas_raw_data' => $sub,
                        ]
                    );
                }

                $hasMore = $data['hasMore'] ?? false;
                $offset += $limit;
            } else {
                Log::error('AsaasDataImportService: Falha ao importar subscriptions', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }
        }
    }

    /**
     * Importa todos os pagamentos do Asaas (paginado)
     */
    public function importPayments(?string $dateCreatedGe = null)
    {
        $limit = 100;
        $offset = 0;
        $hasMore = true;

        while ($hasMore) {
            $params = [
                'limit' => $limit,
                'offset' => $offset,
            ];

            if ($dateCreatedGe) {
                $params['dateCreated[ge]'] = $dateCreatedGe;
            }

            $response = Http::withHeaders($this->asaasService->headers())
                ->get("{$this->asaasService->baseUrl}/payments", $params);

            if ($response->successful()) {
                $data = $response->json();
                $payments = $data['data'] ?? [];

                foreach ($payments as $pay) {
                    AsaasPayment::updateOrCreate(
                        ['asaas_payment_id' => $pay['id']],
                        [
                            'asaas_customer_id' => $pay['customer'] ?? null,
                            'asaas_subscription_id' => $pay['subscription'] ?? null,
                            'status' => $pay['status'] ?? null,
                            'due_date' => $pay['dueDate'] ?? null,
                            'payment_date' => $pay['paymentDate'] ?? null,
                            'client_payment_date' => $pay['clientPaymentDate'] ?? null,
                            'confirmed_date' => $pay['confirmedDate'] ?? null,
                            'deleted' => $pay['deleted'] ?? false,
                            'refunded' => $pay['refunded'] ?? false,
                            'asaas_raw_data' => $pay,
                        ]
                    );
                }

                $hasMore = $data['hasMore'] ?? false;
                $offset += $limit;
            } else {
                Log::error('AsaasDataImportService: Falha ao importar payments', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }
        }
    }
}
