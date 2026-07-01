<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClienteStatusService
{
    /**
     * Calcula o status do cliente consultando DIRETAMENTE a API do Asaas.
     * Esta é a fonte de verdade para saber se o cliente está ativo ou não.
     *
     * Fluxo:
     *  1. Se o cliente tem asaas_customer_id → consulta Asaas por pagamentos recentes
     *  2. Se não tem → fallback para lógica local (dados do banco)
     *
     * Status possíveis: ativo, pendente, inadimplente, churn, cancelado
     */
    public static function calcularStatusViaAsaas(Cliente $cliente): array
    {
        $resultado = [
            'status' => 'pendente',
            'data_ultimo_pagamento' => null,
            'proxima_cobranca' => null,
            'recorrencia_status' => null,
            'pagamentos_sincronizados' => 0,
        ];

        // Se não tem asaas_customer_id, usar lógica local
        if (empty($cliente->asaas_customer_id)) {
            $resultado['status'] = self::calcularStatusLocal($cliente);
            return $resultado;
        }

        try {
            $asaas = new AsaasService();
            $agora = Carbon::now();

            // ══════════════════════════════════════════════════════════════
            // ETAPA 1: Buscar TODOS os pagamentos dos últimos 60 dias
            // Isso cobre o mês atual + mês anterior para determinar status
            // ══════════════════════════════════════════════════════════════
            $startDate = $agora->copy()->subDays(60)->format('Y-m-d');
            $endDate = $agora->format('Y-m-d');

            $pagamentosAsaas = $asaas->getPaymentsByCustomer(
                $cliente->asaas_customer_id,
                $startDate,
                $endDate
            );

            if (empty($pagamentosAsaas)) {
                // Sem pagamentos nos últimos 60 dias → verificar se já pagou antes
                $pagamentosAntigos = $asaas->getPaymentsByCustomer(
                    $cliente->asaas_customer_id,
                    null, // Sem filtro de data de início
                    $startDate
                );

                $jaFoiPago = collect($pagamentosAntigos)->contains(function ($p) {
                    return in_array(strtoupper($p['status'] ?? ''), ['RECEIVED', 'CONFIRMED']);
                });

                $resultado['status'] = $jaFoiPago ? 'churn' : 'pendente';

                // Sincronizar pagamentos encontrados
                $resultado['pagamentos_sincronizados'] = self::sincronizarPagamentosLocais(
                    $cliente, array_merge($pagamentosAntigos, $pagamentosAsaas)
                );

                return $resultado;
            }

            // ══════════════════════════════════════════════════════════════
            // ETAPA 2: Sincronizar pagamentos do Asaas → tabela local
            // ══════════════════════════════════════════════════════════════
            $resultado['pagamentos_sincronizados'] = self::sincronizarPagamentosLocais(
                $cliente, $pagamentosAsaas
            );

            // ══════════════════════════════════════════════════════════════
            // ETAPA 3: Analisar pagamentos para determinar status
            // ══════════════════════════════════════════════════════════════
            $collection = collect($pagamentosAsaas);

            // Ordenar por dueDate desc para pegar o mais recente
            $collection = $collection->sortByDesc('dueDate');

            // Pagamentos confirmados (RECEIVED/CONFIRMED)
            $pagos = $collection->filter(function ($p) {
                return in_array(strtoupper($p['status'] ?? ''), ['RECEIVED', 'CONFIRMED']);
            });

            // Pagamentos pendentes (PENDING/AWAITING_RISK_ANALYSIS)
            $pendentes = $collection->filter(function ($p) {
                return in_array(strtoupper($p['status'] ?? ''), ['PENDING', 'AWAITING_RISK_ANALYSIS']);
            });

            // Pagamentos vencidos (OVERDUE)
            $vencidos = $collection->filter(function ($p) {
                return strtoupper($p['status'] ?? '') === 'OVERDUE';
            });

            // Pagamentos cancelados
            $cancelados = $collection->filter(function ($p) {
                return in_array(strtoupper($p['status'] ?? ''), ['CANCELED', 'DELETED', 'REFUNDED']);
            });

            // ══════════════════════════════════════════════════════════════
            // ETAPA 4: Determinar status final
            // Prioridade: ATIVO > PENDENTE > INADIMPLENTE > CHURN > CANCELADO
            // ══════════════════════════════════════════════════════════════

            // Verificar se tem pagamento PAGO no mês atual
            $inicioMesAtual = $agora->copy()->startOfMonth()->format('Y-m-d');
            $pagosNoMesAtual = $pagos->filter(function ($p) use ($inicioMesAtual) {
                $dueDate = $p['dueDate'] ?? $p['dateCreated'] ?? null;
                return $dueDate && $dueDate >= $inicioMesAtual;
            });

            // Também verificar pagos no mês anterior (cobertura de ciclo mensal)
            $inicioMesAnterior = $agora->copy()->subMonth()->startOfMonth()->format('Y-m-d');
            $fimMesAnterior = $agora->copy()->subMonth()->endOfMonth()->format('Y-m-d');
            $pagosNoMesAnterior = $pagos->filter(function ($p) use ($inicioMesAnterior, $fimMesAnterior) {
                $dueDate = $p['dueDate'] ?? $p['dateCreated'] ?? null;
                return $dueDate && $dueDate >= $inicioMesAnterior && $dueDate <= $fimMesAnterior;
            });

            if ($pagosNoMesAtual->isNotEmpty()) {
                // Pagamento PAGO no mês atual → ATIVO
                $resultado['status'] = 'ativo';
            } elseif ($pagosNoMesAnterior->isNotEmpty() && $vencidos->isEmpty()) {
                // Pagou no mês anterior e sem vencidos → ainda ATIVO (margem de ciclo)
                $resultado['status'] = 'ativo';
            } elseif ($pendentes->isNotEmpty()) {
                // Tem cobrança pendente dentro do prazo
                $pendentesNoFuturo = $pendentes->filter(function ($p) use ($agora) {
                    $dueDate = $p['dueDate'] ?? null;
                    return $dueDate && Carbon::parse($dueDate)->isFuture();
                });

                if ($pendentesNoFuturo->isNotEmpty()) {
                    $resultado['status'] = 'pendente';
                } else {
                    // Pendente mas já venceu → inadimplente
                    $resultado['status'] = 'inadimplente';
                }
            } elseif ($vencidos->isNotEmpty()) {
                // Pagamento OVERDUE
                $ultimoVencido = $vencidos->first();
                $dueDateVencido = Carbon::parse($ultimoVencido['dueDate'] ?? now());
                $diasAtraso = $dueDateVencido->diffInDays($agora);

                // Se já pagou antes e atraso > 30 dias → CHURN
                if ($pagos->isNotEmpty() && $diasAtraso > 30) {
                    $resultado['status'] = 'churn';
                } else {
                    $resultado['status'] = 'inadimplente';
                }
            } elseif ($cancelados->count() === $collection->count() && $collection->isNotEmpty()) {
                // Todos cancelados
                $resultado['status'] = 'cancelado';
            } else {
                // Fallback
                $resultado['status'] = $pagos->isNotEmpty() ? 'ativo' : 'pendente';
            }

            // ══════════════════════════════════════════════════════════════
            // ETAPA 5: Extrair datas auxiliares
            // ══════════════════════════════════════════════════════════════
            $ultimoPago = $pagos->first();
            if ($ultimoPago) {
                $resultado['data_ultimo_pagamento'] = $ultimoPago['paymentDate']
                    ?? $ultimoPago['confirmedDate']
                    ?? $ultimoPago['dueDate']
                    ?? null;
            }

            // Próxima cobrança = primeiro pendente ou vencido não pago
            $proximaPendente = $pendentes->merge($vencidos)->sortBy('dueDate')->first();
            if ($proximaPendente) {
                $resultado['proxima_cobranca'] = $proximaPendente['dueDate'] ?? null;
            }

            // Recorrencia status
            $resultado['recorrencia_status'] = $resultado['status'] === 'ativo' ? 'Pago' : 'Aguardando pagamento';

        } catch (\Exception $e) {
            Log::error('ClienteStatusService: Erro ao calcular status via Asaas', [
                'cliente_id' => $cliente->id,
                'asaas_customer_id' => $cliente->asaas_customer_id,
                'error' => $e->getMessage(),
            ]);

            // Fallback para lógica local em caso de erro de API
            $resultado['status'] = self::calcularStatusLocal($cliente);
        }

        return $resultado;
    }

    /**
     * Sincroniza pagamentos retornados do Asaas com a tabela local `pagamentos`.
     * Cria novos registros se não existirem, atualiza status se mudaram.
     *
     * @return int Quantidade de pagamentos sincronizados
     */
    private static function sincronizarPagamentosLocais(Cliente $cliente, array $pagamentosAsaas): int
    {
        $count = 0;

        foreach ($pagamentosAsaas as $p) {
            $asaasPaymentId = $p['id'] ?? null;
            if (!$asaasPaymentId) {
                continue;
            }

            $statusAsaas = strtoupper($p['status'] ?? 'PENDING');

            // Buscar pagamento existente
            $pagamentoLocal = Pagamento::where('asaas_payment_id', $asaasPaymentId)->first();

            if ($pagamentoLocal) {
                // Atualizar status se diferente
                if (strtoupper($pagamentoLocal->status) !== $statusAsaas) {
                    $pagamentoLocal->status = $statusAsaas;

                    if (in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']) && !$pagamentoLocal->data_pagamento) {
                        $pagamentoLocal->data_pagamento = $p['paymentDate'] ?? $p['confirmedDate'] ?? now();
                    }

                    if (!empty($p['bankSlipUrl'])) {
                        $pagamentoLocal->bank_slip_url = $p['bankSlipUrl'];
                    }
                    if (!empty($p['invoiceUrl'])) {
                        $pagamentoLocal->invoice_url = $p['invoiceUrl'];
                    }
                    if (!empty($p['transactionReceiptUrl'])) {
                        $pagamentoLocal->link_pagamento = $p['transactionReceiptUrl'];
                    }

                    $pagamentoLocal->save();
                    $count++;

                    Log::info('[SyncAsaas] Pagamento local atualizado', [
                        'pagamento_id' => $pagamentoLocal->id,
                        'asaas_id' => $asaasPaymentId,
                        'old_status' => $pagamentoLocal->getOriginal('status'),
                        'new_status' => $statusAsaas,
                    ]);
                }
            } else {
                // Buscar venda vinculada ao cliente para associar o pagamento
                $venda = $cliente->vendas()
                    ->whereNotIn('status', ['Cancelado', 'Expirado'])
                    ->orderByDesc('created_at')
                    ->first();

                // Se não achou venda ativa, buscar por externalReference
                if (!$venda && !empty($p['externalReference'])) {
                    $extRef = $p['externalReference'];
                    if (str_starts_with($extRef, 'venda_')) {
                        $vendaId = (int) str_replace('venda_', '', $extRef);
                        $venda = \App\Models\Venda::find($vendaId);
                    }
                }

                // Se não achou venda por nenhum meio, buscar a última venda do cliente
                if (!$venda) {
                    $venda = $cliente->vendas()->orderByDesc('created_at')->first();
                }

                if ($venda) {
                    $formaMap = ['PIX' => 'pix', 'BOLETO' => 'boleto', 'CREDIT_CARD' => 'cartao', 'CREDIT_CARD_RECURRING' => 'cartao'];
                    $billingType = strtoupper($p['billingType'] ?? 'PIX');

                    Pagamento::create([
                        'venda_id' => $venda->id,
                        'cliente_id' => $cliente->id,
                        'vendedor_id' => $venda->vendedor_id,
                        'asaas_payment_id' => $asaasPaymentId,
                        'valor' => $p['value'] ?? 0,
                        'billing_type' => $billingType,
                        'forma_pagamento' => $formaMap[$billingType] ?? 'pix',
                        'forma_pagamento_real' => $formaMap[$billingType] ?? 'pix',
                        'status' => $statusAsaas,
                        'data_vencimento' => $p['dueDate'] ?? null,
                        'data_pagamento' => in_array($statusAsaas, ['RECEIVED', 'CONFIRMED'])
                            ? ($p['paymentDate'] ?? $p['confirmedDate'] ?? now())
                            : null,
                        'invoice_url' => $p['invoiceUrl'] ?? null,
                        'bank_slip_url' => $p['bankSlipUrl'] ?? null,
                        'link_pagamento' => $p['transactionReceiptUrl'] ?? null,
                    ]);
                    $count++;

                    Log::info('[SyncAsaas] Novo pagamento criado localmente', [
                        'asaas_id' => $asaasPaymentId,
                        'venda_id' => $venda->id,
                        'cliente_id' => $cliente->id,
                        'status' => $statusAsaas,
                    ]);
                }
            }
        }

        return $count;
    }

    /**
     * Aplica o resultado do cálculo via Asaas ao cliente e salva no banco.
     */
    public static function aplicarStatusAsaas(Cliente $cliente, array $resultado): void
    {
        $cliente->status = $resultado['status'];

        if ($resultado['data_ultimo_pagamento']) {
            $cliente->data_ultimo_pagamento = $resultado['data_ultimo_pagamento'];
        }
        if ($resultado['proxima_cobranca']) {
            $cliente->proxima_cobranca = $resultado['proxima_cobranca'];
        }
        if ($resultado['recorrencia_status']) {
            $cliente->recorrencia_status = $resultado['recorrencia_status'];
        }

        $cliente->save();
    }

    // ═══════════════════════════════════════════════════════════════
    // LÓGICA LOCAL (fallback quando não tem asaas_customer_id)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Calcula o status do cliente com base nos seus pagamentos e vendas LOCAIS.
     * Usado como fallback quando o cliente não tem asaas_customer_id.
     *
     * Regras:
     *  - ATIVO:       último pagamento RECEIVED/CONFIRMED, sem cobrança vencida
     *  - PENDENTE:    cobrança PENDING dentro do prazo
     *  - INADIMPLENTE: cobrança OVERDUE ou vencida sem pagamento
     *  - CANCELADO:   pagamento CANCELED/DELETED
     *  - CHURN:       teve pagamento no passado mas perdeu renovação (>30 dias atrasado)
     */
    public static function calcularStatusLocal(Cliente $cliente): string
    {
        // Buscar última venda ativa (não cancelada/expirada)
        $ultimaVenda = $cliente->vendas()
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with('pagamentos')
            ->orderByDesc('created_at')
            ->first();

        // Sem venda ativa → verificar se já teve alguma venda paga ou se é LEGADO
        if (!$ultimaVenda) {
            // Verificar se é legado
            $legado = \App\Models\LegacyCustomerImport::where('local_cliente_id', $cliente->id)->first();
            if ($legado) {
                return mb_strtolower($legado->customer_status ?? 'pendente');
            }

            $teveVendaCancelada = $cliente->vendas()
                ->whereIn('status', ['Cancelado', 'Expirado'])
                ->exists();

            return $teveVendaCancelada ? 'cancelado' : 'pendente';
        }

        $ultimoPagamento = $ultimaVenda->pagamentos()
            ->orderByDesc('created_at')
            ->first();

        // Venda sem pagamento gerado → pendente
        if (!$ultimoPagamento) {
            return 'pendente';
        }

        $statusPagamento = strtoupper($ultimoPagamento->status);
        $vencimento = $ultimoPagamento->data_vencimento;
        $jafoiPago = $cliente->vendas()
            ->whereHas('pagamentos', fn ($q) => $q->whereIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'pago']))
            ->exists();

        // 1. Pago e em dia → ATIVO
        if (in_array($statusPagamento, ['RECEIVED', 'CONFIRMED', 'PAGO'])) {
            return 'ativo';
        }

        // 2. Cancelado / deletado no Asaas → CANCELADO
        if (in_array($statusPagamento, ['CANCELED', 'DELETED'])) {
            return 'cancelado';
        }

        // 3. Vencido no Asaas ou vencimento passou → INADIMPLENTE ou CHURN
        $isOverdue = $statusPagamento === 'OVERDUE'
            || ($vencimento && $vencimento->isPast() && !in_array($statusPagamento, ['RECEIVED', 'CONFIRMED', 'PAGO']));

        if ($isOverdue) {
            // Se já foi pago no passado e o atraso é > 30 dias → CHURN
            if ($jafoiPago && $vencimento && $vencimento->diffInDays(now()) > 30) {
                return 'churn';
            }
            return 'inadimplente';
        }

        // 4. Pendente dentro do prazo
        if ($statusPagamento === 'PENDING' || $statusPagamento === 'AWAITING_RISK_ANALYSIS') {
            // Se já foi pago no passado e está pendente há muito tempo → churn
            if ($jafoiPago && $vencimento && $vencimento->diffInDays(now()) > 30) {
                return 'churn';
            }
            return 'pendente';
        }

        return 'pendente';
    }

    /**
     * Calcula o status do cliente usando a lógica local (mantido por compatibilidade).
     * @deprecated Use calcularStatusViaAsaas() para resultados mais precisos.
     */
    public static function calcularStatus(Cliente $cliente): string
    {
        return self::calcularStatusLocal($cliente);
    }

    /**
     * Sincroniza o status de todos os clientes (ou um subconjunto) no banco.
     * Usado antes de listar clientes na tela.
     */
    public static function sincronizarStatus($clientes = null): void
    {
        try {
            if ($clientes === null) {
                $clientes = Cliente::with(['vendas.pagamentos'])->get();
            }

            foreach ($clientes as $cliente) {
                $novoStatus = self::calcularStatusLocal($cliente);

                // Atualizar data_ultimo_pagamento e proxima_cobranca
                $ultimaVenda = $cliente->vendas()
                    ->with('pagamentos')
                    ->orderByDesc('created_at')
                    ->first();

                $dataUltimoPagamento = null;
                $proximaCobranca = null;
                $recorrenciaStatus = null;

                if ($ultimaVenda) {
                    $ultimoPagoConfirmado = $ultimaVenda->pagamentos()
                        ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'pago'])
                        ->orderByDesc('data_pagamento')
                        ->first();

                    if ($ultimoPagoConfirmado && $ultimoPagoConfirmado->data_pagamento) {
                        $dataUltimoPagamento = $ultimoPagoConfirmado->data_pagamento->format('Y-m-d');
                    }

                    $proxCobranca = $ultimaVenda->pagamentos()
                        ->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'pago', 'CANCELED', 'DELETED'])
                        ->orderBy('data_vencimento')
                        ->first();

                    if ($proxCobranca && $proxCobranca->data_vencimento) {
                        $proximaCobranca = $proxCobranca->data_vencimento->format('Y-m-d');
                    }

                    $recorrenciaStatus = $ultimaVenda->status;
                }

                $cliente->status = $novoStatus;
                $cliente->data_ultimo_pagamento = $dataUltimoPagamento;
                $cliente->proxima_cobranca = $proximaCobranca;
                $cliente->recorrencia_status = $recorrenciaStatus;
                $cliente->save();
            }
        } catch (\Exception $e) {
            Log::warning('ClienteStatusService: Erro ao sincronizar status', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualiza o status de um único cliente.
     * Agora usa calcularStatusViaAsaas() quando possível.
     */
    public static function atualizarCliente(Cliente $cliente): void
    {
        // Se tem asaas_customer_id, usar método via Asaas (fonte de verdade)
        if (!empty($cliente->asaas_customer_id)) {
            $resultado = self::calcularStatusViaAsaas($cliente);
            self::aplicarStatusAsaas($cliente, $resultado);
            return;
        }

        // Fallback: lógica local
        $cliente->load('vendas.pagamentos');
        $novoStatus = self::calcularStatusLocal($cliente);

        $ultimaVenda = $cliente->vendas()
            ->with('pagamentos')
            ->orderByDesc('created_at')
            ->first();

        $dataUltimoPagamento = null;
        $proximaCobranca = null;
        $recorrenciaStatus = null;

        if ($ultimaVenda) {
            $ultimoPagoConfirmado = $ultimaVenda->pagamentos()
                ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'pago'])
                ->orderByDesc('data_pagamento')
                ->first();

            if ($ultimoPagoConfirmado && $ultimoPagoConfirmado->data_pagamento) {
                $dataUltimoPagamento = $ultimoPagoConfirmado->data_pagamento->format('Y-m-d');
            }

            $proxCobranca = $ultimaVenda->pagamentos()
                ->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'pago', 'CANCELED', 'DELETED'])
                ->orderBy('data_vencimento')
                ->first();

            if ($proxCobranca && $proxCobranca->data_vencimento) {
                $proximaCobranca = $proxCobranca->data_vencimento->format('Y-m-d');
            }

            $recorrenciaStatus = $ultimaVenda->status;
        }

        $cliente->status = $novoStatus;
        $cliente->data_ultimo_pagamento = $dataUltimoPagamento;
        $cliente->proxima_cobranca = $proximaCobranca;
        $cliente->recorrencia_status = $recorrenciaStatus;
        $cliente->save();
    }
}
