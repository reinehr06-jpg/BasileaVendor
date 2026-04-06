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
     * Exportar Histórico Completo
     */
    public function exportarHistorico(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        $vendedorId = $vendedor ? $vendedor->id : 0;

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
