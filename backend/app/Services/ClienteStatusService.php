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
            $inicio = now()->subMonthsNoOverflow(1)->startOfMonth();
            $fim = now()->endOfMonth();

            // ══════════════════════════════════════════════════════════════
            // ETAPA 1: Puxar assinaturas e pagamentos da API
            // ══════════════════════════════════════════════════════════════
            $pagamentos = $asaas->getPaymentsByCustomer(
                $cliente->asaas_customer_id, $inicio, $fim
            );

            $assinaturas = $asaas->getSubscriptionsByCustomer(
                $cliente->asaas_customer_id, true
            );

            // ══════════════════════════════════════════════════════════════
            // ETAPA 2: Sincronizar localmente
            // ══════════════════════════════════════════════════════════════
            $resultado['pagamentos_sincronizados'] = self::sincronizarPagamentosLocais($cliente, $pagamentos);

            // ══════════════════════════════════════════════════════════════
            // ETAPA 3: Avaliar status cruzando os dados
            // ══════════════════════════════════════════════════════════════
            $assinaturaAtiva = collect($assinaturas)
                ->contains(fn ($a) => strtoupper($a['status'] ?? '') === 'ACTIVE' && empty($a['deleted']));

            $pagamentosValidos = collect($pagamentos)->filter(
                fn ($p) => in_array(strtoupper($p['status'] ?? ''), ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'])
                    && empty($p['deleted'])
                    && empty($p['refunded'])
            );

            $temChargeback = collect($pagamentos)->contains(
                fn ($p) => in_array(strtoupper($p['status'] ?? ''), ['CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE'])
            );

            $temVencidoNaAssinaturaAtiva = collect($pagamentos)->contains(
                fn ($p) => strtoupper($p['status'] ?? '') === 'OVERDUE' && empty($p['deleted'])
            );

            // Atraso maior que 30 dias para OVERDUE
            $atrasoMaiorQue30Dias = false;
            if ($temVencidoNaAssinaturaAtiva) {
                $ultimoVencido = collect($pagamentos)
                    ->filter(fn ($p) => strtoupper($p['status'] ?? '') === 'OVERDUE' && empty($p['deleted']))
                    ->sortByDesc('dueDate')
                    ->first();

                if ($ultimoVencido && !empty($ultimoVencido['dueDate'])) {
                    $diasAtraso = Carbon::parse($ultimoVencido['dueDate'])->diffInDays(now(), false);
                    if ($diasAtraso > 30) {
                        $atrasoMaiorQue30Dias = true;
                    }
                }
            }

            // Aplicar precedência rígida
            $statusEncontrado = match (true) {
                $temChargeback => 'cancelado', // O bd suporta cancelado/ativo/pendente/inadimplente/churn
                $temVencidoNaAssinaturaAtiva && $atrasoMaiorQue30Dias => 'churn',
                $temVencidoNaAssinaturaAtiva => 'inadimplente',
                $assinaturaAtiva && $pagamentosValidos->isEmpty() => 'pendente', // pendente_primeiro_pagamento vira pendente
                $assinaturaAtiva && $pagamentosValidos->isNotEmpty() => 'ativo',
                default => 'cancelado', // inativo vira cancelado
            };

            $resultado['status'] = $statusEncontrado;

            // ══════════════════════════════════════════════════════════════
            // ETAPA 4: Extrair datas auxiliares
            // ══════════════════════════════════════════════════════════════
            $ultimoPago = $pagamentosValidos->sortByDesc('dueDate')->first();
            if ($ultimoPago) {
                $resultado['data_ultimo_pagamento'] = $ultimoPago['clientPaymentDate']
                    ?? $ultimoPago['paymentDate']
                    ?? $ultimoPago['confirmedDate']
                    ?? null;
            }

            $pendentesFuturos = collect($pagamentos)->filter(fn ($p) => in_array(strtoupper($p['status'] ?? ''), ['PENDING', 'AWAITING_RISK_ANALYSIS']));
            $vencidos = collect($pagamentos)->filter(fn ($p) => strtoupper($p['status'] ?? '') === 'OVERDUE');

            $proximaPendente = $pendentesFuturos->merge($vencidos)->sortBy('dueDate')->first();
            if ($proximaPendente) {
                $resultado['proxima_cobranca'] = $proximaPendente['dueDate'] ?? null;
            }

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
