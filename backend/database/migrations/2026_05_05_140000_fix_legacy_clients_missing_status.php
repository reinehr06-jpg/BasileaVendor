<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Correção retroativa: clientes criados a partir de legacy_customer_imports
 * que ficaram sem status, asaas_customer_id, data_ultimo_pagamento, etc.
 * 
 * Também re-confirma imports que têm vendedor mas nunca foram confirmados.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Corrigir clientes já confirmados mas sem status
        $importsConfirmados = DB::table('legacy_customer_imports')
            ->whereNotNull('local_cliente_id')
            ->get();

        $corrigidos = 0;
        foreach ($importsConfirmados as $import) {
            $statusCliente = match($import->diagnostico_status) {
                'ATIVO'     => 'ativo',
                'CHURN'     => 'inadimplente',
                'CANCELADO' => 'cancelado',
                default     => 'pendente',
            };

            $updateData = [
                'status'     => $statusCliente,
                'updated_at' => now(),
            ];

            // Preencher asaas_customer_id se estiver vazio
            if (!empty($import->asaas_customer_id)) {
                $updateData['asaas_customer_id'] = $import->asaas_customer_id;
            }

            // Preencher datas de pagamento se disponíveis
            if (!empty($import->ultimo_pagamento_confirmado_at)) {
                $updateData['data_ultimo_pagamento'] = $import->ultimo_pagamento_confirmado_at;
            }
            if (!empty($import->proximo_vencimento_at)) {
                $updateData['proxima_cobranca'] = $import->proximo_vencimento_at;
            }

            try {
                $cliente = DB::table('clientes')->where('id', $import->local_cliente_id)->first();
                if ($cliente && empty($cliente->status)) {
                    DB::table('clientes')->where('id', $import->local_cliente_id)->update($updateData);
                    $corrigidos++;
                }
            } catch (\Exception $e) {
                Log::warning("Migration fix_legacy_clients: erro ao atualizar cliente {$import->local_cliente_id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 2. Auto-confirmar imports que têm vendedor mas nunca foram confirmados
        $importsPendentes = DB::table('legacy_customer_imports')
            ->whereNull('local_cliente_id')
            ->whereNotNull('vendedor_id')
            ->get();

        $confirmados = 0;
        foreach ($importsPendentes as $import) {
            $doc = preg_replace('/\D/', '', $import->documento ?? '');
            if (empty($doc)) continue;

            $statusCliente = match($import->diagnostico_status) {
                'ATIVO'     => 'ativo',
                'CHURN'     => 'inadimplente',
                'CANCELADO' => 'cancelado',
                default     => 'pendente',
            };

            try {
                // Criar ou reutilizar cliente
                $clienteExistente = DB::table('clientes')->where('documento', $doc)->first();
                if (!$clienteExistente) {
                    $clienteId = DB::table('clientes')->insertGetId([
                        'nome'              => $import->nome,
                        'documento'         => $doc,
                        'contato'           => $import->telefone,
                        'whatsapp'          => $import->telefone,
                        'email'             => $import->email,
                        'status'            => $statusCliente,
                        'asaas_customer_id' => $import->asaas_customer_id,
                        'data_ultimo_pagamento' => $import->ultimo_pagamento_confirmado_at,
                        'proxima_cobranca'      => $import->proximo_vencimento_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $clienteId = $clienteExistente->id;
                    DB::table('clientes')->where('id', $clienteId)->update([
                        'status'            => $statusCliente,
                        'asaas_customer_id' => $import->asaas_customer_id,
                        'data_ultimo_pagamento' => $import->ultimo_pagamento_confirmado_at,
                        'proxima_cobranca'      => $import->proximo_vencimento_at,
                        'updated_at' => now(),
                    ]);
                }

                // Criar venda
                $billingType = $import->asaas_subscription_billing_type ?? 'BOLETO';
                $formaPgto = match(strtoupper($billingType)) {
                    'CREDIT_CARD' => 'Cartão de Crédito',
                    'PIX'         => 'PIX',
                    default       => 'Boleto',
                };
                $statusVenda = match($import->diagnostico_status) {
                    'ATIVO'  => 'Pago',
                    'CHURN'  => 'Aguardando pagamento',
                    default  => 'Cancelada',
                };
                $tipoNegociacao = match($import->tipo_cobranca) {
                    'installment'  => 'parcelado',
                    'subscription' => 'mensal',
                    default        => 'avulso',
                };

                $valorVendaReal = (float) ($import->valor_total_cobranca ?? 0);
                if ($valorVendaReal <= 0) {
                    $valorVendaReal = ($import->valor_plano_mensal ?? 0) * ($import->parcelas_total ?? 1);
                }

                DB::table('vendas')->updateOrInsert(
                    ['cliente_id' => $clienteId, 'origem' => 'asaas_legado'],
                    [
                        'vendedor_id'      => $import->vendedor_id,
                        'valor'            => $valorVendaReal,
                        'comissao_gerada'  => $import->comissao_vendedor_calculada ?? 0,
                        'status'           => $statusVenda,
                        'forma_pagamento'  => $formaPgto,
                        'tipo_negociacao'  => $tipoNegociacao,
                        'parcelas'         => $import->parcelas_total ?? 1,
                        'origem'           => 'asaas_legado',
                        'data_venda'       => $import->primeiro_pagamento_at ?? now()->toDateString(),
                        'updated_at'       => now(),
                    ]
                );

                $venda = DB::table('vendas')
                    ->where('cliente_id', $clienteId)
                    ->where('origem', 'asaas_legado')
                    ->first();

                if ($venda) {
                    // Criar pagamento para clientes ativos
                    if ($statusVenda === 'Pago') {
                        DB::table('pagamentos')->updateOrInsert(
                            ['venda_id' => $venda->id, 'asaas_payment_id' => 'legacy_' . $import->id],
                            [
                                'cliente_id'      => $clienteId,
                                'vendedor_id'     => $import->vendedor_id,
                                'valor'           => $import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0,
                                'status'          => 'RECEIVED',
                                'data_pagamento'  => $import->ultimo_pagamento_confirmado_at ?? $import->primeiro_pagamento_at ?? now(),
                                'data_vencimento' => $import->primeiro_pagamento_at ?? now(),
                                'forma_pagamento' => $formaPgto,
                                'billing_type'    => $billingType,
                            ]
                        );
                    }

                    // Vincular import
                    DB::table('legacy_customer_imports')->where('id', $import->id)->update([
                        'local_cliente_id' => $clienteId,
                        'local_venda_id'   => $venda->id,
                        'confirmado_em'    => now(),
                        'updated_at'       => now(),
                    ]);

                    $confirmados++;
                }
            } catch (\Exception $e) {
                Log::warning("Migration fix_legacy_clients: erro ao confirmar import {$import->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Migration fix_legacy_clients_missing_status concluída", [
            'clientes_corrigidos' => $corrigidos,
            'imports_confirmados' => $confirmados,
        ]);
    }

    public function down(): void
    {
        // Não é seguro reverter esta operação
    }
};
