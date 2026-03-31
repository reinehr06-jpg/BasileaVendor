<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\LegacyCustomerImport;
use App\Services\LegacyImportService;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PullAllAsaasCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $baseUrl;
    protected string $apiKey;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $ambiente = Setting::get('asaas_environment', config('services.asaas.ambiente', 'sandbox'));
        $this->baseUrl = $ambiente === 'production' ? 'https://api.asaas.com/v3' : 'https://sandbox.asaas.com/api/v3';
        $this->apiKey = Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $limit = 100;
        $offset = 0;
        $totalPulled = 0;
        $totalCreated = 0;

        Log::info('[PullAllAsaasCustomersJob] Global sync started');

        try {
            do {
                $response = Http::withHeaders([
                    'access_token' => $this->apiKey,
                ])->get("{$this->baseUrl}/customers", [
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                if (!$response->successful()) {
                    Log::error('[PullAllAsaasCustomersJob] Error calling Asaas API', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    break;
                }

                $data = $response->json();
                $customers = $data['data'] ?? [];
                
                foreach ($customers as $customer) {
                    $totalPulled++;
                    
                    $import = LegacyCustomerImport::where('asaas_customer_id', $customer['id'])
                        ->orWhere('documento', preg_replace('/\D/', '', $customer['cpfCnpj'] ?? ''))
                        ->first();

                    if (!$import) {
                        $import = LegacyCustomerImport::create([
                            'asaas_customer_id' => $customer['id'],
                            'asaas_customer_data' => $customer,
                            'nome' => $customer['name'],
                            'documento' => preg_replace('/\D/', '', $customer['cpfCnpj'] ?? ''),
                            'email' => $customer['email'] ?? null,
                            'telefone' => $customer['phone'] ?? null,
                            'customer_status' => $this->mapCustomerStatus($customer['status'] ?? null),
                            'import_status' => 'PENDING',
                            'notes' => 'Descoberto na sincronização global'
                        ]);
                        $totalCreated++;
                    }

                    // Se não estiver migrado (link comercial incompleto), forçamos o Job de descoberta
                    if (!$import->hasValidCommercialLink()) {
                        ImportLegacyCustomerJob::dispatch($import->id);
                    }
                }

                $offset += $limit;
                $hasMore = !empty($data['hasMore']);

            } while ($hasMore);

            Log::info("[PullAllAsaasCustomersJob] Sync COMPLETED. Discovered: {$totalPulled}, Created: {$totalCreated}");

        } catch (\Exception $e) {
            Log::error('[PullAllAsaasCustomersJob] Critical failure during sync', [
                'error' => $e->getMessage()
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
}
