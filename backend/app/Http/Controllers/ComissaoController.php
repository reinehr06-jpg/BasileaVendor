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

            // Query base para listagem com paginação
            $queryList = Comissao::where(function($q) use ($user, $vendedorId) {
                $q->where('vendedor_id', $vendedorId)
                  ->orWhere('gerente_id', $user->id);
            })->where('competencia', $mes)
              ->with(['cliente', 'venda', 'vendedor.user']);

            if ($tipo) $queryList->where('tipo_comissao', $tipo);
            if ($status) $queryList->where('status', $status);

            $comissoes = $queryList->orderByDesc('created_at')->paginate(20);

            // Resumo usando agregados do banco (Query apartada para evitar conflitos de clones)
            $resumoQuery = Comissao::where(function($q) use ($user, $vendedorId) {
                $q->where('vendedor_id', $vendedorId)
                  ->orWhere('gerente_id', $user->id);
            })->where('competencia', $mes);

            $resumo = [
                'pendente' => (float)(clone $resumoQuery)->where('status', 'pendente')->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
                'confirmada' => (float)(clone $resumoQuery)->where('status', 'confirmada')->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
                'paga' => (float)(clone $resumoQuery)->where('status', 'paga')->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
                'recorrencias' => (int)(clone $resumoQuery)->where('tipo_comissao', 'recorrencia')->count(),
                'total' => (float)(clone $resumoQuery)->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
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

            $vendasEfetivas = $v->vendas->whereNotIn('status', ['Cancelado', 'Expirado']);
            $vendasCanceladas = $v->vendas->whereIn('status', ['Cancelado', 'Expirado', 'Vencido']);

            $metaObj = Meta::where('vendedor_id', $v->id)->where('mes_referencia', $mes)->first();
            $valorMeta = $metaObj ? $metaObj->valor_meta : ($v->meta_mensal ?? 0);
            $valorVendido = $vendasEfetivas->sum('valor');

            $totalComissao = $comissoesDiretas->sum('valor_comissao') + $comissoesGestao->sum('valor_gerente');

            // Buscar notas fiscais
            $notasCount = 0;
            if (class_exists('App\Models\NotaFiscal')) {
                $notasCount = \App\Models\NotaFiscal::where('vendedor_id', $v->id)
                    ->whereBetween('created_at', [$dataInicio, $dataFim])
                    ->count();
            }

            return [
                'id' => $v->id,
                'nome' => $v->user->name ?? 'N/A',
                'email' => $v->user->email ?? '',
                'total_vendas' => $vendasEfetivas->count(),
                'comissao_total' => $totalComissao,
                'vendido' => $valorVendido,
                'cancelamentos' => $vendasCanceladas->count(),
                'meta' => $valorMeta,
                'percentual_meta' => $valorMeta > 0 ? round(($valorVendido / $valorMeta) * 100, 1) : 0,
                'notas_fiscais_count' => $notasCount,
            ];
        });

        $resumo = [
            'total_vendedores' => $vendedoresComissao->count(),
            'total_comissao' => $vendedoresComissao->sum('comissao_total'),
            'total_vendas' => $vendedoresComissao->sum('total_vendas'),
            'ticket_medio' => $vendedoresComissao->sum('total_vendas') > 0 
                ? $vendedoresComissao->sum('comissao_total') / $vendedoresComissao->sum('total_vendas')
                : 0,
        ];

        return view('master.comissoes.index', compact('vendedoresComissao', 'resumo', 'mes'));
    }

    /**
     * Página de histórico completo de um vendedor
     */
    public function historicoVendedor(Request $request, $vendedorId)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        
        if ($request->expectsJson()) {
            return $this->historicoVendedorJson($request, $vendedorId, $mes);
        }
        
        return view('master.comissoes.historico', compact('vendedorId', 'mes'));
    }
    
    /**
     * Histórico completo de um vendedor (JSON para modal)
     */
    private function historicoVendedorJson(Request $request, $vendedorId, $mes)
    {
        $dataInicio = Carbon::parse($mes . '-01')->startOfMonth();
        $dataFim = (clone $dataInicio)->endOfMonth();

        $vendedor = Vendedor::with('user')->findOrFail($vendedorId);

        $vendas = Venda::where('vendedor_id', $vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->with(['cliente'])->get();

        $vendasEfetivas = $vendas->whereNotIn('status', ['Cancelado', 'Expirado']);
        $vendasCanceladas = $vendas->whereIn('status', ['Cancelado', 'Expirado', 'Vencido']);

        $comissoes = Comissao::where(function($q) use ($vendedorId, $vendedor) {
            $q->where('vendedor_id', $vendedorId)
              ->orWhere('gerente_id', $vendedor->usuario_id);
        })->where('competencia', $mes)
          ->with(['cliente', 'venda', 'vendedor.user'])->get();

        $getComisVal = function($c) use ($vendedorId) {
            return ($c->vendedor_id == $vendedorId) ? $c->valor_comissao : $c->valor_gerente;
        };

        $porFormaPagamento = $vendasEfetivas->groupBy('forma_pagamento')->map(function ($g, $forma) {
            return [
                'forma' => $forma ?: 'Não definido',
                'quantidade' => $g->count(),
                'valor' => $g->sum('valor'),
            ];
        })->values();

        $porTipoNegociacao = $vendasEfetivas->groupBy('tipo_negociacao')->map(function ($g, $tipo) {
            return [
                'tipo' => $tipo ?: 'Não definido',
                'quantidade' => $g->count(),
                'valor' => $g->sum('valor'),
            ];
        })->values();

        $metaObj = Meta::where('vendedor_id', $vendedorId)->where('mes_referencia', $mes)->first();
        $valorMeta = $metaObj ? $metaObj->valor_meta : ($vendedor->meta_mensal ?? 0);

        $clientesAtivos = Pagamento::where('vendedor_id', $vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'pago'])
            ->distinct('cliente_id')->count('cliente_id');

        $notasFiscais = [];
        if (Auth::user()->perfil === 'master') {
            $notasFiscais = \App\Models\NotaFiscal::where('vendedor_id', $vendedorId)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->orderByDesc('created_at')->get()->map(function ($nf) {
                    return [
                        'id' => $nf->id,
                        'descricao' => $nf->descricao,
                        'valor' => $nf->valor,
                        'data' => $nf->created_at->format('d/m/Y'),
                        'arquivo' => $nf->arquivo_path,
                    ];
                })->toArray();
        }

        return response()->json([
            'vendedor' => [
                'id' => $vendedor->id,
                'nome' => $vendedor->user->name ?? 'N/A',
                'email' => $vendedor->user->email ?? '',
                'perfil' => $vendedor->user->perfil ?? 'vendedor',
            ],
            'mes' => $mes,
            'meta' => [
                'valor' => $valorMeta,
                'valor_vendido' => $vendasEfetivas->sum('valor'),
                'valor_recebido' => $vendas->where('status', 'PAGO')->sum('valor'),
                'percentual' => $valorMeta > 0 ? round(($vendasEfetivas->sum('valor') / $valorMeta) * 100, 1) : 0,
                'status' => $metaObj->status ?? 'não iniciada',
            ],
            'vendas' => [
                'total' => $vendasEfetivas->count(),
                'valor_total' => $vendasEfetivas->sum('valor'),
                'cancelamentos' => $vendasCanceladas->count(),
                'valor_cancelado' => $vendasCanceladas->sum('valor'),
                'por_forma_pagamento' => $porFormaPagamento,
                'por_tipo_negociacao' => $porTipoNegociacao,
                'clientes_ativos' => $clientesAtivos,
            ],
            'comissoes' => [
                'total' => $comissoes->sum($getComisVal),
                'paga' => $comissoes->where('status', 'paga')->sum($getComisVal),
                'pendente' => $comissoes->where('status', 'pendente')->sum($getComisVal),
                'confirmada' => $comissoes->where('status', 'confirmada')->sum($getComisVal),
                'detalhes' => $comissoes->map(function ($c) use ($vendedorId, $getComisVal) {
                    $isDirect = ($c->vendedor_id == $vendedorId);
                    return [
                        'id' => $c->id,
                        'cliente' => ($isDirect ? '' : '[Equipe: ' . ($c->vendedor->user->name ?? 'Vendedor') . '] ') . ($c->cliente->nome_igreja ?? $c->cliente->nome ?? 'N/A'),
                        'venda_id' => $c->venda_id,
                        'valor_venda' => $c->valor_venda,
                        'percentual' => $isDirect ? $c->percentual_aplicado : $c->percentual_gerente,
                        'valor_comissao' => $getComisVal($c),
                        'tipo' => $isDirect ? "Direta: {$c->tipo_comissao}" : "Equipe (Gestão): {$c->tipo_comissao}",
                        'status' => $c->status,
                        'data_pagamento' => $c->data_pagamento?->format('d/m/Y'),
                    ];
                }),
            ],
            'notas_fiscais' => $notasFiscais,
            'is_admin' => Auth::user()->perfil === 'master',
        ]);
    }

    /**
     * API: Listar comissões
     */
    public function apiListar(Request $request)
    {
        $user = Auth::user();
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');
        $tipo = $request->get('tipo');
        $status = $request->get('status');

        $query = Comissao::where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user']);

        if ($user->perfil !== 'master') {
            if (!$user->vendedor) {
                return response()->json([]);
            }
            $query->where('vendedor_id', $user->vendedor->id);
        } elseif ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        if ($tipo) $query->where('tipo_comissao', $tipo);
        if ($status) $query->where('status', $status);

        $comissoes = $query->orderByDesc('created_at')->get();

        return response()->json($comissoes->map(fn ($c) => [
            'id' => $c->id,
            'vendedor' => $c->vendedor->user->name ?? 'N/A',
            'cliente' => $c->cliente->nome_igreja ?? $c->cliente->nome,
            'documento' => $c->cliente->documento,
            'venda_id' => $c->venda_id,
            'valor_venda' => $c->valor_venda,
            'percentual' => $c->percentual_aplicado,
            'valor_comissao' => $c->valor_comissao,
            'tipo' => $c->tipo_comissao,
            'data_pagamento' => $c->data_pagamento?->format('Y-m-d'),
            'competencia' => $c->competencia,
            'status' => $c->status,
        ]));
    }

    /**
     * API: Resumo das comissões
     */
    public function apiResumo(Request $request)
    {
        $user = Auth::user();
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');

        $query = Comissao::where('competencia', $mes);

        if ($user->perfil !== 'master') {
            if (!$user->vendedor) {
                return response()->json(['mes' => $mes, 'total_comissao' => 0, 'pendente' => 0, 'confirmada' => 0, 'paga' => 0, 'recorrencias' => 0, 'iniciais' => 0, 'total_registros' => 0]);
            }
            $query->where('vendedor_id', $user->vendedor->id);
        } elseif ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $todas = $query->get();

        return response()->json([
            'mes' => $mes,
            'total_comissao' => round($todas->sum('valor_comissao'), 2),
            'pendente' => round($todas->where('status', 'pendente')->sum('valor_comissao'), 2),
            'confirmada' => round($todas->where('status', 'confirmada')->sum('valor_comissao'), 2),
            'paga' => round($todas->where('status', 'paga')->sum('valor_comissao'), 2),
            'recorrencias' => $todas->where('tipo_comissao', 'recorrencia')->count(),
            'iniciais' => $todas->where('tipo_comissao', 'inicial')->count(),
            'total_registros' => $todas->count(),
        ]);
    }

    /**
     * Exportar comissões em CSV
     */
    public function exportar(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');
        $formato = $request->get('formato', 'csv');
        $user = Auth::user();

        $query = Comissao::where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user']);

        if ($user->perfil === 'vendedor' && $user->vendedor) {
            $query->where('vendedor_id', $user->vendedor->id);
        } elseif ($user->perfil === 'gestor') {
            $vendedorIdExport = $user->vendedor ? $user->vendedor->id : 0;
            $query->where(function($q) use ($user, $vendedorIdExport) {
                $q->where('vendedor_id', $vendedorIdExport)
                  ->orWhere('gerente_id', $user->id);
            });
        } elseif ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $comissoes = $query->orderBy('vendedor_id')->orderBy('created_at')->get();

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('master.comissoes.export-pdf', compact('comissoes', 'mes'))
                ->setPaper('a4', 'landscape');
            return $pdf->download("comissoes_{$mes}.pdf");
        }

        if ($formato === 'excel' || $formato === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"comissoes_{$mes}.csv\"",
            ];
            $callback = function () use ($comissoes) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['Vendedor', 'Cliente', 'CPF/CNPJ', 'Valor Venda', '%', 'Valor Comissão', 'Tipo', 'Status', 'Data'], ';');
                foreach ($comissoes as $c) {
                    fputcsv($file, [
                        $c->vendedor?->user?->name ?? 'N/A',
                        $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A',
                        $c->cliente?->documento ?? 'N/A',
                        number_format((float)($c->valor_venda ?? 0), 2, ',', '.'),
                        $c->percentual_aplicado . '%',
                        number_format((float)($c->valor_comissao ?? 0), 2, ',', '.'),
                        ucfirst($c->tipo_comissao ?? ''),
                        ucfirst($c->status ?? ''),
                        $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '-',
                    ], ';');
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return response()->json(['error' => 'Formato inválido.'], 400);
    }

    /**
     * Exportar histórico completo do vendedor em CSV
     */
    public function exportarHistorico(Request $request, $vendedorId)
    {
        $user = Auth::user();

        if ($user->perfil !== 'master') {
            if (!$user->vendedor || $user->vendedor->id != $vendedorId) {
                abort(403, 'Acesso não autorizado.');
            }
        }

        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $dataInicio = Carbon::parse($mes . '-01')->startOfMonth();
        $dataFim = (clone $dataInicio)->endOfMonth();

        $vendedor = Vendedor::with('user')->findOrFail($vendedorId);

        $vendas = Venda::where('vendedor_id', $vendedorId)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->with(['cliente'])->get();

        $nomeArquivo = "historico_{$vendedor->user->name}_{$mes}.csv";
        $nomeArquivo = str_replace(' ', '_', strtolower($nomeArquivo));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
        ];

        $callback = function () use ($vendas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Venda #', 'Cliente', 'Pastor', 'Valor (R$)', 'Forma Pagamento',
                'Tipo Negociação', 'Status', 'Comissão Gerada (R$)', 'Data',
            ], ';');

            foreach ($vendas as $v) {
                fputcsv($file, [
                    $v->id,
                    $v->cliente?->nome_igreja ?? $v->cliente?->nome ?? 'N/A',
                    $v->cliente?->nome_pastor ?? '—',
                    number_format((float)($v->valor ?? 0), 2, ',', '.'),
                    $v->forma_pagamento ?? '—',
                    $v->tipo_negociacao ?? '—',
                    $v->status,
                    number_format((float)($v->comissao_gerada ?? 0), 2, ',', '.'),
                    $v->created_at ? $v->created_at->format('d/m/Y') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download de nota fiscal (apenas admin)
     */
    public function downloadNotaFiscal(Request $request, $notaId)
    {
        if (Auth::user()->perfil !== 'master') {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $nota = \App\Models\NotaFiscal::findOrFail($notaId);
        $path = storage_path('app/' . $nota->arquivo_path);

        if (!file_exists($path)) {
            return back()->withErrors(['error' => 'Arquivo não encontrado.']);
        }

        return response()->download($path, $nota->descricao . '.' . pathinfo($nota->arquivo_path, PATHINFO_EXTENSION));
    }
}
