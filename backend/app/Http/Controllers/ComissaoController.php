<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comissao;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Models\Meta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComissaoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $vendedor = $user->vendedor;
        if (!$vendedor && !in_array($user->perfil, ['gestor', 'master'])) {
            return redirect()->route('vendedor.dashboard')
                ->with('error', 'Perfil de acesso não encontrado.');
        }

        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $tipo = $request->get('tipo');
        $status = $request->get('status');
        $vendedorId = $vendedor ? $vendedor->id : 0;

        // Query base para listagem com paginação (Sempre instanciar do zero para evitar clone issues)
        $queryList = Comissao::where(function($q) use ($user, $vendedorId) {
            $q->where('vendedor_id', $vendedorId)
              ->orWhere('gerente_id', $user->id);
        })->where('competencia', $mes)
          ->with(['cliente', 'venda', 'vendedor.user']);

        if ($tipo) $queryList->where('tipo_comissao', $tipo);
        if ($status) $queryList->where('status', $status);

        $comissoes = $queryList->orderByDesc('created_at')->paginate(20);

        // Agregados usando queries separadas (Garantia de que não há clone de objetos não-objetos)
        $resumo = [
            'pendente' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('status', 'pendente')
                ->selectRaw("SUM(CASE WHEN vendedor_id = {$vendedorId} THEN valor_comissao ELSE valor_gerente END) as total")
                ->value('total') ?? 0,
            
            'confirmada' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('status', 'confirmada')
                ->selectRaw("SUM(CASE WHEN vendedor_id = {$vendedorId} THEN valor_comissao ELSE valor_gerente END) as total")
                ->value('total') ?? 0,
            
            'paga' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('status', 'paga')
                ->selectRaw("SUM(CASE WHEN vendedor_id = {$vendedorId} THEN valor_comissao ELSE valor_gerente END) as total")
                ->value('total') ?? 0,
            
            'recorrencias' => (int) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('tipo_comissao', 'recorrencia')->count(),
            
            'total' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)
                ->selectRaw("SUM(CASE WHEN vendedor_id = {$vendedorId} THEN valor_comissao ELSE valor_gerente END) as total")
                ->value('total') ?? 0,
        ];

        return view('vendedor.comissoes.index', compact('comissoes', 'resumo', 'mes', 'tipo', 'status', 'vendedor'));
    }

    /**
     * Tela de comissões do master - simplificada por vendedor
     */
    public function indexMaster(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $dataInicio = Carbon::parse($mes . '-01')->startOfMonth();
        $dataFim = (clone $dataInicio)->endOfMonth();

        $vendedores = Vendedor::with(['user', 'vendas' => function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('created_at', [$dataInicio, $dataFim]);
        }])->get();

        $vendedoresComissao = $vendedores->map(function ($v) use ($dataInicio, $dataFim, $mes) {
            // Comissão como Vendedor (Direta)
            $comissoesDiretas = Comissao::where('vendedor_id', $v->id)
                ->where('competencia', $mes)->get();

            // Comissão como Gestor (Equipe - Overriding)
            $comissoesGestao = Comissao::where('gerente_id', $v->usuario_id)
                ->where('competencia', $mes)->get();

            $totalDireta = $comissoesDiretas->sum('valor_comissao');
            $totalGestao = $comissoesGestao->sum('valor_gerente');

            // --- Campos extras para a View Master ---
            $totalVendido = $v->vendas->sum('valor');
            $meta = 0; // Se houver modelo de meta, buscar aqui.

            return [
                'id' => $v->id,
                'nome' => $v->user->name ?? 'N/A',
                'email' => $v->user->email ?? 'N/A',
                'total_vendas' => $v->vendas->count(),
                'comissao_total' => $totalDireta + $totalGestao,
                'detalhe_direta' => $totalDireta,
                'detalhe_gestao' => $totalGestao,
                'vendido' => $totalVendido,
                'meta' => $meta,
                'notas_fiscais_count' => 0, // Placeholder
            ];
        });

        // Agregados globais para os cards do topo
        $resumo = [
            'total_vendedores' => $vendedores->count(),
            'total_comissao'   => $vendedoresComissao->sum('comissao_total'),
            'total_vendas'     => $vendedoresComissao->sum('total_vendas'),
            'total_faturamento'=> $vendedoresComissao->sum('vendido'),
        ];
        $resumo['ticket_medio'] = $resumo['total_vendas'] > 0 ? $resumo['total_comissao'] / $resumo['total_vendas'] : 0;

        return view('master.comissoes.index', compact('vendedoresComissao', 'mes', 'resumo'));
    }

    public function exportar(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        $vendedorId = $vendedor ? $vendedor->id : 0;
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $formato = $request->get('formato', 'csv');

        $query = Comissao::where(function($q) use ($user, $vendedorId) {
                $q->where('vendedor_id', $vendedorId)
                  ->orWhere('gerente_id', $user->id);
            })
            ->where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user'])
            ->orderByDesc('created_at');

        $comissoes = $query->get();

        if ($formato === 'pdf') {
            $resumo = [
                'total' => $comissoes->sum(function($c) use ($vendedorId) {
                    return $c->vendedor_id == $vendedorId ? $c->valor_comissao : $c->valor_gerente;
                }),
                'mes' => $mes,
                'vendedor' => $vendedor ? $vendedor->nome : $user->name
            ];

            $pdf = Pdf::loadView('vendedor.comissoes.pdf', compact('comissoes', 'resumo', 'mes'));
            return $pdf->download("comissoes_{$mes}_" . now()->format('Y-m-d_Hism') . ".pdf");
        }

        $filename = "comissoes_{$mes}_" . now()->format('Y-m-d_His') . '.' . ($formato === 'excel' ? 'csv' : $formato);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($comissoes, $vendedorId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'ID', 'Cliente', 'Igreja', 'ID Venda', 'Plano', 'Valor Venda', 
                'Percentual (%)', 'Valor Comissão', 'Status', 'Tipo', 'Data'
            ], ';');

            foreach ($comissoes as $c) {
                $valorComissao = $c->vendedor_id == $vendedorId ? $c->valor_comissao : $c->valor_gerente;
                $percentual = $c->vendedor_id == $vendedorId ? $c->percentual_aplicado : $c->percentual_gerente;

                fputcsv($file, [
                    $c->id,
                    $c->cliente?->nome ?? 'N/A',
                    $c->cliente?->nome_igreja ?? 'N/A',
                    $c->venda_id,
                    $c->venda?->plano ?? 'N/A',
                    number_format((float)($c->valor_venda ?? 0), 2, ',', '.'),
                    number_format((float)($percentual ?? 0), 1, ',', '.'),
                    number_format((float)($valorComissao ?? 0), 2, ',', '.'),
                    ucfirst($c->status ?? 'pendente'),
                    ucfirst($c->tipo_comissao ?? 'inicial'),
                    $c->created_at ? $c->created_at->format('d/m/Y') : now()->format('d/m/Y'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Histórico completo do vendedor — retorna View ou JSON (AJAX)
     * Integra dados reais (tabela comissoes) + dados legados (legacy_customer_imports)
     */
    public function historicoVendedor(Request $request, $vendedorId)
    {
        try {
            $vendedor = Vendedor::with('user')->findOrFail($vendedorId);
            $mes = $request->get('mes', Carbon::now()->format('Y-m'));

            // Se for requisição normal (não AJAX), retorna a view
            if (!$request->wantsJson() && !$request->ajax()) {
                return view('master.comissoes.historico', compact('vendedorId', 'mes'));
            }

            // ── A partir daqui: JSON para o fetch() do front-end ──

            $dataInicio = Carbon::parse($mes . '-01')->startOfMonth();
            $dataFim    = (clone $dataInicio)->endOfMonth();

            // ── 1. Dados de Vendas (tabela vendas) ──
            $vendas = Venda::where('vendedor_id', $vendedorId)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->get();

            $vendasPagas = $vendas->filter(fn($v) => in_array(strtoupper($v->status ?? ''), ['PAGO', 'PAGO_ASAAS']));
            $vendasCanceladas = $vendas->filter(fn($v) => in_array(strtoupper($v->status ?? ''), ['CANCELADO', 'ESTORNADO', 'EXPIRADO', 'VENCIDO']));

            $valorRecebido = Pagamento::whereIn('venda_id', $vendasPagas->pluck('id'))
                ->whereIn('status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
                ->sum('valor');

            // Clientes ativos (únicos com pagamento confirmado)
            $clientesAtivos = $vendasPagas->pluck('cliente_id')->unique()->count();

            // ── 2. Dados Legacy (legacy_customer_imports) ──
            $percIni = (float) ($vendedor->comissao_inicial ?? 0);
            $percRec = (float) ($vendedor->comissao_recorrencia ?? 0);

            $legacyClientes = DB::table('legacy_customer_imports')
                ->where('vendedor_id', $vendedorId)
                ->whereNotNull('primeiro_pagamento_at')
                ->get();

            $legacyComissoes = [];
            $legacyValorVendido = 0;

            foreach ($legacyClientes as $lc) {
                $dataRef = $lc->primeiro_pagamento_at;
                if (!$dataRef) continue;

                $start = Carbon::parse($dataRef)->startOfMonth();
                $valorPlano = (float) ($lc->valor_plano_mensal ?? 0);
                $parcelasTotal = (int) ($lc->parcelas_total ?? 1);

                // Calcular em qual "mês de vida" do cliente estamos
                $mesAtualCalc = $start->copy();
                $countMeses = 0;

                while ($mesAtualCalc <= $dataFim) {
                    $countMeses++;
                    $mesStr = $mesAtualCalc->format('Y-m');

                    if ($mesStr === $mes) {
                        $cv = 0;
                        $tipo = 'recorrencia';

                        $lcComissaoTipo = $lc->comissao_tipo ?? 'recorrencia';

                        if ($lcComissaoTipo === 'inicial_antecipada') {
                            if ($countMeses === 1) {
                                $cv = $valorPlano * ($percIni / 100);
                                $restantes = max(0, $parcelasTotal - 1);
                                $cv += $valorPlano * ($percRec / 100) * $restantes;
                                $tipo = 'inicial_antecipada';
                            }
                        } elseif ($lcComissaoTipo === 'inicial') {
                            if ($countMeses === 1) {
                                $cv = $valorPlano * ($percIni / 100);
                                $tipo = 'inicial';
                            } else {
                                if (($lc->tipo_cobranca ?? '') === 'installment' && $countMeses > $parcelasTotal) {
                                    $cv = 0;
                                } else {
                                    $cv = $valorPlano * ($percRec / 100);
                                }
                            }
                        } elseif ($lcComissaoTipo === 'recorrencia') {
                            if (($lc->tipo_cobranca ?? '') === 'installment' && $countMeses > $parcelasTotal) {
                                $cv = 0;
                            } else {
                                $cv = $valorPlano * ($percRec / 100);
                            }
                        }

                        if ($cv > 0) {
                            $legacyValorVendido += $valorPlano;
                            $legacyComissoes[] = [
                                'cliente'        => $lc->nome ?? 'Cliente Legado',
                                'venda_id'       => 'L-' . $lc->id,
                                'valor_venda'    => $valorPlano,
                                'percentual'     => $tipo === 'inicial' || $tipo === 'inicial_antecipada' ? $percIni : $percRec,
                                'valor_comissao' => round($cv, 2),
                                'tipo'           => $tipo,
                                'status'         => 'confirmada',
                                'data_pagamento' => Carbon::parse($dataRef)->format('d/m/Y'),
                                'is_legacy'      => true,
                            ];
                        }
                        break; // Já achamos o mês, pode sair
                    }

                    $mesAtualCalc->addMonth();
                    if ($countMeses > 240) break; // Trava de segurança
                }
            }

            // ── 3. Comissões reais (tabela comissoes) ──
            $comissoesReais = Comissao::where('vendedor_id', $vendedorId)
                ->where('competencia', $mes)
                ->with(['cliente', 'venda'])
                ->orderByDesc('created_at')
                ->get();

            $comissoesDetalhes = $comissoesReais->map(function ($c) {
                return [
                    'cliente'        => $c->cliente?->nome ?? $c->cliente?->nome_igreja ?? 'N/A',
                    'venda_id'       => $c->venda_id,
                    'valor_venda'    => (float) ($c->valor_venda ?? 0),
                    'percentual'     => (float) ($c->percentual_aplicado ?? 0),
                    'valor_comissao' => (float) ($c->valor_comissao ?? 0),
                    'tipo'           => $c->tipo_comissao ?? 'inicial',
                    'status'         => $c->status ?? 'pendente',
                    'data_pagamento' => $c->created_at?->format('d/m/Y') ?? '-',
                    'is_legacy'      => false,
                ];
            })->toArray();

            // Merge comissões reais + legacy
            $todosDetalhes = array_merge($comissoesDetalhes, $legacyComissoes);

            $totalComissaoReal   = $comissoesReais->sum('valor_comissao');
            $totalComissaoLegacy = collect($legacyComissoes)->sum('valor_comissao');
            $totalComissao       = $totalComissaoReal + $totalComissaoLegacy;

            // ── 4. Vendas por forma de pagamento ──
            $porForma = $vendasPagas->groupBy('forma_pagamento')->map(function ($group, $forma) {
                return [
                    'forma'      => $forma ?: 'Não definido',
                    'quantidade' => $group->count(),
                    'valor'      => $group->sum('valor'),
                ];
            })->values()->toArray();

            // ── 5. Vendas por tipo de negociação ──
            $porTipo = $vendasPagas->groupBy('tipo_negociacao')->map(function ($group, $tipo) {
                return [
                    'tipo'       => $tipo ?: 'Não definido',
                    'quantidade' => $group->count(),
                    'valor'      => $group->sum('valor'),
                ];
            })->values()->toArray();

            // ── 6. Meta do mês ──
            $meta = Meta::where('vendedor_id', $vendedorId)
                ->where('mes_referencia', $mes)
                ->first();

            $valorMeta = (float) ($meta?->valor_meta ?? 0);
            $valorVendido = $vendasPagas->sum('valor') + $legacyValorVendido;
            $percentualMeta = $valorMeta > 0 ? round(($valorVendido / $valorMeta) * 100, 1) : 0;

            // ── 7. Ticket médio ──
            $totalVendasCount = $vendasPagas->count() + count($legacyComissoes);
            $ticketMedio = $totalVendasCount > 0 ? round($valorVendido / $totalVendasCount, 2) : 0;

            return response()->json([
                'status' => 'success',
                'vendedor' => [
                    'id'    => $vendedor->id,
                    'nome'  => $vendedor->user->name ?? 'N/A',
                    'email' => $vendedor->user->email ?? 'N/A',
                ],
                'meta' => [
                    'valor'        => $valorMeta,
                    'valor_vendido'=> $valorVendido,
                    'percentual'   => $percentualMeta,
                ],
                'vendas' => [
                    'total'              => $vendas->count() + count($legacyComissoes),
                    'valor_total'        => $valorVendido,
                    'valor_recebido'     => (float) $valorRecebido + $legacyValorVendido,
                    'clientes_ativos'    => $clientesAtivos + $legacyClientes->where('diagnostico_status', 'ATIVO')->count(),
                    'cancelamentos'      => $vendasCanceladas->count(),
                    'valor_cancelado'    => $vendasCanceladas->sum('valor'),
                    'ticket_medio'       => $ticketMedio,
                    'por_forma_pagamento'  => $porForma,
                    'por_tipo_negociacao'  => $porTipo,
                ],
                'comissoes' => [
                    'total'    => round($totalComissao, 2),
                    'detalhes' => $todosDetalhes,
                ],
                'notas_fiscais' => [],
                'is_admin' => auth()->user()?->perfil === 'master',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro no histórico do vendedor: ' . $e->getMessage(), [
                'vendedor_id' => $vendedorId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Exportar Histórico Completo
     */
    public function exportarHistorico(Request $request, $vendedorId)
    {
        $user = Auth::user();
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $formato = $request->get('formato', 'csv');
        
        // Se for admin, usa o ID passado. Se não, usa o próprio ID do vendedor logado.
        if ($user->perfil !== 'master') {
            $vendedor = $user->vendedor;
            $vendedorId = $vendedor ? $vendedor->id : 0;
        }

        $filename = "historico_comissoes_" . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $comissoes = Comissao::where(function($q) use ($user, $vendedorId) {
                $q->where('vendedor_id', $vendedorId)
                  ->orWhere('gerente_id', $user->id);
            })
            ->with(['cliente', 'venda'])
            ->orderByDesc('created_at')
            ->get();

        $callback = function () use ($comissoes, $vendedorId) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Competência', 'Cliente', 'Igreja', 'ID Venda', 'Valor Comissão', 'Status', 'Tipo', 'Data'], ';');

            foreach ($comissoes as $c) {
                $valorComissao = $c->vendedor_id == $vendedorId ? $c->valor_comissao : $c->valor_gerente;

                fputcsv($file, [
                    $c->competencia,
                    $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A',
                    $c->cliente?->nome_igreja ?? 'N/A',
                    $c->venda_id,
                    number_format((float)($valorComissao ?? 0), 2, ',', '.'),
                    ucfirst($c->status ?? 'pendente'),
                    ucfirst($c->tipo_comissao ?? 'inicial'),
                    $c->created_at ? $c->created_at->format('d/m/Y') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
