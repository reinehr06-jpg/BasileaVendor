<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AsaasEvent;
use App\Models\Cliente;
use App\Services\ClienteStatusService;
use App\Services\ChurchProvisioningService;
use App\Mail\ClienteAcessoSuspenso;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Services\CommissionEngineService;
use App\Models\Pagamento;

class ProcessAsaasEventsCommand extends Command
{
    protected $signature = 'asaas:process-events';
    protected $description = 'Processa fila de eventos de webhooks assíncronos do Asaas';

    public function handle()
    {
        $events = AsaasEvent::where('status', 'PENDING')
                            ->orderBy('id', 'asc')
                            ->limit(50)
                            ->get();

        if ($events->isEmpty()) {
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$events->count()} eventos pendentes.");

        foreach ($events as $eventModel) {
            DB::beginTransaction();
            try {
                $payload = $eventModel->payload;
                $eventName = $eventModel->event_name;
                $payment = $payload['payment'] ?? null;
                $customerId = $payment['customer'] ?? $payload['customer'] ?? null;

                if ($customerId) {
                    $cliente = Cliente::where('asaas_customer_id', $customerId)->first();
                    if ($cliente) {
                        // Atualizar a data do último webhook
                        $cliente->update(['last_webhook_event_at' => now()]);

                        // RECALCULAR STATUS COMPLETO (Sincroniza pagamentos e atualiza status local)
                        $resultado = ClienteStatusService::calcularStatusViaAsaas($cliente);
                        ClienteStatusService::aplicarStatusAsaas($cliente, $resultado);
                        
                        $novoStatus = $resultado['status'];

                        // ════════════════════════════════════════════════════════
                        // Automações de Integração (Church / Email)
                        // ════════════════════════════════════════════════════════
                        if (in_array($eventName, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED']) && $novoStatus === 'ativo') {
                            try {
                                $church = new ChurchProvisioningService();
                                if ($cliente->church_user_id) {
                                    $church->reativarConta($cliente);
                                } else {
                                    // Obs: Venda não está mais no escopo explícito, 
                                    // mas as funções de church aceitam null para a venda dependendo da implementação
                                    $church->criarConta($cliente, null); 
                                }
                            } catch (\Exception $e) {
                                Log::error('[Church] Falha ao reativar/criar conta após pagamento asaas event', ['error' => $e->getMessage()]);
                            }

                            // ════════════════════════════════════════════════════════
                            // Lógica de Comissões (Motor Automático)
                            // ════════════════════════════════════════════════════════
                            try {
                                if (isset($payment['id'])) {
                                    $pagamentoLocal = Pagamento::where('asaas_payment_id', $payment['id'])->first();
                                    if ($pagamentoLocal && $pagamentoLocal->venda_id) {
                                        $vendaLocal = \App\Models\Venda::find($pagamentoLocal->venda_id);
                                        if ($vendaLocal && $vendaLocal->vendedor_id) {
                                            CommissionEngineService::processarPagamento($pagamentoLocal, $vendaLocal);
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('[Commission] Falha ao processar comissão no Webhook', ['error' => $e->getMessage(), 'payment_id' => $payment['id'] ?? null]);
                            }
                        }

                        if ($eventName === 'PAYMENT_OVERDUE' && in_array($novoStatus, ['inadimplente', 'churn'])) {
                            try {
                                $church = new ChurchProvisioningService();
                                $church->suspenderConta($cliente);
                                Log::info('[Church] Conta suspensa por pagamento vencido via Asaas Event', ['cliente_id' => $cliente->id]);
                            } catch (\Exception $e) {
                                Log::error('[Church] Falha ao suspender conta', ['error' => $e->getMessage()]);
                            }

                            try {
                                if ($cliente->email) {
                                    Mail::to($cliente->email)->send(new ClienteAcessoSuspenso($cliente));
                                }
                            } catch (\Exception $e) {
                                Log::error('[Email] Falha ao enviar aviso de suspensão', ['error' => $e->getMessage()]);
                            }
                        }
                    }
                }

                $eventModel->status = 'DONE';
                $eventModel->save();
                
                DB::commit();
                $this->info("Evento {$eventModel->asaas_event_id} processado.");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('ProcessAsaasEventsCommand: Erro ao processar evento', [
                    'event_id' => $eventModel->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
