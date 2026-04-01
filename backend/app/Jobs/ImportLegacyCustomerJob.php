<?php

namespace App\Jobs;

use App\Models\LegacyCustomerImport;
use App\Services\LegacyImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportLegacyCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $importId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importId)
    {
        $this->importId = $importId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $import = LegacyCustomerImport::find($this->importId);

        if (!$import) {
            Log::warning('[ImportLegacyCustomerJob] Import ID not found', ['import_id' => $this->importId]);
            return;
        }

        try {
            Log::info('[ImportLegacyCustomerJob] Starting sync for legacy customer', [
                'import_id' => $import->id,
                'nome' => $import->nome,
                'asaas_id' => $import->asaas_customer_id
            ]);

            $service = new LegacyImportService();
            $service->syncFromAsaas($import);

            Log::info('[ImportLegacyCustomerJob] Sync completed successfully', ['import_id' => $import->id]);
        } catch (\Exception $e) {
            Log::error('[ImportLegacyCustomerJob] Error syncing legacy customer', [
                'import_id' => $import->id,
                'error' => $e->getMessage()
            ]);
            
            $import->update([
                'import_status' => 'ERROR',
                'notes' => 'Erro no processamento background: ' . $e->getMessage()
            ]);
        }
    }
}
