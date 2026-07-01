<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AsaasDataImportService;
use App\Models\AsaasPayment;
use App\Models\Cliente;
use App\Services\ClienteStatusService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyAsaasReconciliationCommand extends Command
{
    protected $signature = 'asaas:reconcile';
    protected $description = 'Reconciliação diária de dados do Asaas das últimas 24h';

    public function handle()
    {
        $this->info('Iniciando reconciliação diária com Asaas...');

        $importer = new AsaasDataImportService();
        $dateGe = Carbon::now()->subHours(24)->format('Y-m-d');
        
        $this->info("Importando pagamentos a partir de {$dateGe}...");
        $importer->importPayments($dateGe);

        // Clientes afetados nas últimas 24h para re-cálculo
        $customerIds = AsaasPayment::where('updated_at', '>=', Carbon::now()->subHours(24))
            ->whereNotNull('asaas_customer_id')
            ->distinct()
            ->pluck('asaas_customer_id');

        $this->info("Recalculando status para {$customerIds->count()} clientes...");

        foreach ($customerIds as $asaasId) {
            $cliente = Cliente::where('asaas_customer_id', $asaasId)->first();
            if ($cliente) {
                try {
                    $resultado = ClienteStatusService::calcularStatusViaAsaas($cliente);
                    ClienteStatusService::aplicarStatusAsaas($cliente, $resultado);
                } catch (\Exception $e) {
                    Log::error("Erro na reconciliação do cliente {$cliente->id}", ['error' => $e->getMessage()]);
                }
            }
        }

        $this->info('Reconciliação finalizada.');
        return Command::SUCCESS;
    }
}
