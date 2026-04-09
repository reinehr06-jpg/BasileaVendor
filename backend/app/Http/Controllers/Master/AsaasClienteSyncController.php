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
    // AUDITORIA RETROATIVA
    // ──────────────────────────────────────────────────────────────
    public function auditoriaRetroativa(Request $request)
    {
        $vendedores = Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get();
        $vendedorId = $request->get('vendedor_id');
        
        $dadosTabela = []; // Mês => lista de clientes e comissões
        
        if ($vendedorId) {
            $vendedor = Vendedor::find($vendedorId);
            $clientes = DB::table('legacy_customer_imports')
                ->where('vendedor_id', $vendedorId)
                ->whereIn('diagnostico_status', ['ATIVO', 'CHURN', 'PENDENTE']) // Evitar cancelados se nao renderam
                ->get();
                
            $percIni = (float) ($vendedor->comissao_inicial ?? 0);
            $percRec = (float) ($vendedor->comissao_recorrencia ?? 0);

            $now = Carbon::now();

            foreach ($clientes as $c) {
                // Usar a data do primeiro pagamento ou created_at se faltar
                $dataRef = $c->primeiro_pagamento_at ?? $c->created_at;
                if (!$dataRef) continue;

                $start = Carbon::parse($dataRef)->startOfMonth();
                $end = $now->copy()->startOfMonth();

                $valorPlano = (float) ($c->valor_plano_mensal ?? 0);
                $parcelasTotal = (int) ($c->parcelas_total ?? 1);
                
                $mesAtual = $start->copy();
                $countMeses = 0;

                while ($mesAtual <= $end) {
                    $countMeses++;
                    $mesStr = $mesAtual->format('Y-m');
                    $cv = 0;

                    if ($c->comissao_tipo === 'inicial_antecipada') {
                        if ($countMeses === 1) {
                            $cv = $valorPlano * ($percIni / 100);
                            $restantes = max(0, $parcelasTotal - 1);
                            $cv += $valorPlano * ($percRec / 100) * $restantes;
                        }
                    } elseif ($c->comissao_tipo === 'inicial') {
                        if ($countMeses === 1) {
                            $cv = $valorPlano * ($percIni / 100);
                        } else {
                            if ($c->tipo_cobranca === 'installment' && $countMeses > $parcelasTotal) {
                                $cv = 0;
                            } else {
                                $cv = $valorPlano * ($percRec / 100);
                            }
                        }
                    } elseif ($c->comissao_tipo === 'recorrencia') {
                        if ($c->tipo_cobranca === 'installment' && $countMeses > $parcelasTotal) {
                            $cv = 0;
                        } else {
                            $cv = $valorPlano * ($percRec / 100);
                        }
                    }

                    if ($cv > 0) {
                        if (!isset($dadosTabela[$mesStr])) {
                            $dadosTabela[$mesStr] = [];
                        }
                        $dadosTabela[$mesStr][] = [
                            'cliente_nome' => $c->nome,
                            'cliente_doc' => $c->documento,
                            'data_inicio' => Carbon::parse($dataRef)->format('d/m/Y'),
                            'tipo' => $c->comissao_tipo,
                            'cobranca' => $c->tipo_cobranca,
                            'parcela_numero' => $countMeses,
                            'comissao_calculada' => $cv
                        ];
                    }

                    $mesAtual->addMonth();
                }
            }
            
            // Ordenar meses do mais recente para o mais antigo
            krsort($dadosTabela);
        }

        return view('master.clientes_asaas.auditoria', compact('vendedores', 'vendedorId', 'dadosTabela'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDITAR CLIENTE (FORMULÁRIO)
    // ──────────────────────────────────────────────────────────────
    public function edit(Request $request, $id)
    {
        $cliente = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$cliente) {
            return redirect()->route('master.clientes-asaas.index')->with('error', 'Cliente não encontrado.');
        }

        $vendedores = Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get();
        $listaG = collect();
        $listaV = collect();
        foreach($vendedores as $v) {
            $r = strtolower($v->role ?? ($v->user->role ?? ''));
            if(str_contains($r, 'gestor') || str_contains($r, 'master')) $listaG->push($v);
            else $listaV->push($v);
        }

        return view('master.clientes_asaas.edit', compact('cliente', 'listaG', 'listaV'));
    }

    // ──────────────────────────────────────────────────────────────
    // ATUALIZAR CLIENTE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $cliente = DB::table('legacy_customer_imports')->where('id', $id)->first();
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        $validated = $request->validate([
            'nome' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'documento' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'tipo_cobranca' => 'nullable|in:subscription,installment,avulso',
            'parcelas_total' => 'nullable|integer|min:1',
            'parcelas_pagas' => 'nullable|integer|min:0',
            'valor_plano_mensal' => 'nullable|numeric|min:0',
            'primeiro_pagamento_at' => 'nullable|date',
            'ultimo_pagamento_confirmado_at' => 'nullable|date',
            'proximo_vencimento_at' => 'nullable|date',
            'diagnostico_status' => 'nullable|in:ATIVO,CHURN,CANCELADO,PENDENTE',
            'comissao_tipo' => 'nullable|in:inicial,inicial_antecipada,recorrencia,sem_comissao',
            'valor_total_cobranca' => 'nullable|numeric|min:0',
            'vendedor_id' => 'nullable|exists:vendedores,id',
        ]);

        $data = [
            'updated_at' => now(),
        ];

        // Campos editáveis
        $camposEditaveis = [
            'nome', 'email', 'documento', 'telefone', 'tipo_cobranca',
            'parcelas_total', 'parcelas_pagas', 'valor_plano_mensal', 'valor_total_cobranca',
            'primeiro_pagamento_at', 'ultimo_pagamento_confirmado_at',
            'proximo_vencimento_at', 'diagnostico_status', 'comissao_tipo'
        ];

        foreach ($camposEditaveis as $campo) {
            if ($request->has($campo)) {
                $data[$campo] = $request->input($campo);
            }
        }

        // Se for parcelamento e informou valor total, garante valor_plano_mensal consistente
        if (($data['tipo_cobranca'] ?? $cliente->tipo_cobranca) === 'installment') {
            $vt = $data['valor_total_cobranca'] ?? $cliente->valor_total_cobranca ?? 0;
            $pt = $data['parcelas_total'] ?? $cliente->parcelas_total ?? 1;
            if ($vt > 0 && $pt > 0) {
                // Auto recalcula o valor de cada parcela
                $data['valor_plano_mensal'] = round($vt / $pt, 2);
            }
        }

        // Recalcular dias_sem_pagar se alterou último pagamento
        if ($request->has('ultimo_pagamento_confirmado_at') && $request->ultimo_pagamento_confirmado_at) {
            $dias = (int) Carbon::parse($request->ultimo_pagamento_confirmado_at)->diffInDays(now(), false);
            $data['dias_sem_pagar'] = max(0, $dias);
        }

        // Se alterou vendedor, recalcular comissão
        $vendedorId = $request->input('vendedor_id');
        $comissaoVendedor = 0;
        $comissaoGestor = 0;

        if ($vendedorId && ($validated['diagnostico_status'] ?? $cliente->diagnostico_status) === 'ATIVO') {
            $vendedor = Vendedor::find($vendedorId);
            if ($vendedor) {
                // Priorizar valor que veio do form para o cálculo da comissão atual
                $novoValorPlano = (float) ($data['valor_plano_mensal'] ?? $cliente->valor_plano_mensal ?? 0);
                $importSimulado = (object) array_merge((array) $cliente, $data, [
                    'valor_plano_mensal' => $novoValorPlano,
                    'comissao_tipo' => $request->input('comissao_tipo') ?? $cliente->comissao_tipo,
                    'parcelas_total' => $data['parcelas_total'] ?? $cliente->parcelas_total ?? 1,
                ]);

                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($importSimulado, $vendedor);
                
                $data['vendedor_id'] = $vendedorId;
                $data['comissao_vendedor_calculada'] = $comissaoVendedor;
                $data['comissao_gestor_calculada'] = $comissaoGestor;
                $data['comissao_mes_referencia'] = $this->getMesReferencia();
            }
        } elseif ($vendedorId === null && $request->has('vendedor_id')) {
            // Remover atribuição
            $data['vendedor_id'] = null;
            $data['comissao_vendedor_calculada'] = 0;
            $data['comissao_gestor_calculada'] = 0;
            $data['comissao_mes_referencia'] = null;
        }

        DB::table('legacy_customer_imports')->where('id', $id)->update($data);

        // SYNC AUTOMÁTICO: Se estiver ATIVO e tiver Vendedor, sincroniza com as tabelas oficiais agora
        if (($validated['diagnostico_status'] ?? $cliente->diagnostico_status) === 'ATIVO' && ($vendedorId ?? $cliente->vendedor_id)) {
            $this->confirmarCliente($request, (int) $id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cliente atualizado com sucesso!',
            'comissao_vendedor' => 'R$ ' . number_format($comissaoVendedor, 2, ',', '.'),
            'comissao_gestor' => 'R$ ' . number_format($comissaoGestor, 2, ',', '.'),
        ]);
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

        // Verifica se há pendentes com mais de 2 dias de atraso (margem de regularização)
        $temPendenteAtrasado = !empty(array_filter($pendentes, function($p) use ($now) {
            if (empty($p['dueDate'])) return false;
            $dueDate = \Carbon\Carbon::parse($p['dueDate'])->startOfDay();
            // diffInDays retorna negativo se dueDate for no passado. < -2 significa mais de 2 dias.
            return $now->startOfDay()->diffInDays($dueDate, false) < -2;
        }));

        // 4. Determinar diagnóstico de status
        $subscriptionCancelada = !empty($subscriptions) && collect($subscriptions)->every(fn($s) =>
            in_array(strtoupper($s['status'] ?? ''), ['CANCELLED', 'CANCELED', 'EXPIRED'])
        );

        if ($subscriptionCancelada && !$temConfirmado) {
            $diagnostico = 'CANCELADO';
        } elseif (!$temConfirmado && $temPendente) {
            // Nunca pagou, só tem pendentes
            $diagnostico = 'CANCELADO';
        } elseif ($temConfirmado && $temPendenteAtrasado) {
            // Já pagou antes, mas tem pendente atual VENCIDO há mais de 2 dias
            $diagnostico = 'CHURN';
        } elseif ($temConfirmado && !$temPendenteAtrasado) {
            // Já pagou antes e, se tiver pendente, ainda está no prazo ou carência
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
        // Obs: Usa o campo comissao_tipo que já foi definido na sincronização
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
        
        // Se já existe um tipo de comissão definido, usar ele (preserve para atribuições retroativas)
        if (!empty($import->comissao_tipo)) {
            $comissaoTipo = $import->comissao_tipo;
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
        $comissaoTipo     = $import->comissao_tipo ?? 'recorrencia';

        if ($vendedorId && $import->diagnostico_status === 'ATIVO' && $comissaoTipo) {
            $vendedor = Vendedor::with('user')->find($vendedorId);
            if ($vendedor) {
                $isGestor = $vendedor->is_gestor ?? false;
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
                
                // Criar registro de comissão
                $gestorId = $vendedor->gestor_id ?? $vendedor->usuario_id;
                
                if ($isGestor) {
                    // Gestor recebe apenas como vendedor
                    if ($comissaoVendedor > 0) {
                        Comissao::create([
                            'vendedor_id' => $vendedorId,
                            'gerente_id' => null,
                            'tipo_comissao' => $comissaoTipo,
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
                    // Vendedor normal: cria comissão para vendedor E gestor
                    if ($comissaoVendedor > 0 || $comissaoGestor > 0) {
                        Comissao::create([
                            'vendedor_id' => $vendedorId,
                            'gerente_id' => $gestorId,
                            'tipo_comissao' => $comissaoTipo,
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
    // ATRIBUIÇÃO EM MASSA - PRÉVIA (CALCULAR ANTES)
    // ──────────────────────────────────────────────────────────────

    public function previewAssign(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'integer|exists:legacy_customer_imports,id',
            'vendedor_id' => 'required',
        ]);

        $vendedorId = (int) $request->vendedor_id;
        $customerIds = array_map('intval', (array) $request->customer_ids);
        $mesRef = $this->getMesReferencia();

        $vendedor = Vendedor::with('user')->find($vendedorId);
        if (!$vendedor) {
            return response()->json(['success' => false, 'message' => 'Vendedor não encontrado'], 404);
        }

        $isGestor = $vendedor->is_gestor ?? false;
        $gestorId = $vendedor->gestor_id ?? $vendedor->usuario_id;

        $totalComissaoVendedor = 0;
        $totalComissaoGestor = 0;
        $totalClientes = 0;
        $debugImports = [];

        foreach ($customerIds as $customerId) {
            $import = DB::table('legacy_customer_imports')->where('id', $customerId)->first();
            
            if (!$import || $import->vendedor_id) {
                continue;
            }

            $comissaoVendedor = 0;
            $comissaoGestor = 0;

            // Forçar cálculo se for ATIVO
            if ($import->diagnostico_status === 'ATIVO') {
                // Definir tipo se estiver vazio
                if (empty($import->comissao_tipo)) {
                    $import->comissao_tipo = (!$import->valor_marco_pago || $import->valor_marco_pago == 0) ? 'recorrencia' : 'inicial';
                }
                
                // Usar o método centralizado para garantir os mesmos fallbacks (operador Elvis)
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
            }

            if ($isGestor) {
                $totalComissaoVendedor += $comissaoVendedor;
            } else {
                $totalComissaoVendedor += $comissaoVendedor;
                $totalComissaoGestor += $comissaoGestor;
            }
            $totalClientes++;
        }

        return response()->json([
            'success' => true,
            'total_clientes' => $totalClientes,
            'vendedor_nome' => $vendedor->user->name ?? 'Vendedor',
            'percentual_vendedor' => $vendedor->comissao_inicial ?? 10,
            'percentual_gestor' => $vendedor->comissao_gestor_primeira ?? 5,
            'comissao_vendedor' => number_format($totalComissaoVendedor, 2, ',', '.'),
            'comissao_gestor' => number_format($totalComissaoGestor, 2, ',', '.'),
        ]);
    }

    public function calculatePreview(Request $request)
    {
        $vendedorId = $request->vendedor_id;
        $comissaoTipo = $request->comissao_tipo;
        $valorPlano = (float) $request->valor_plano_mensal;
        $parcelasTotal = (int) $request->parcelas_total;
        $parcelasPagas = (int) $request->parcelas_pagas;
        $status = $request->diagnostico_status;

        if (!$vendedorId || $status !== 'ATIVO' || $comissaoTipo === 'sem_comissao') {
            return response()->json([
                'success' => true,
                'vendedor' => 'R$ 0,00',
                'gestor' => 'R$ 0,00'
            ]);
        }

        $vendedor = Vendedor::find($vendedorId);
        if (!$vendedor) {
            return response()->json(['success' => false, 'message' => 'Vendedor não encontrado'], 404);
        }

        $import = (object) [
            'valor_marco_pago' => $valorPlano,
            'valor_plano_mensal' => $valorPlano,
            'comissao_tipo' => $comissaoTipo,
            'parcelas_total' => $parcelasTotal,
            'parcelas_pagas' => $parcelasPagas,
        ];

        [$cv, $cg] = $this->calcularComissao($import, $vendedor);

        $diagnostic = null;
        if ($cv == 0 && $valorPlano > 0) {
            $diagnostic = "Zero detectado: PercIni=".($vendedor->comissao_inicial ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0).
                         "%, PercRec=".($vendedor->comissao_recorrencia ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0).
                         "%, Base=".$valorPlano.", Tipo=".$comissaoTipo;
        }

        return response()->json([
            'success' => true,
            'vendedor' => 'R$ ' . number_format($cv, 2, ',', '.'),
            'gestor' => 'R$ ' . number_format($cg, 2, ',', '.'),
            'diagnostic' => $diagnostic,
            'debug' => [
                'vendedor_id' => $vendedor->id,
                'perc_ini' => (float) ($vendedor->comissao_inicial ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0),
                'perc_rec' => (float) ($vendedor->comissao_recorrencia ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0),
                'valor_base' => $valorPlano,
                'tipo_comissao' => $comissaoTipo
            ]
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
            $comissaoTipo = $import->comissao_tipo ?? 'recorrencia'; // Padrão para vendas antigas

            if ($import->diagnostico_status === 'ATIVO' && $comissaoTipo) {
                [$comissaoVendedor, $comissaoGestor] = $this->calcularComissao($import, $vendedor);
            }

            DB::table('legacy_customer_imports')->where('id', $customerId)->update([
                'vendedor_id' => $vendedorId,
                'comissao_vendedor_calculada' => $comissaoVendedor,
                'comissao_gestor_calculada' => $comissaoGestor,
                'comissao_mes_referencia' => $mesRef,
                'comissao_tipo' => $comissaoTipo,
                'updated_at' => now(),
            ]);

            // Criar registro de comissão
            if ($isGestor) {
                // Gestor recebe apenas como vendedor (não recebe como gestor de si mesmo)
                if ($comissaoVendedor > 0) {
                    Comissao::create([
                        'vendedor_id' => $vendedorId,
                        'gerente_id' => null,
                        'tipo_comissao' => $comissaoTipo,
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
                // Vendedor normal: cria comissão para vendedor E gestor da equipe
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

            $totalComissaoVendedor += $isGestor ? $comissaoGestor : $comissaoVendedor;
            $totalComissaoGestor += $comissaoGestor;
            $atribuidos++;
        }

        $vendedorNome = $vendedor->user->name ?? 'Vendedor';

        return response()->json([
            'success' => true,
            'message' => "{$atribuidos} cliente(s) atribuído(s) com sucesso!",
            'atribuidos' => $atribuidos,
            'vendedor_nome' => $vendedorNome,
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

        // Determinar Valor Real da Venda (Total do contrato se parcelado)
        $valorVendaReal = (float) ($import->valor_total_cobranca ?? 0);
        if ($valorVendaReal <= 0) {
            $valorVendaReal = ($import->valor_plano_mensal ?? 0) * ($import->parcelas_total ?? 1);
        }

        // Criar ou atualizar venda
        $vendaValues = [
            'cliente_id'       => $clienteId,
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
        ];

        DB::table('vendas')->updateOrInsert(
            ['cliente_id' => $clienteId, 'origem' => 'asaas_legado'],
            $vendaValues
        );
        $actualVenda = DB::table('vendas')->where('cliente_id', $clienteId)->where('origem', 'asaas_legado')->first();
        $vendaId     = $actualVenda->id;

        // LÓGICA DE COMISSÃO (Mês 4 / Último Pagamento)
        $dataRef = $import->ultimo_pagamento_confirmado_at ?? $import->primeiro_pagamento_at ?? now();
        $carbonRef = Carbon::parse($dataRef);
        $competencia = $carbonRef->format('Y-m');

        // "Ele nao pode mostrar se a comissao for antes do mes 4, mas se for uma recorrencia ele ja deve exibir o valor que vai ser pago esse mes agora"
        $currentMonth = now()->format('Y-m');
        if ($competencia < '2026-04') {
            // Se for importado para trabalhar agora, força a comissão para o mês atual para aparecer no dashboard
            $competencia = $currentMonth;
        }

        if ($import->vendedor_id) {
            $vendedor = Vendedor::find($import->vendedor_id);
            if ($vendedor) {
                [$cv, $cg] = $this->calcularComissao($import, $vendedor);
                
                $gestorId = $vendedor->gestor_id ?? $vendedor->usuario_id;
                
                Comissao::updateOrCreate(
                    [
                        'vendedor_id' => $vendedor->id,
                        'cliente_id'  => $clienteId,
                        'venda_id'    => $vendaId,
                        'competencia' => $competencia,
                    ],
                    [
                        'gerente_id'          => $gestorId,
                        'tipo_comissao'       => $import->comissao_tipo ?? 'recorrencia',
                        'percentual_aplicado' => $vendedor->comissao_inicial ?? 0,
                        'percentual_gerente'  => $vendedor->comissao_gestor_primeira ?? 0,
                        'valor_venda'         => $valorVendaReal,
                        'valor_comissao'      => $cv,
                        'valor_gerente'       => $cg,
                        'status'              => 'confirmada',
                    ]
                );
            }
        }

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
        // Percentuais (Vendedor e Gestor)
        $percIni    = (float) ($vendedor->comissao_inicial ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0);
        $percRec    = (float) ($vendedor->comissao_recorrencia ?: $vendedor->comissao ?: $vendedor->percentual_comissao ?: 0);
        $percGstIni = (float) ($vendedor->comissao_gestor_primeira ?? 0);
        $percGstRec = (float) ($vendedor->comissao_gestor_recorrencia ?? 0);

        // Valores e Parcelas
        $valorPlano    = (float) ($import->valor_plano_mensal ?? 0);
        $parcelasTotal = (int) ($import->parcelas_total ?? 1);

        $cv = 0.0;
        $cg = 0.0;

        $comissaoTipo = $import->comissao_tipo ?? 'recorrencia';

        if ($comissaoTipo === 'inicial_antecipada') {
            // Lógica: 1ª parcela (PercIni) + Demais (PercRec * Restantes)
            $restantes = max(0, $parcelasTotal - 1);
            $cv = ($valorPlano * ($percIni / 100)) + ($valorPlano * ($percRec / 100) * $restantes);
            $cg = ($valorPlano * ($percGstIni / 100)) + ($valorPlano * ($percGstRec / 100) * $restantes);
        } elseif ($comissaoTipo === 'inicial') {
            $cv = $valorPlano * ($percIni / 100);
            $cg = $valorPlano * ($percGstIni / 100);
        } elseif ($comissaoTipo === 'recorrencia') {
            $cv = $valorPlano * ($percRec / 100);
            $cg = $valorPlano * ($percGstRec / 100);
        }

        // Se o vendedor não tem gestor acima dele, a comissão de gestão deve ser ZERO
        if (empty($vendedor->gestor_id)) {
            $cg = 0;
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
