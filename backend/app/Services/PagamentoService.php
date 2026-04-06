<?php

namespace App\Services;

use App\Mail\ClienteBoasVindas;
use App\Mail\VendedorPagamentoConfirmado;
use App\Models\Comissao;
use App\Models\CommissionRule;
use App\Models\LogEvento;
use App\Models\Pagamento;
use App\Models\Setting;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PagamentoService
{
    /**
     * Sincroniza o status de um pagamento com o Asaas e atualiza a venda.
     * Retorna true se o pagamento foi confirmado nesta execução.
     */
    public function sync(Pagamento $pagamento): bool
    {
        if (! $pagamento->asaas_payment_id) {
            return false;
        }

        // PROTEÇÃO CRÍTICA: Verificar se a venda e cliente existem
        $venda = $pagamento->venda;
        if (!$venda) {
            Log::error('PagamentoService: sync ignorado - pagamento sem venda', [
                'pagamento_id' => $pagamento->id,
                'asaas_payment_id' => $pagamento->asaas_payment_id,
            ]);
            return false;
        }
        if (!$venda->cliente) {
            Log::error('PagamentoService: sync ignorado - venda sem cliente', [
                'pagamento_id' => $pagamento->id,
                'venda_id' => $venda->id,
            ]);
            return false;
        }

        try {
            $asaas = new AsaasService;
            $asaasId = $pagamento->asaas_payment_id;
            $paymentData = null;

            // Se for subscription, buscar o primeiro pagamento da assinatura
            if (str_starts_with($asaasId, 'sub_')) {
                try {
                    $paymentsResponse = $asaas->requestAsaas('GET', "/subscriptions/{$asaasId}/payments");
                    if (! empty($paymentsResponse['data']) && count($paymentsResponse['data']) > 0) {
                        $paymentData = $paymentsResponse['data'][0];
                        // Atualiza o asaas_payment_id para o ID real do pagamento
                        if (! empty($paymentData['id'])) {
                            $pagamento->asaas_payment_id = $paymentData['id'];
                            $pagamento->save();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('PagamentoService: erro ao buscar pagamentos da subscription', ['error' => $e->getMessage()]);

                    return false;
                }
            } else {
                $paymentData = $asaas->getPayment($asaasId);
            }

            if (! $paymentData) {
                return false;
            }

            $statusAsaas = strtoupper($paymentData['status'] ?? '');
            $isPago = in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']);
            $alreadyPago = in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED']);

            // Atualiza dados básicos se disponíveis
            if (! empty($paymentData['bankSlipUrl'])) {
                $pagamento->bank_slip_url = $paymentData['bankSlipUrl'];
            }
            if (! empty($paymentData['identificationField'])) {
                $pagamento->linha_digitavel = $paymentData['identificationField'];
            }
            if (! empty($paymentData['transactionReceiptUrl'])) {
                $pagamento->link_pagamento = $paymentData['transactionReceiptUrl'];
            }

            // Atualizar forma_pagamento_real com billingType do Asaas
            if (! empty($paymentData['billingType'])) {
                $billingType = strtoupper($paymentData['billingType']);
                $formaMap = [
                    'PIX' => 'pix',
                    'BOLETO' => 'boleto',
                    'CREDIT_CARD' => 'cartao',
                    'CREDIT_CARD_RECURRING' => 'cartao',
                ];
                $pagamento->forma_pagamento_real = $formaMap[$billingType] ?? strtolower($billingType);
            }

            if ($isPago && ! $alreadyPago) {
                $this->confirmarPagamento($pagamento, $paymentData);

                return true;
            }

            // Se não mudou para PAGO, apenas atualiza o status se for diferente
            if ($statusAsaas !== strtoupper($pagamento->status)) {
                $pagamento->status = $statusAsaas;
                $pagamento->save();

                $venda = $pagamento->venda;
                if ($venda) {
                    // Para vendas parceladas: se já tem parcela paga, NÃO alterar status da venda
                    // exceto para cancelamento/estorno
                    $isCancelamento = in_array($statusAsaas, ['CANCELED', 'DELETED', 'REFUNDED', 'REFUND_REQUESTED']);
                    $isParcelado = $venda->isPagamentoParcelado();
                    $jaTemParcelaPaga = $venda->getParcelaAtual() > 0;

                    if ($isParcelado && $jaTemParcelaPaga && ! $isCancelamento) {
                        // Manter venda como PAGO - não alterar status
                        Log::info("PagamentoService: Venda parcelada #{$venda->id} mantida como PAGO (parcela {$venda->getParcelaAtual()}/{$venda->parcelas})");
                    } else {
                        $vendaStatus = AsaasService::mapStatus($statusAsaas);
                        $venda->status = $vendaStatus;

                        if ($isCancelamento) {
                            $venda->comissao_gerada = 0;
                            $venda->valor_comissao = 0;
                        }

                        $venda->save();
                    }

                    // Sincronizar cobranças auxiliares
                    foreach ($venda->cobrancas as $cobranca) {
                        $cobranca->status = $statusAsaas;
                        $cobranca->save();
                    }

                    // Atualizar status do cliente
                    $cliente = $venda->cliente;
                    if ($cliente) {
                        ClienteStatusService::atualizarCliente($cliente);
                    }
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('PagamentoService: Erro ao sincronizar', [
                'pagamento_id' => $pagamento->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Marca o pagamento como recebido, gera comissão e dispara automações.
     */
    public function confirmarPagamento(Pagamento $pagamento, array $paymentData = []): void
    {
        // PROTEÇÃO CRÍTICA: Verificar se a venda e cliente existem
        $venda = $pagamento->venda;
        if (!$venda) {
            Log::error('PagamentoService: tentativa de confirmar pagamento sem venda', [
                'pagamento_id' => $pagamento->id,
                'asaas_payment_id' => $pagamento->asaas_payment_id,
            ]);
            return;
        }
        if (!$venda->cliente) {
            Log::error('PagamentoService: venda sem cliente. NAO confirmando pagamento.', [
                'pagamento_id' => $pagamento->id,
                'venda_id' => $venda->id,
            ]);
            return;
        }

        // PROTEÇÃO CRÍTICA: Verificar se o status Asaas é válido
        $statusAsaas = strtoupper($paymentData['status'] ?? '');
        if (!in_array($statusAsaas, ['RECEIVED', 'CONFIRMED'])) {
            Log::error('PagamentoService: status Asaas invalido para confirmacao', [
                'pagamento_id' => $pagamento->id,
                'status_asaas' => $statusAsaas,
            ]);
            return;
        }

        $pagamento->status = 'RECEIVED';
        $pagamento->data_pagamento = now();

        // Atualizar forma_pagamento_real com dados do Asaas
        if (! empty($paymentData['billingType'])) {
            $billingType = strtoupper($paymentData['billingType']);
            $formaMap = [
                'PIX' => 'pix',
                'BOLETO' => 'boleto',
                'CREDIT_CARD' => 'cartao',
                'CREDIT_CARD_RECURRING' => 'cartao',
            ];
            $pagamento->forma_pagamento_real = $formaMap[$billingType] ?? strtolower($billingType);
        }

        $pagamento->save();

        $venda = $pagamento->venda;
        if ($venda) {
            $statusAnterior = $venda->status;
            $venda->status = 'PAGO';

            // ══════════════════════════════════════════════════════════════
            // GERAR COMISSÕES (Sistema Novo: Fixed por plano + Sistema Legado: %)
            // ══════════════════════════════════════════════════════════════
            $vendedor = $venda->vendedor;
            if ($vendedor) {
                $planoNome = $venda->plano ?? '';
                $commissionRule = CommissionRule::forPlan($planoNome);

                // Determinar se é primeira parcela ou recorrência
                $isPrimeiraParcela = $pagamento->parcela_numero == 1 || $pagamento->parcela_numero === null;
                $commissionType = $isPrimeiraParcela ? 'FIRST_PAYMENT' : 'RECURRING';

                // Verificar se já existe comissão para este pagamento
                $jaExisteComissao = Comissao::where('venda_id', $venda->id)
                    ->where('pagamento_id', $pagamento->id)
                    ->where('tipo_comissao', $commissionType)
                    ->exists();

                if (! $jaExisteComissao) {

                    if ($commissionRule) {
                        // ═══════════════════════════════════════════
                        // SISTEMA NOVO: Comissão Fixa por Plano
                        // ═══════════════════════════════════════════

                        // Comissão do Vendedor
                        $sellerAmount = $isPrimeiraParcela
                            ? $commissionRule->seller_fixed_value_first_payment
                            : $commissionRule->seller_fixed_value_recurring;

                        if ($sellerAmount > 0) {
                            Comissao::create([
                                'vendedor_id' => $vendedor->id,
                                'cliente_id' => $venda->cliente_id,
                                'venda_id' => $venda->id,
                                'pagamento_id' => $pagamento->id,
                                'gerente_id' => null,
                                'tipo_comissao' => $commissionType,
                                'percentual_aplicado' => 0,
                                'percentual_gerente' => 0,
                                'valor_venda' => $pagamento->valor,
                                'valor_comissao' => $sellerAmount,
                                'valor_gerente' => 0,
                                'status' => 'confirmada',
                                'data_pagamento' => $pagamento->data_pagamento ?? now(),
                                'competencia' => now()->format('Y-m'),
                                'eligible_at' => $pagamento->data_pagamento ?? now(),
                                'released_at' => $pagamento->data_pagamento ?? now(),
                            ]);

                            $venda->comissao_gerada = ($venda->comissao_gerada ?? 0) + $sellerAmount;

                            Log::info('[Comissão] Vendedor gerada (fixa)', [
                                'venda_id' => $venda->id,
                                'plano' => $planoNome,
                                'tipo' => $commissionType,
                                'valor' => $sellerAmount,
                            ]);
                        }

                        // Comissão do Gestor (se o vendedor tem um gestor E o vendedor não é gestor)
                        if ($vendedor->gestor_id && !$vendedor->is_gestor) {
                            $gestorCommissionRate = $isPrimeiraParcela
                                ? ($vendedor->comissao_gestor_primeira ?? 0)
                                : ($vendedor->comissao_gestor_recorrencia ?? 0);

                            if ($gestorCommissionRate > 0) {
                                $gestorAmount = ($pagamento->valor * $gestorCommissionRate) / 100;

                                if ($gestorAmount > 0) {
                                    Comissao::create([
                                        'vendedor_id' => $vendedor->id,
                                        'cliente_id' => $venda->cliente_id,
                                        'venda_id' => $venda->id,
                                        'pagamento_id' => $pagamento->id,
                                        'gerente_id' => $vendedor->gestor_id,
                                        'tipo_comissao' => $commissionType,
                                        'percentual_aplicado' => 0,
                                        'percentual_gerente' => $gestorCommissionRate,
                                        'valor_venda' => $pagamento->valor,
                                        'valor_comissao' => 0,
                                        'valor_gerente' => $gestorAmount,
                                        'status' => 'confirmada',
                                        'data_pagamento' => $pagamento->data_pagamento ?? now(),
                                        'competencia' => now()->format('Y-m'),
                                        'eligible_at' => $pagamento->data_pagamento ?? now(),
                                        'released_at' => $pagamento->data_pagamento ?? now(),
                                    ]);

                                    $venda->comissao_gerada = ($venda->comissao_gerada ?? 0) + $gestorAmount;

                                    Log::info('[Comissão] Gestor gerada (fixa)', [
                                        'venda_id' => $venda->id,
                                        'gestor_id' => $vendedor->gestor_id,
                                        'plano' => $planoNome,
                                        'tipo' => $commissionType,
                                        'percentual' => $gestorCommissionRate,
                                        'valor' => $gestorAmount,
                                    ]);
                                }
                            }
                        }

                        // Comissão do Gerente (Master)
                        $manager = User::where('perfil', 'master')->first();
                        $managerAmount = $isPrimeiraParcela
                            ? $commissionRule->manager_fixed_value_first_payment
                            : $commissionRule->manager_fixed_value_recurring;

                        if ($managerAmount > 0 && $manager) {
                            Comissao::create([
                                'vendedor_id' => $vendedor->id,
                                'cliente_id' => $venda->cliente_id,
                                'venda_id' => $venda->id,
                                'pagamento_id' => $pagamento->id,
                                'gerente_id' => $manager->id,
                                'tipo_comissao' => $commissionType,
                                'percentual_aplicado' => 0,
                                'percentual_gerente' => 0,
                                'valor_venda' => $pagamento->valor,
                                'valor_comissao' => 0,
                                'valor_gerente' => $managerAmount,
                                'status' => 'confirmada',
                                'data_pagamento' => $pagamento->data_pagamento ?? now(),
                                'competencia' => now()->format('Y-m'),
                                'eligible_at' => $pagamento->data_pagamento ?? now(),
                                'released_at' => $pagamento->data_pagamento ?? now(),
                            ]);

                            Log::info('[Comissão] Gerente gerada (fixa)', [
                                'venda_id' => $venda->id,
                                'gerente' => $manager->name,
                                'plano' => $planoNome,
                                'tipo' => $commissionType,
                                'valor' => $managerAmount,
                            ]);
                        }

                    } else {
                        // ═══════════════════════════════════════════
                        // SISTEMA LEGADO: Comissão Percentual
                        // ═══════════════════════════════════════════
                        $percentual = $vendedor->percentual_comissao ?: ($vendedor->comissao ?: 10);
                        $isAnualParcelado = ($venda->tipo_negociacao === 'anual') && ($venda->parcelas > 1);

                        if ($isAnualParcelado) {
                            $baseComissao = $venda->valor_final ?? $venda->valor;
                        } else {
                            $baseComissao = $pagamento->valor;
                        }

                        $comissao = ($baseComissao * $percentual) / 100;

                        $venda->comissao_gerada = $comissao;
                        $venda->valor_comissao = $comissao;

                        Comissao::create([
                            'vendedor_id' => $vendedor->id,
                            'cliente_id' => $venda->cliente_id,
                            'venda_id' => $venda->id,
                            'pagamento_id' => $pagamento->id,
                            'tipo_comissao' => $isPrimeiraParcela ? 'FIRST_PAYMENT' : 'RECURRING',
                            'percentual_aplicado' => $percentual,
                            'valor_venda' => $baseComissao,
                            'valor_comissao' => $comissao,
                            'status' => 'confirmada',
                            'data_pagamento' => $pagamento->data_pagamento ?? now(),
                            'competencia' => now()->format('Y-m'),
                            'eligible_at' => $pagamento->data_pagamento ?? now(),
                            'released_at' => $pagamento->data_pagamento ?? now(),
                        ]);

                        Log::info('[Comissão] Vendedor gerada (percentual)', [
                            'venda_id' => $venda->id,
                            'percentual' => $percentual,
                            'base' => $baseComissao,
                            'comissao' => $comissao,
                        ]);

                        // Comissão do Gestor (sistema legado - percentual) — só se vendedor NÃO for gestor
                        if ($vendedor->gestor_id && !$vendedor->is_gestor) {
                            $gestorPercentual = $isPrimeiraParcela
                                ? ($vendedor->comissao_gestor_primeira ?? 0)
                                : ($vendedor->comissao_gestor_recorrencia ?? 0);

                            if ($gestorPercentual > 0) {
                                $comissaoGestor = ($baseComissao * $gestorPercentual) / 100;

                                if ($comissaoGestor > 0) {
                                    Comissao::create([
                                        'vendedor_id' => $vendedor->id,
                                        'cliente_id' => $venda->cliente_id,
                                        'venda_id' => $venda->id,
                                        'pagamento_id' => $pagamento->id,
                                        'gerente_id' => $vendedor->gestor_id,
                                        'tipo_comissao' => $isPrimeiraParcela ? 'FIRST_PAYMENT' : 'RECURRING',
                                        'percentual_aplicado' => $percentual,
                                        'percentual_gerente' => $gestorPercentual,
                                        'valor_venda' => $baseComissao,
                                        'valor_comissao' => 0,
                                        'valor_gerente' => $comissaoGestor,
                                        'status' => 'confirmada',
                                        'data_pagamento' => $pagamento->data_pagamento ?? now(),
                                        'competencia' => now()->format('Y-m'),
                                        'eligible_at' => $pagamento->data_pagamento ?? now(),
                                        'released_at' => $pagamento->data_pagamento ?? now(),
                                    ]);

                                    $venda->comissao_gerada = ($venda->comissao_gerada ?? 0) + $comissaoGestor;

                                    Log::info('[Comissão] Gestor gerada (percentual)', [
                                        'venda_id' => $venda->id,
                                        'gestor_id' => $vendedor->gestor_id,
                                        'percentual' => $gestorPercentual,
                                        'base' => $baseComissao,
                                        'comissao' => $comissaoGestor,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $venda->save();

            // Sincronizar cobranças auxiliares
            foreach ($venda->cobrancas as $cobranca) {
                $cobranca->status = 'RECEIVED';
                $cobranca->save();
            }

            // Atualizar status do cliente para ATIVO
            $cliente = $venda->cliente;
            if ($cliente) {
                $cliente->status = 'ativo';
                $cliente->data_ultimo_pagamento = now();
                $cliente->save();
            }

            // ─── Cria conta no Basiléia Church ──────────────────────
            if ($cliente) {
                try {
                    $church = new ChurchProvisioningService;
                    $church->criarConta($cliente, $venda);
                } catch (\Exception $e) {
                    Log::error('[Church] Falha ao criar conta no PagamentoService', [
                        'cliente_id' => $cliente->id,
                        'venda_id' => $venda->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Automações (Email)
            if (strtoupper($statusAnterior) !== 'PAGO') {
                $this->dispararAutomacoes($venda, $pagamento);
            }

            Log::info("PagamentoService: Venda #{$venda->id} confirmada com sucesso.");
        }

        // Log de Evento
        LogEvento::create([
            'usuario_id' => 1,
            'entidade' => 'Pagamento',
            'entidade_id' => $pagamento->id,
            'acao' => 'Sincronização: Confirmado',
            'descricao' => 'Pagamento detectado como pago no Asaas durante sincronização.',
        ]);
    }

    private function dispararAutomacoes(Venda $venda, Pagamento $pagamento): void
    {
        // Email para o vendedor: pagamento confirmado
        try {
            $vendedorUser = $venda->vendedor?->user;
            if ($vendedorUser && $vendedorUser->email) {
                $fromEmail = Setting::get('email_vendedor_from', config('mail.from.address'));
                $fromName = config('mail.from.name', 'Basiléia Global');

                Mail::to($vendedorUser->email)
                    ->send((new VendedorPagamentoConfirmado($venda))->from($fromEmail, $fromName));
                Log::info('[Email] VendedorPagamentoConfirmado enviado', ['venda_id' => $venda->id, 'to' => $vendedorUser->email]);
            }
        } catch (\Exception $e) {
            Log::error('[Email] Falha ao enviar VendedorPagamentoConfirmado', [
                'venda_id' => $venda->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Email para o cliente: boas-vindas
        try {
            $cliente = $venda->cliente;
            if ($cliente && $cliente->email) {
                $fromEmail = Setting::get('email_cliente_from', config('mail.from.address'));
                $fromName = config('mail.from.name', 'Basiléia Global');

                Mail::to($cliente->email)
                    ->send((new ClienteBoasVindas($cliente))->from($fromEmail, $fromName));
                Log::info('[Email] ClienteBoasVindas enviado', ['cliente_id' => $cliente->id, 'to' => $cliente->email]);
            }
        } catch (\Exception $e) {
            Log::error('[Email] Falha ao enviar ClienteBoasVindas', [
                'cliente_id' => $venda->cliente_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
