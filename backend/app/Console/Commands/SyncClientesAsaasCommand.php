<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Services\ClienteStatusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncClientesAsaasCommand extends Command
{
    protected $signature = 'clientes:sync-asaas
                            {--cliente= : Sincronizar um cliente específico por ID}
                            {--limit=50 : Limite de clientes por execução}
                            {--force : Forçar re-sync mesmo de clientes sincronizados recentemente}';

    protected $description = 'Sincroniza status dos clientes consultando diretamente a API do Asaas (mês a mês)';

    public function handle(): int
    {
        $clienteId = $this->option('cliente');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info('═══════════════════════════════════════════════');
        $this->info('  🔄 Sincronização de Clientes via Asaas API');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        // ═══════════════════════════════════════════
        // MODO 1: Sincronizar um cliente específico
        // ═══════════════════════════════════════════
        if ($clienteId) {
            $cliente = Cliente::find($clienteId);
            if (!$cliente) {
                $this->error("Cliente #{$clienteId} não encontrado.");
                return self::FAILURE;
            }

            if (empty($cliente->asaas_customer_id)) {
                $this->warn("Cliente #{$clienteId} ({$cliente->nome_igreja}) não possui asaas_customer_id.");
                $this->info('Usando lógica local como fallback...');
            }

            $this->info("Sincronizando: {$cliente->nome_igreja} (ID: {$cliente->id})");
            $statusAnterior = $cliente->status;

            $resultado = ClienteStatusService::calcularStatusViaAsaas($cliente);
            ClienteStatusService::aplicarStatusAsaas($cliente, $resultado);

            $this->newLine();
            $this->info("  📊 Resultado:");
            $this->info("     Status anterior:  {$statusAnterior}");
            $this->info("     Status novo:      {$resultado['status']}");
            $this->info("     Último pagamento: " . ($resultado['data_ultimo_pagamento'] ?? 'N/A'));
            $this->info("     Próxima cobrança: " . ($resultado['proxima_cobranca'] ?? 'N/A'));
            $this->info("     Pagamentos sync:  {$resultado['pagamentos_sincronizados']}");

            if ($statusAnterior !== $resultado['status']) {
                $this->warn("  ⚠️  Status MUDOU de '{$statusAnterior}' para '{$resultado['status']}'");
            } else {
                $this->info("  ✅ Status mantido: {$resultado['status']}");
            }

            return self::SUCCESS;
        }

        // ═══════════════════════════════════════════
        // MODO 2: Sincronizar todos os clientes
        // ═══════════════════════════════════════════
        $query = Cliente::whereNotNull('asaas_customer_id')
            ->where('asaas_customer_id', '!=', '');

        // Se não forçar, sincronizar apenas clientes "silenciosos" (sem webhook nas últimas 24h)
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('last_webhook_event_at')
                  ->orWhere('last_webhook_event_at', '<', now()->subHours(24));
            });
        }

        $clientes = $query->take($limit)->get();

        if ($clientes->isEmpty()) {
            $this->info('Nenhum cliente com asaas_customer_id encontrado.');
            return self::SUCCESS;
        }

        $this->info("📋 Encontrados {$clientes->count()} clientes para sincronizar.");
        $this->newLine();

        $bar = $this->output->createProgressBar($clientes->count());

        $totais = [
            'ativo' => 0,
            'pendente' => 0,
            'inadimplente' => 0,
            'churn' => 0,
            'cancelado' => 0,
            'mudaram' => 0,
            'erros' => 0,
            'pagamentos_sync' => 0,
        ];

        foreach ($clientes as $cliente) {
            try {
                $statusAnterior = $cliente->status;

                $resultado = ClienteStatusService::calcularStatusViaAsaas($cliente);
                ClienteStatusService::aplicarStatusAsaas($cliente, $resultado);

                $status = $resultado['status'];
                $totais[$status] = ($totais[$status] ?? 0) + 1;
                $totais['pagamentos_sync'] += $resultado['pagamentos_sincronizados'];

                if ($statusAnterior !== $status) {
                    $totais['mudaram']++;
                    $this->newLine();
                    $this->warn("  ↕ {$cliente->nome_igreja}: {$statusAnterior} → {$status}");
                }

                // Rate limiting: 200ms entre chamadas para não sobrecarregar a API
                usleep(200000);

            } catch (\Exception $e) {
                $totais['erros']++;
                $this->newLine();
                $this->error("  ✗ Cliente #{$cliente->id} ({$cliente->nome_igreja}): {$e->getMessage()}");
                Log::error('SyncClientesAsaas: erro ao sincronizar', [
                    'cliente_id' => $cliente->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ═══════════════════════════════════════════
        // RESUMO
        // ═══════════════════════════════════════════
        $this->info('═══════════════════════════════════════════════');
        $this->info('  📊 RESUMO DA SINCRONIZAÇÃO');
        $this->info('═══════════════════════════════════════════════');
        $this->info("  ✅ Ativos:         {$totais['ativo']}");
        $this->info("  ⏳ Pendentes:      {$totais['pendente']}");
        $this->info("  ⚠️  Inadimplentes: {$totais['inadimplente']}");
        $this->info("  📉 Churn:          {$totais['churn']}");
        $this->info("  ❌ Cancelados:     {$totais['cancelado']}");
        $this->info("  ↕  Mudaram status: {$totais['mudaram']}");
        $this->info("  🔄 Pagamentos sync:{$totais['pagamentos_sync']}");

        if ($totais['erros'] > 0) {
            $this->error("  ❌ Erros:          {$totais['erros']}");
        }

        $this->newLine();

        Log::info('[SyncClientesAsaas] Sincronização concluída', $totais);

        return self::SUCCESS;
    }
}
