<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use App\Models\Vendedor;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AsaasClienteSyncController extends Controller
{
    protected AsaasService $asaas;

    public function __construct()
    {
        $this->asaas = new AsaasService();
    }

    /**
     * Lista todos os clientes importados do Asaas com filtros
     */
    public function index(Request $request)
    {
        $vendedores = Vendedor::where('status', 'ativo')->with('user')->get();

        $query = DB::table('legacy_customer_imports as lci')
            ->leftJoin('vendedores as v', 'lci.vendedor_id', '=', 'v.id')
            ->leftJoin('users as u', 'v.usuario_id', '=', 'u.id')
            ->select(
                'lci.*',
                'u.name as vendedor_nome'
            )
            ->orderBy('lci.nome');

        // Filtros
        if ($request->filled('vendedor_id')) {
            if ($request->vendedor_id === 'sem_vendedor') {
                $query->whereNull('lci.vendedor_id');
            } else {
                $query->where('lci.vendedor_id', $request->vendedor_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('lci.customer_status', $request->status);
        }

        if ($request->filled('tipo_comissao')) {
            $query->where('lci.comissao_tipo', $request->tipo_comissao);
        }

        if ($request->filled('tipo_cobranca')) {
            $query->where('lci.tipo_cobranca', $request->tipo_cobranca);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('lci.nome', 'like', $search)
                  ->orWhere('lci.documento', 'like', $search)
                  ->orWhere('lci.email', 'like', $search);
            });
        }

        $clientes = $query->paginate(50)->withQueryString();

        // Totais
        $totais = DB::table('legacy_customer_imports')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN vendedor_id IS NOT NULL THEN 1 END) as com_vendedor,
                COUNT(CASE WHEN vendedor_id IS NULL THEN 1 END) as sem_vendedor,
                SUM(comissao_vendedor_calculada) as total_comissao_vendedor,
                SUM(comissao_gestor_calculada) as total_comissao_gestor,
                COUNT(CASE WHEN customer_status = "ACTIVE" THEN 1 END) as ativos,
                COUNT(CASE WHEN customer_status = "OVERDUE" THEN 1 END) as vencidos,
                COUNT(CASE WHEN tipo_cobranca = "installment" THEN 1 END) as parcelados
            ')
            ->first();

        // Identificar duplicatas (mesmo documento aparece mais de uma vez)
        $dupCpfs = DB::table('legacy_customer_imports')
            ->select('documento')
            ->whereNotNull('documento')
            ->where('documento', '!=', '')
            ->groupBy('documento')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('documento')
            ->toArray();

        $ultimaSincronizacao = DB::table('legacy_customer_imports')
            ->max('asaas_synced_at');

        return view('master.clientes_asaas.index', compact(
            'clientes', 'vendedores', 'totais', 'dupCpfs', 'ultimaSincronizacao'
        ));
    }

    /**
     * Sincroniza clientes do Asaas (paginado, inclui duplicatas)
     */
    public function sincronizar(Request $request)
    {
        ini_set('max_execution_time', 300);

        try {
            $offset = 0;
            $limit = 100;
            $totalSinc = 0;
            $medMes = '2026-03'; // Mês de referência fixo para este ciclo

            do {
                $response = $this->asaas->requestAsaas('GET', '/customers', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

                $customers = $response['data'] ?? [];
                if (empty($customers)) break;

                foreach ($customers as $customer) {
                    $this->processarCliente($customer, $medMes);
                    $totalSinc++;
                }

                $offset += $limit;
                $hasMore = $response['hasMore'] ?? false;

            } while ($hasMore);

            return response()->json([
                'success' => true,
                'message' => "✅ Sincronizado com sucesso! {$totalSinc} clientes importados/atualizados.",
                'total'   => $totalSinc,
            ]);

        } catch (\Exception $e) {
            Log::error('AsaasSync: erro na sincronização', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '❌ Erro na sincronização: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Processa cada cliente do Asaas e salva no banco (incluindo duplicatas como linhas separadas)
     */
    private function processarCliente(array $customer, string $mesMes): void
    {
        $asaasId = $customer['id'];
        $now = now();

        // Buscar assinaturas do cliente
        $subscriptions = $this->getClienteSubscriptions($asaasId);

        // Buscar cobranças avulsas (não vinculadas a assinatura)
        $allPayments = $this->getClientePayments($asaasId);

        // Se tem assinaturas, processar cada uma como uma entrada separada
        if (!empty($subscriptions)) {
            foreach ($subscriptions as $sub) {
                $this->salvarEntradaImport($customer, $sub, [], $mesMes, 'subscription', $now);
            }
        }

        // Cobranças com parcelamento (installment) — não vinculadas a subscription
        $installmentGroups = [];
        $avulsos = [];

        foreach ($allPayments as $payment) {
            if (!empty($payment['installment'])) {
                $installmentGroups[$payment['installment']][] = $payment;
            } elseif (empty($payment['subscription'])) {
                $avulsos[] = $payment;
            }
        }

        // Processar cada grupo de parcelamento
        foreach ($installmentGroups as $installmentId => $parcelas) {
            $this->salvarEntradaImport($customer, null, $parcelas, $mesMes, 'installment', $now);
        }

        // Cobranças avulsas (sem assinatura e sem parcelamento agrupado)
        if (!empty($avulsos) && empty($subscriptions) && empty($installmentGroups)) {
            $this->salvarEntradaImport($customer, null, $avulsos, $mesMes, 'avulso', $now);
        }

        // Se não tem NADA — criar entrada vazia do cliente para visibilidade
        if (empty($subscriptions) && empty($installmentGroups) && empty($avulsos)) {
            $this->salvarEntradaImport($customer, null, [], $mesMes, 'avulso', $now);
        }
    }

    /**
     * Salva ou atualiza uma entrada na tabela de importação
     */
    private function salvarEntradaImport(
        array $customer,
        ?array $subscription,
        array $payments,
        string $mesMes,
        string $tipoCobranca,
        $now
    ): void {
        $asaasId      = $customer['id'];
        $subId        = $subscription['id'] ?? null;
        $installmentId = !empty($payments) ? ($payments[0]['installment'] ?? null) : null;

        // Chave única para identificar este registro específico
        // (cliente pode ter múltiplas assinaturas = múltiplas linhas)
        $existingQuery = DB::table('legacy_customer_imports')
            ->where('asaas_customer_id', $asaasId);

        if ($subId) {
            $existingQuery->where('asaas_subscription_id', $subId);
        } elseif ($installmentId) {
            $existingQuery->where('asaas_subscription_id', $installmentId);
        } else {
            $existingQuery->whereNull('asaas_subscription_id');
        }

        $existing = $existingQuery->first();

        // --- Calcular dados das parcelas ---
        $parcelasTotal = 1;
        $parcelasPagas = 0;
        $valorPlanoMensal = null;
        $valorTotalCobranca = null;
        $valorMarcoPago = null;
        $primeiroPagamentoAt = null;
        $ultimoPagamentoAt = null;

        if ($subscription) {
            $valorPlanoMensal  = $subscription['value'] ?? null;
            $valorTotalCobranca = $subscription['value'] ?? null;

            // Buscar pagamentos da assinatura para determinar datas
            $subPayments = $this->getSubscriptionPayments($subId);

            $pagosConfirmados = array_filter($subPayments, fn($p) =>
                in_array($p['status'] ?? '', ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'])
            );

            if (!empty($pagosConfirmados)) {
                $datas = array_column($pagosConfirmados, 'paymentDate');
                sort($datas);
                $primeiroPagamentoAt = $datas[0] ?? null;
                $ultimoPagamentoAt   = end($datas);
                $parcelasPagas       = count($pagosConfirmados);

                // Pago em Março/2026
                $pagoMarco = array_filter($pagosConfirmados, fn($p) =>
                    str_starts_with($p['paymentDate'] ?? '', '2026-03')
                );
                $valorMarcoPago = !empty($pagoMarco) ? array_sum(array_column($pagoMarco, 'value')) : null;
            }

        } elseif (!empty($payments)) {
            // Parcelamento ou avulso
            $parcelasTotal = count($payments);

            $pagosConfirmados = array_filter($payments, fn($p) =>
                in_array($p['status'] ?? '', ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'])
            );
            $parcelasPagas = count($pagosConfirmados);

            if (!empty($pagosConfirmados)) {
                $datasAsc = $pagosConfirmados;
                usort($datasAsc, fn($a, $b) => strcmp($a['paymentDate'] ?? '', $b['paymentDate'] ?? ''));
                $primeiroPagamentoAt = $datasAsc[0]['paymentDate'] ?? null;
                $ultimoPagamentoAt   = end($datasAsc)['paymentDate'] ?? null;
            }

            // Valor de cada parcela
            $valorPlanoMensal = !empty($payments) ? ($payments[0]['value'] ?? null) : null;
            $valorTotalCobranca = array_sum(array_column($payments, 'value'));

            // Pago em Março/2026
            $pagoMarco = array_filter($pagosConfirmados, fn($p) =>
                str_starts_with($p['paymentDate'] ?? '', '2026-03')
            );
            $valorMarcoPago = !empty($pagoMarco) ? array_sum(array_column($pagoMarco, 'value')) : null;
        }

        // --- Determinar tipo de comissão ---
        $comissaoTipo = null;
        if ($primeiroPagamentoAt) {
            $isPrimeiroPagamentoMarco = str_starts_with($primeiroPagamentoAt, '2026-03');
            if ($isPrimeiroPagamentoMarco && $tipoCobranca === 'installment') {
                $comissaoTipo = 'inicial_antecipada'; // Parcelamento criado em marco = antecipa tudo
            } elseif ($isPrimeiroPagamentoMarco) {
                $comissaoTipo = 'inicial';
            } else {
                $comissaoTipo = 'recorrencia';
            }
        }

        // Status da assinatura
        $subStatus = 'NONE';
        if ($subscription) {
            $subStatus = match(strtoupper($subscription['status'] ?? '')) {
                'ACTIVE'    => 'ACTIVE',
                'INACTIVE'  => 'INACTIVE',
                'CANCELLED', 'CANCELED' => 'CANCELLED',
                'EXPIRED'   => 'INACTIVE',
                default     => 'NONE',
            };
        } elseif ($tipoCobranca === 'installment') {
            $subStatus = $parcelasPagas >= $parcelasTotal ? 'INACTIVE' : 'ACTIVE';
        }

        $customerStatus = match(strtolower($customer['personType'] ?? '')) {
            default => 'ACTIVE',
        };
        if (!empty($customer['deleted']) && $customer['deleted']) {
            $customerStatus = 'INACTIVE';
        }

        $data = [
            'asaas_customer_id'              => $asaasId,
            'asaas_subscription_id'          => $subId ?? $installmentId,
            'asaas_subscription_status'      => $subscription['status'] ?? null,
            'asaas_subscription_billing_type'=> $subscription['billingType'] ?? ($payments[0]['billingType'] ?? null),
            'asaas_customer_data'            => json_encode($customer),
            'nome'                           => $customer['name'] ?? null,
            'documento'                      => preg_replace('/\D/', '', $customer['cpfCnpj'] ?? ''),
            'email'                          => $customer['email'] ?? null,
            'telefone'                       => $customer['phone'] ?? $customer['mobilePhone'] ?? null,
            'tipo_cobranca'                  => $tipoCobranca,
            'parcelas_total'                 => $parcelasTotal,
            'parcelas_pagas'                 => $parcelasPagas,
            'primeiro_pagamento_at'          => $primeiroPagamentoAt,
            'ultimo_pagamento_at'            => $ultimoPagamentoAt,
            'valor_plano_mensal'             => $valorPlanoMensal,
            'valor_total_cobranca'           => $valorTotalCobranca,
            'valor_marco_pago'               => $valorMarcoPago,
            'comissao_tipo'                  => $comissaoTipo,
            'customer_status'                => $customerStatus,
            'subscription_status'            => $subStatus,
            'import_status'                  => 'IMPORTED',
            'asaas_synced_at'                => $now,
            'asaas_sync_error'               => null,
        ];

        if ($existing) {
            // Preservar atribuição de vendedor e comissão calculada existente
            $preservar = [
                'vendedor_id', 'comissao_vendedor_calculada', 'comissao_gestor_calculada',
                'comissao_mes_referencia', 'comissao_resetada_em'
            ];
            foreach ($preservar as $campo) {
                unset($data[$campo]);
            }
            DB::table('legacy_customer_imports')->where('id', $existing->id)->update($data);
        } else {
            $data['comissao_vendedor_calculada'] = 0;
            $data['comissao_gestor_calculada']   = 0;
            $data['comissao_mes_referencia']     = null;
            DB::table('legacy_customer_imports')->insert($data + ['created_at' => $now, 'updated_at' => $now]);
        }
    }

    /**
     * Atribuir vendedor a um cliente importado e RECALCULAR comissão
     */
    public function atribuirVendedor(Request $request, int $id)
    {
        $request->validate([
            'vendedor_id' => 'nullable|exists:vendedores,id',
        ]);

        $import = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$import) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado'], 404);
        }

        $vendedorId = $request->vendedor_id;
        $comissaoVendedor = 0;
        $comissaoGestor = 0;
        $mesRef = '2026-03';

        if ($vendedorId) {
            $vendedor = Vendedor::with('user')->find($vendedorId);
            if ($vendedor && !is_null($import->comissao_tipo)) {
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
            }
        }

        DB::table('legacy_customer_imports')->where('id', $id)->update([
            'vendedor_id'                 => $vendedorId,
            'comissao_vendedor_calculada' => $comissaoVendedor,
            'comissao_gestor_calculada'   => $comissaoGestor,
            'comissao_mes_referencia'     => $vendedorId ? $mesRef : null,
            'updated_at'                  => now(),
        ]);

        return response()->json([
            'success'           => true,
            'comissao_vendedor' => 'R$ ' . number_format($comissaoVendedor, 2, ',', '.'),
            'comissao_gestor'   => 'R$ ' . number_format($comissaoGestor, 2, ',', '.'),
            'tipo'              => $import->comissao_tipo,
        ]);
    }

    /**
     * Calcula comissão do vendedor baseado nas regras de Março/2026
     */
    private function calcularComissao(object $import, Vendedor $vendedor): array
    {
        $comissaoVendedor = 0;
        $comissaoGestor = 0;

        $percInicial     = (float) ($vendedor->comissao_inicial ?? 0);
        $percRecorrencia = (float) ($vendedor->comissao_recorrencia ?? 0);
        $percGestorInicial     = (float) ($vendedor->comissao_gestor_primeira ?? 0);
        $percGestorRecorrencia = (float) ($vendedor->comissao_gestor_recorrencia ?? 0);

        $valorBase = (float) ($import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0);
        $valorPlano = (float) ($import->valor_plano_mensal ?? 0);
        $parcelasTotal = (int) ($import->parcelas_total ?? 1);
        $parcelasPagas = (int) ($import->parcelas_pagas ?? 0);

        switch ($import->comissao_tipo) {
            case 'inicial':
                // Assinatura criada em Março — apenas 1ª comissão
                $comissaoVendedor = $valorBase * ($percInicial / 100);
                $comissaoGestor   = $valorBase * ($percGestorInicial / 100);
                break;

            case 'inicial_antecipada':
                // Parcelamento criado em Março — antecipa TUDO
                // Comissão inicial sobre o valor do plano (1ª parcela)
                $comVendedorInicial = $valorPlano * ($percInicial / 100);
                $comGestorInicial   = $valorPlano * ($percGestorInicial / 100);

                // Antecipa recorrência para as parcelas restantes
                $parcelasRestantes = max(0, $parcelasTotal - $parcelasPagas); // restantes a pagar
                $comVendedorRec = $valorPlano * ($percRecorrencia / 100) * $parcelasRestantes;
                $comGestorRec   = $valorPlano * ($percGestorRecorrencia / 100) * $parcelasRestantes;

                $comissaoVendedor = $comVendedorInicial + $comVendedorRec;
                $comissaoGestor   = $comGestorInicial + $comGestorRec;
                break;

            case 'recorrencia':
                // Assinatura/cobrança recorrente — apenas comissão de recorrência
                $comissaoVendedor = $valorBase * ($percRecorrencia / 100);
                $comissaoGestor   = $valorBase * ($percGestorRecorrencia / 100);
                break;
        }

        return [round($comissaoVendedor, 2), round($comissaoGestor, 2)];
    }

    /**
     * Buscar assinaturas de um cliente no Asaas
     */
    private function getClienteSubscriptions(string $customerId): array
    {
        try {
            $response = $this->asaas->requestAsaas('GET', '/subscriptions', [
                'customer' => $customerId,
                'limit'    => 100,
            ]);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning("AsaasSync: erro ao buscar subscriptions de {$customerId}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Buscar pagamentos de um cliente no Asaas
     */
    private function getClientePayments(string $customerId): array
    {
        try {
            $response = $this->asaas->requestAsaas('GET', '/payments', [
                'customer' => $customerId,
                'limit'    => 100,
            ]);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning("AsaasSync: erro ao buscar payments de {$customerId}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Buscar pagamentos de uma assinatura específica
     */
    private function getSubscriptionPayments(string $subscriptionId): array
    {
        try {
            $response = $this->asaas->requestAsaas('GET', '/payments', [
                'subscription' => $subscriptionId,
                'limit'        => 100,
            ]);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
