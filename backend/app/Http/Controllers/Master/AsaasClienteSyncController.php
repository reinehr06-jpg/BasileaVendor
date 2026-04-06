<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AsaasClienteSyncController extends Controller
{
    // Status de pagamento que indicam pagamento confirmado
    const PAGAMENTOS_CONFIRMADOS = ['CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH'];
    // Status que indicam pagamento pendente/em aberto
    const PAGAMENTOS_PENDENTES = ['PENDING', 'OVERDUE', 'AWAITING_RISK_ANALYSIS'];
    // Mês de referência para comissões (dinâmico - usa o mês atual)
    const MES_REFERENCIA = null; // null = usa mês atual

    protected AsaasService $asaas;

    public function __construct()
    {
        $this->asaas = new AsaasService();
    }

    private function getMesReferencia(): string
    {
        return self::MES_REFERENCIA ?? now()->format('Y-m');
    }

    // ──────────────────────────────────────────────────────────────
    // LISTAGEM PRINCIPAL COM ABAS
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendedores = Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get();
        $aba        = $request->get('aba', 'todos');

        $base = DB::table('legacy_customer_imports as lci')
            ->leftJoin('vendedores as v', 'lci.vendedor_id', '=', 'v.id')
            ->leftJoin('users as u', 'v.usuario_id', '=', 'u.id')
            ->select('lci.*', 'u.name as vendedor_nome');

        // Filtros de aba
        $base = match($aba) {
            'ativos'      => $base->where('lci.diagnostico_status', 'ATIVO'),
            'churn'       => $base->where('lci.diagnostico_status', 'CHURN'),
            'cancelados'  => $base->where('lci.diagnostico_status', 'CANCELADO'),
            'sem_vendedor'=> $base->whereNull('lci.vendedor_id'),
            default       => $base, // todos
        };

        // Filtros adicionais
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $base->where(fn($q) => $q
                ->where('lci.nome', 'like', $s)
                ->orWhere('lci.documento', 'like', $s)
                ->orWhere('lci.email', 'like', $s)
            );
        }
        if ($request->filled('vendedor_id')) {
            $request->vendedor_id === 'sem_vendedor'
                ? $base->whereNull('lci.vendedor_id')
                : $base->where('lci.vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('tipo_cobranca')) {
            $base->where('lci.tipo_cobranca', $request->tipo_cobranca);
        }

        $clientes = $base->orderBy('lci.nome')->paginate(50)->withQueryString();

        // KPIs
        $totais = DB::table('legacy_customer_imports')->selectRaw("
            COUNT(*)                                                          as total,
            COUNT(CASE WHEN diagnostico_status = 'ATIVO'     THEN 1 END)     as ativos,
            COUNT(CASE WHEN diagnostico_status = 'CHURN'     THEN 1 END)     as churn,
            COUNT(CASE WHEN diagnostico_status = 'CANCELADO' THEN 1 END)     as cancelados,
            COUNT(CASE WHEN vendedor_id IS NULL               THEN 1 END)     as sem_vendedor,
            COUNT(CASE WHEN tipo_cobranca = 'installment'    THEN 1 END)     as parcelados,
            SUM(comissao_vendedor_calculada)                                  as total_comissao_vendedor,
            SUM(comissao_gestor_calculada)                                    as total_comissao_gestor
        ")->first();

        // Duplicatas por CPF
        $dupCpfs = DB::table('legacy_customer_imports')
            ->select('documento')
            ->whereNotNull('documento')->where('documento', '!=', '')
            ->groupBy('documento')->havingRaw('COUNT(*) > 1')
            ->pluck('documento')->toArray();

        $ultimaSincronizacao = DB::table('legacy_customer_imports')->max('asaas_synced_at');

        return view('master.clientes_asaas.index', compact(
            'clientes', 'vendedores', 'totais', 'dupCpfs', 'ultimaSincronizacao', 'aba'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // EXIBIR DETALHES DE UM CLIENTE
    // ──────────────────────────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $cliente = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$cliente) {
            return redirect()->route('master.clientes-asaas.index')->with('error', 'Cliente não encontrado.');
        }

        $vendedores = Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get();
        // Separando Gestores e Vendedores
        $listaG = collect();
        $listaV = collect();
        foreach($vendedores as $v) {
            $r = strtolower($v->role ?? ($v->user->role ?? ''));
            if(str_contains($r, 'gestor') || str_contains($r, 'master')) $listaG->push($v);
            else $listaV->push($v);
        }

        return view('master.clientes_asaas.show', compact('cliente', 'listaG', 'listaV'));
    }

    // ──────────────────────────────────────────────────────────────
    // SINCRONIZAÇÃO — PAGINADA COM PROGRESS VIA SSE
    // ──────────────────────────────────────────────────────────────

    public function sincronizar(Request $request)
    {
        // Trava de execução: max 10 min
        set_time_limit(600);
        ini_set('max_execution_time', 600);

        $offset    = 0;
        $limit     = 100;
        $totalSinc = 0;
        $erros     = 0;

        try {
            do {
                $response  = $this->asaas->requestAsaas('GET', '/customers', [
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);
                $customers = $response['data'] ?? [];
                if (empty($customers)) break;

                foreach ($customers as $customer) {
                    try {
                        $this->processarCliente($customer);
                        $totalSinc++;
                    } catch (\Exception $e) {
                        $erros++;
                        Log::warning("AsaasSync: falha no cliente {$customer['id']}", ['error' => $e->getMessage()]);
                    }
                    // Delay para não saturar a API do Asaas (~3 req/cliente)
                    usleep(300_000); // 300ms
                }

                $offset  += $limit;
                $hasMore  = $response['hasMore'] ?? false;

            } while ($hasMore);

            return response()->json([
                'success' => true,
                'message' => "✅ Sincronizado! {$totalSinc} clientes processados." . ($erros > 0 ? " ({$erros} com erro)" : ''),
                'total'   => $totalSinc,
                'erros'   => $erros,
            ]);

        } catch (\Exception $e) {
            Log::error('AsaasSync: erro geral', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '❌ Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PROCESSAMENTO DE CADA CLIENTE
    // ──────────────────────────────────────────────────────────────

    private function processarCliente(array $customer): void
    {
        $now      = now();
        $asaasId  = $customer['id'];

        // 1. Buscar TODOS os pagamentos do cliente
        $allPayments = $this->getClientePayments($asaasId);

        // 2. Buscar assinaturas
        $subscriptions = $this->getClienteSubscriptions($asaasId);

        // 3. Analisar histórico de pagamentos
        $confirmados = array_filter($allPayments, fn($p) =>
            in_array($p['status'] ?? '', self::PAGAMENTOS_CONFIRMADOS)
        );
        $pendentes = array_filter($allPayments, fn($p) =>
            in_array($p['status'] ?? '', self::PAGAMENTOS_PENDENTES)
        );

        $temConfirmado = !empty($confirmados);
        $temPendente   = !empty($pendentes);

        // 4. Determinar diagnóstico de status
        $subscriptionCancelada = !empty($subscriptions) && collect($subscriptions)->every(fn($s) =>
            in_array(strtoupper($s['status'] ?? ''), ['CANCELLED', 'CANCELED', 'EXPIRED'])
        );

        if ($subscriptionCancelada && !$temConfirmado) {
            $diagnostico = 'CANCELADO';
        } elseif (!$temConfirmado && $temPendente) {
            // Nunca pagou, só tem pendentes
            $diagnostico = 'CANCELADO';
        } elseif ($temConfirmado && $temPendente) {
            // Já pagou antes, mas tem pendente atual
            $diagnostico = 'CHURN';
        } elseif ($temConfirmado && !$temPendente) {
            $diagnostico = 'ATIVO';
        } else {
            $diagnostico = 'PENDENTE';
        }

        // 5. Datas de pagamento
        $datasConfirmadas = array_column(array_values($confirmados), 'paymentDate');
        sort($datasConfirmadas);
        $primeiroPgtAt         = !empty($datasConfirmadas) ? $datasConfirmadas[0] : null;
        $ultimoConfirmadoAt    = !empty($datasConfirmadas) ? end($datasConfirmadas) : null;

        // Próximo vencimento (menor data dos pendentes)
        $datasPendentes = array_filter(
            array_column(array_values($pendentes), 'dueDate'),
            fn($d) => !empty($d)
        );
        sort($datasPendentes);
        $proximoVencimento = !empty($datasPendentes) ? $datasPendentes[0] : null;

        // Dias sem pagar
        $diasSemPagar = 0;
        if ($ultimoConfirmadoAt) {
            $diasSemPagar = (int) Carbon::parse($ultimoConfirmadoAt)->diffInDays(now(), false);
            $diasSemPagar = max(0, $diasSemPagar);
        }

        // 6. Separar cobranças por tipo e agrupá-las
        $installmentGroups = [];
        $avulsos           = [];
        foreach ($allPayments as $p) {
            $instId = $p['installment'] ?? null;
            $subId  = $p['subscription'] ?? null;
            if ($instId) {
                $installmentGroups[$instId][] = $p;
            } elseif (!$subId) {
                $avulsos[] = $p;
            }
        }

        // 7. Salvar cada entrada
        if (!empty($subscriptions)) {
            foreach ($subscriptions as $sub) {
                $subPayments = array_filter($allPayments, fn($p) =>
                    ($p['subscription'] ?? null) === $sub['id']
                );
                $this->salvarEntrada($customer, $sub, array_values($subPayments), 'subscription', $diagnostico,
                    $primeiroPgtAt, $ultimoConfirmadoAt, $proximoVencimento, $diasSemPagar, $temConfirmado, $temPendente, $now);
            }
        }

        foreach ($installmentGroups as $instId => $parcelas) {
            $this->salvarEntrada($customer, null, $parcelas, 'installment', $diagnostico,
                $primeiroPgtAt, $ultimoConfirmadoAt, $proximoVencimento, $diasSemPagar, $temConfirmado, $temPendente, $now);
        }

        if (!empty($avulsos) && empty($subscriptions) && empty($installmentGroups)) {
            $this->salvarEntrada($customer, null, $avulsos, 'avulso', $diagnostico,
                $primeiroPgtAt, $ultimoConfirmadoAt, $proximoVencimento, $diasSemPagar, $temConfirmado, $temPendente, $now);
        }

        if (empty($subscriptions) && empty($installmentGroups) && empty($avulsos)) {
            $this->salvarEntrada($customer, null, [], 'avulso', $diagnostico,
                $primeiroPgtAt, $ultimoConfirmadoAt, $proximoVencimento, $diasSemPagar, $temConfirmado, $temPendente, $now);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SALVAR / ATUALIZAR ENTRADA NA TABELA
    // ──────────────────────────────────────────────────────────────

    private function salvarEntrada(
        array  $customer,
        ?array $subscription,
        array  $payments,
        string $tipoCobranca,
        string $diagnostico,
        ?string $primeiroPgtAt,
        ?string $ultimoConfirmadoAt,
        ?string $proximoVencimento,
        int    $diasSemPagar,
        bool   $temConfirmado,
        bool   $temPendente,
        $now
    ): void {
        $asaasId    = $customer['id'];
        $subId      = $subscription['id'] ?? null;
        $installId  = !empty($payments) ? ($payments[0]['installment'] ?? null) : null;

        // Chave única: cliente + assinatura/installment
        $query = DB::table('legacy_customer_imports')->where('asaas_customer_id', $asaasId);
        if ($subId) {
            $query->where('asaas_subscription_id', $subId);
        } elseif ($installId) {
            $query->where('asaas_subscription_id', $installId);
        } else {
            $query->whereNull('asaas_subscription_id');
        }
        $existing = $query->first();

        // ── Dados financeiros ──
        $confirmadosDeste = array_filter($payments, fn($p) =>
            in_array($p['status'] ?? '', self::PAGAMENTOS_CONFIRMADOS)
        );

        $parcelasTotal  = 1;
        $parcelasPagas  = 0;
        $valorPlano     = null;
        $valorTotal     = null;
        $valorMarcoPago = null;

        if ($subscription) {
            $valorPlano = (float) ($subscription['value'] ?? 0);
            $valorTotal = $valorPlano;
            $parcelasPagas = count(array_filter($payments, fn($p) =>
                in_array($p['status'] ?? '', self::PAGAMENTOS_CONFIRMADOS)
            ));
            $mesRef = $this->getMesReferencia();
            // Valor pago no mês de referência
            $pagoMarco = array_filter($confirmadosDeste, fn($p) =>
                str_starts_with($p['paymentDate'] ?? '', $mesRef)
            );
            $valorMarcoPago = array_sum(array_column($pagoMarco, 'value')) ?: null;

        } elseif (!empty($payments)) {
            $parcelasTotal  = max(1, count($payments));
            $parcelasPagas  = count($confirmadosDeste);
            $valorPlano     = (float) ($payments[0]['value'] ?? 0);
            $valorTotal     = array_sum(array_column($payments, 'value'));

            $mesRef = $this->getMesReferencia();
            $pagoMarco = array_filter($confirmadosDeste, fn($p) =>
                str_starts_with($p['paymentDate'] ?? '', $mesRef)
            );
            $valorMarcoPago = array_sum(array_column($pagoMarco, 'value')) ?: null;
        }

        // ── Status da assinatura ──
        $subStatusAsaas = null;
        $subStatusLocal = 'NONE';
        if ($subscription) {
            $subStatusAsaas = $subscription['status'] ?? null;
            $subStatusLocal = match(strtoupper($subStatusAsaas ?? '')) {
                'ACTIVE'              => 'ACTIVE',
                'INACTIVE','EXPIRED'  => 'INACTIVE',
                'CANCELLED','CANCELED'=> 'CANCELLED',
                default               => 'NONE',
            };
        } elseif ($tipoCobranca === 'installment') {
            $subStatusLocal = $parcelasPagas >= $parcelasTotal ? 'INACTIVE' : 'ACTIVE';
        }

        // ── Tipo de comissão (só calcula se ATIVO e tem pagamento no mês de referência) ──
        $comissaoTipo = null;
        $mesRef = $this->getMesReferencia();
        if ($diagnostico === 'ATIVO' && $valorMarcoPago > 0 && $primeiroPgtAt) {
            $isPrimeiroEmMarco = str_starts_with($primeiroPgtAt, $mesRef);
            if ($tipoCobranca === 'installment' && $isPrimeiroEmMarco) {
                $comissaoTipo = 'inicial_antecipada';
            } elseif ($isPrimeiroEmMarco) {
                $comissaoTipo = 'inicial';
            } else {
                $comissaoTipo = 'recorrencia';
            }
        }

        $data = [
            'asaas_customer_id'               => $asaasId,
            'asaas_subscription_id'           => $subId ?? $installId,
            'asaas_subscription_status'       => $subStatusAsaas,
            'asaas_subscription_billing_type' => $subscription['billingType'] ?? ($payments[0]['billingType'] ?? null),
            'asaas_customer_data'             => json_encode($customer),
            'nome'                            => $customer['name'] ?? null,
            'documento'                       => preg_replace('/\D/', '', $customer['cpfCnpj'] ?? ''),
            'email'                           => $customer['email'] ?? null,
            'telefone'                        => $customer['phone'] ?? $customer['mobilePhone'] ?? null,
            'tipo_cobranca'                   => $tipoCobranca,
            'parcelas_total'                  => $parcelasTotal,
            'parcelas_pagas'                  => $parcelasPagas,
            'primeiro_pagamento_at'           => $primeiroPgtAt,
            'ultimo_pagamento_at'             => $ultimoConfirmadoAt,
            'ultimo_pagamento_confirmado_at'  => $ultimoConfirmadoAt,
            'proximo_vencimento_at'           => $proximoVencimento,
            'dias_sem_pagar'                  => $diasSemPagar,
            'valor_plano_mensal'              => $valorPlano,
            'valor_total_cobranca'            => $valorTotal,
            'valor_marco_pago'                => $valorMarcoPago,
            'comissao_tipo'                   => $comissaoTipo,
            'diagnostico_status'              => $diagnostico,
            'customer_status'                 => ($customer['deleted'] ?? false) ? 'INACTIVE' : 'ACTIVE',
            'subscription_status'             => $subStatusLocal,
            'tem_pagamento_confirmado'        => $temConfirmado,
            'tem_pagamento_pendente_atual'    => $temPendente,
            'import_status'                   => 'IMPORTED',
            'asaas_synced_at'                 => $now,
            'asaas_sync_error'                => null,
            'updated_at'                      => $now,
        ];

        if ($existing) {
            // Preservar atribuição de vendedor e comissão já calculada
            foreach (['vendedor_id', 'comissao_vendedor_calculada', 'comissao_gestor_calculada',
                      'comissao_mes_referencia', 'comissao_resetada_em',
                      'local_cliente_id', 'local_venda_id', 'confirmado_em', 'confirmado_por'] as $campo) {
                unset($data[$campo]);
            }
            DB::table('legacy_customer_imports')->where('id', $existing->id)->update($data);
        } else {
            $data['comissao_vendedor_calculada'] = 0;
            $data['comissao_gestor_calculada']   = 0;
            $data['comissao_mes_referencia']     = null;
            $data['vendedor_id']                 = null;
            $data['local_cliente_id']            = null;
            $data['local_venda_id']              = null;
            $data['created_at']                  = $now;
            DB::table('legacy_customer_imports')->insert($data);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // ATRIBUIR VENDEDOR + RECALCULAR COMISSÃO
    // ──────────────────────────────────────────────────────────────

    public function atribuirVendedor(Request $request, int $id)
    {
        $request->validate(['vendedor_id' => 'nullable|exists:vendedores,id']);

        $import = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$import) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado'], 404);
        }

        $vendedorId       = $request->vendedor_id;
        $comissaoVendedor = 0;
        $comissaoGestor   = 0;
        $mesRef           = $this->getMesReferencia();

        if ($vendedorId && $import->diagnostico_status === 'ATIVO' && $import->comissao_tipo) {
            $vendedor = Vendedor::with('user')->find($vendedorId);
            if ($vendedor) {
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
                
                // Criar registro de comissão
                $gestorId = $vendedor->gestor_id ?? $vendedor->usuario_id;
                if ($comissaoVendedor > 0 || $comissaoGestor > 0) {
                    Comissao::create([
                        'vendedor_id' => $vendedorId,
                        'gerente_id' => $gestorId,
                        'tipo_comissao' => $import->comissao_tipo ?? 'inicial',
                        'percentual_aplicado' => $vendedor->comissao_inicial ?? 0,
                        'percentual_gerente' => $vendedor->comissao_gestor_primeira ?? 0,
                        'valor_venda' => $import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0,
                        'valor_comissao' => $comissaoVendedor,
                        'valor_gerente' => $comissaoGestor,
                        'status' => 'pendente',
                        'competencia' => $mesRef,
                    ]);
                }
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
            'diagnostico'       => $import->diagnostico_status,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // ATRIBUIÇÃO EM MASSA
    // ──────────────────────────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'integer|exists:legacy_customer_imports,id',
            'vendedor_id' => 'required|exists:vendedores,id',
        ]);

        $vendedorId = $request->vendedor_id;
        $customerIds = $request->customer_ids;
        $mesRef = $this->getMesReferencia();

        $vendedor = Vendedor::with('user')->find($vendedorId);
        if (!$vendedor) {
            return response()->json(['success' => false, 'message' => 'Vendedor não encontrado'], 404);
        }

        $totalComissaoVendedor = 0;
        $totalComissaoGestor = 0;
        $atribuidos = 0;

        // Verificar se é gestor
        $isGestor = $vendedor->is_gestor ?? false;
        
        // Buscar o gestor do vendedor (se for vendedor, pega o gestor; se for gestor, usa ele mesmo)
        $gestorId = $vendedor->gestor_id ?? $vendedor->usuario_id;

        foreach ($customerIds as $customerId) {
            $import = DB::table('legacy_customer_imports')->where('id', $customerId)->first();
            
            if (!$import || $import->vendedor_id) {
                continue;
            }

            $comissaoVendedor = 0;
            $comissaoGestor = 0;

            if ($import->diagnostico_status === 'ATIVO' && $import->comissao_tipo) {
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
            }

            DB::table('legacy_customer_imports')->where('id', $customerId)->update([
                'vendedor_id' => $vendedorId,
                'comissao_vendedor_calculada' => $comissaoVendedor,
                'comissao_gestor_calculada' => $comissaoGestor,
                'comissao_mes_referencia' => $mesRef,
                'updated_at' => now(),
            ]);

            // Criar registro de comissão
            if ($isGestor) {
                // Gestor recebe apenas como vendedor (não recebe como gestor de si mesmo)
                if ($comissaoVendedor > 0) {
                    Comissao::create([
                        'vendedor_id' => $vendedorId,
                        'gerente_id' => null,
                        'tipo_comissao' => $import->comissao_tipo ?? 'inicial',
                        'percentual_aplicado' => $vendedor->comissao_inicial ?? 0,
                        'percentual_gerente' => 0,
                        'valor_venda' => $import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0,
                        'valor_comissao' => $comissaoVendedor,
                        'valor_gerente' => 0,
                        'status' => 'pendente',
                        'competencia' => $mesRef,
                    ]);
                }
            } else {
                // Se é vendedor normal, cria comissão para vendedor e gestor
                if ($comissaoVendedor > 0 || $comissaoGestor > 0) {
                    Comissao::create([
                        'vendedor_id' => $vendedorId,
                        'gerente_id' => $gestorId,
                        'tipo_comissao' => $import->comissao_tipo ?? 'inicial',
                        'percentual_aplicado' => $vendedor->comissao_inicial ?? 0,
                        'percentual_gerente' => $vendedor->comissao_gestor_primeira ?? 0,
                        'valor_venda' => $import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0,
                        'valor_comissao' => $comissaoVendedor,
                        'valor_gerente' => $comissaoGestor,
                        'status' => 'pendente',
                        'competencia' => $mesRef,
                    ]);
                }
            }

            // Criar cliente no sistema automaticamente (se ainda não existir)
            $doc = preg_replace('/\D/', '', $import->documento ?? '');
            $clienteExistente = DB::table('clientes')->where('documento', $doc)->first();
            
            if (!$clienteExistente && $doc) {
                $clienteId = DB::table('clientes')->insertGetId([
                    'nome'       => $import->nome,
                    'documento'  => $doc,
                    'contato'    => $import->telefone,
                    'whatsapp'   => $import->telefone,
                    'email'      => $import->email,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Vincular ao import
                DB::table('legacy_customer_imports')->where('id', $customerId)->update([
                    'local_cliente_id' => $clienteId,
                    'confirmado_em' => now(),
                ]);
            }

            $totalComissaoVendedor += $isGestor ? $comissaoGestor : $comissaoVendedor;
            $totalComissaoGestor += $comissaoGestor;
            $atribuidos++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$atribuidos} cliente(s) atribuído(s) com sucesso!",
            'atribuidos' => $atribuidos,
            'comissao_vendedor' => number_format($totalComissaoVendedor, 2, ',', '.'),
            'comissao_gestor' => number_format($totalComissaoGestor, 2, ',', '.'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // CONFIRMAR CLIENTE → cria em clientes + vendas do sistema
    // ──────────────────────────────────────────────────────────────

    public function confirmarCliente(Request $request, int $id)
    {
        $import = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$import) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado'], 404);
        }
        if (!$import->vendedor_id) {
            return response()->json(['success' => false, 'message' => 'Atribua um vendedor antes de confirmar.'], 422);
        }
        if ($import->local_cliente_id) {
            return response()->json(['success' => false, 'message' => 'Cliente já confirmado no sistema.'], 422);
        }

        $doc = preg_replace('/\D/', '', $import->documento ?? '');

        // Criar ou reutilizar cliente
        $cliente = DB::table('clientes')->where('documento', $doc)->first();
        if (!$cliente) {
            $clienteId = DB::table('clientes')->insertGetId([
                'nome'       => $import->nome,
                'documento'  => $doc,
                'contato'    => $import->telefone,
                'whatsapp'   => $import->telefone,
                'email'      => $import->email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $clienteId = $cliente->id;
        }

        // Determinar forma de pagamento e status da venda
        $billingType   = $import->asaas_subscription_billing_type ?? 'BOLETO';
        $formaPgto     = match(strtoupper($billingType)) {
            'CREDIT_CARD' => 'Cartão de Crédito',
            'PIX'         => 'PIX',
            default       => 'Boleto',
        };
        $statusVenda = match($import->diagnostico_status) {
            'ATIVO'     => 'Pago',
            'CHURN'     => 'Aguardando pagamento',
            default     => 'Cancelada',
        };
        $tipoNegociacao = match($import->tipo_cobranca) {
            'installment' => 'parcelado',
            'subscription'=> 'mensal',
            default       => 'avulso',
        };

        // Criar venda
        $vendaId = DB::table('vendas')->insertGetId([
            'cliente_id'       => $clienteId,
            'vendedor_id'      => $import->vendedor_id,
            'valor'            => $import->valor_plano_mensal ?? 0,
            'comissao_gerada'  => $import->comissao_vendedor_calculada ?? 0,
            'status'           => $statusVenda,
            'plano'            => null,
            'forma_pagamento'  => $formaPgto,
            'tipo_negociacao'  => $tipoNegociacao,
            'parcelas'         => $import->parcelas_total ?? 1,
            'origem'           => 'asaas_legado',
            'data_venda'       => $import->primeiro_pagamento_at ?? now()->toDateString(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Vincular import ao cliente e venda criados
        DB::table('legacy_customer_imports')->where('id', $id)->update([
            'local_cliente_id' => $clienteId,
            'local_venda_id'   => $vendaId,
            'confirmado_em'    => now(),
            'confirmado_por'   => auth()->id(),
            'updated_at'       => now(),
        ]);

        // Para clientes CHURN, atualizar status da venda gerada para "Aguardando pagamento"
        // A lógica de "Todas as Vendas" os exibirá automaticamente pelo status

        return response()->json([
            'success'    => true,
            'message'    => 'Cliente confirmado no sistema com sucesso!',
            'cliente_id' => $clienteId,
            'venda_id'   => $vendaId,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // CÁLCULO DE COMISSÃO
    // ──────────────────────────────────────────────────────────────

    private function calcularComissao(object $import, Vendedor $vendedor): array
    {
        $percIni    = (float) ($vendedor->comissao_inicial ?? 0);
        $percRec    = (float) ($vendedor->comissao_recorrencia ?? 0);
        $percGstIni = (float) ($vendedor->comissao_gestor_primeira ?? 0);
        $percGstRec = (float) ($vendedor->comissao_gestor_recorrencia ?? 0);

        $valorBase  = (float) ($import->valor_marco_pago ?? $import->valor_plano_mensal ?? 0);
        $valorPlano = (float) ($import->valor_plano_mensal ?? 0);
        $parcelasTotal = (int) ($import->parcelas_total ?? 1);
        $parcelasPagas = (int) ($import->parcelas_pagas ?? 0);

        $cv = 0.0;
        $cg = 0.0;

        switch ($import->comissao_tipo) {
            case 'inicial':
                // Assinatura/PIX/Boleto: primeiro pagamento em março
                $cv = $valorBase * ($percIni / 100);
                $cg = $valorBase * ($percGstIni / 100);
                break;

            case 'inicial_antecipada':
                // Parcelado criado em março — antecipa TODA a comissão
                // Comissão inicial sobre o valor da 1ª parcela
                $cv = $valorPlano * ($percIni / 100);
                $cg = $valorPlano * ($percGstIni / 100);
                // Recorrência antecipada para as parcelas restantes (total - pagas)
                $restantes = max(0, $parcelasTotal - $parcelasPagas);
                $cv += $valorPlano * ($percRec / 100) * $restantes;
                $cg += $valorPlano * ($percGstRec / 100) * $restantes;
                break;

            case 'recorrencia':
                // Assinatura recorrente — já pagava antes de março
                $cv = $valorBase * ($percRec / 100);
                $cg = $valorBase * ($percGstRec / 100);
                break;
        }

        return [round($cv, 2), round($cg, 2)];
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS — CHAMADAS À API ASAAS
    // ──────────────────────────────────────────────────────────────

    private function getClientePayments(string $customerId): array
    {
        try {
            $all      = [];
            $offset   = 0;
            do {
                $resp = $this->asaas->requestAsaas('GET', '/payments', [
                    'customer' => $customerId,
                    'limit'    => 100,
                    'offset'   => $offset,
                ]);
                $data = $resp['data'] ?? [];
                $all  = array_merge($all, $data);
                $offset += 100;
            } while ($resp['hasMore'] ?? false);
            return $all;
        } catch (\Exception $e) {
            Log::warning("AsaasSync: erro pagamentos de {$customerId}", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function getClienteSubscriptions(string $customerId): array
    {
        try {
            $resp = $this->asaas->requestAsaas('GET', '/subscriptions', [
                'customer' => $customerId,
                'limit'    => 100,
            ]);
            return $resp['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning("AsaasSync: erro subscriptions de {$customerId}", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
