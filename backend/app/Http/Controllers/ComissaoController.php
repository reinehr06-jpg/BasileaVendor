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
                ->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
            
            'confirmada' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('status', 'confirmada')
                ->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
            
            'paga' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('status', 'paga')
                ->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
            
            'recorrencias' => (int) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)->where('tipo_comissao', 'recorrencia')->count(),
            
            'total' => (float) Comissao::where(function($q) use ($user, $vendedorId) {
                    $q->where('vendedor_id', $vendedorId)->orWhere('gerente_id', $user->id);
                })->where('competencia', $mes)
                ->sum(DB::raw("CASE WHEN vendedor_id = $vendedorId THEN valor_comissao ELSE valor_gerente END")),
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

            return [
                'id' => $v->id,
                'nome' => $v->user->name ?? 'N/A',
                'vendas_count' => $v->vendas->count(),
                'total_comissao' => $totalDireta + $totalGestao,
                'detalhe_direta' => $totalDireta,
                'detalhe_gestao' => $totalGestao,
            ];
        });

        return view('master.comissoes.index', compact('vendedoresComissao', 'mes'));
    }

    public function exportar(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        $vendedorId = $vendedor ? $vendedor->id : 0;
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $formato = $request->get('formato', 'csv'); // Padrão CSV

        $filename = "comissoes_{$mes}_" . now()->format('Y-m-d_His') . '.' . ($formato === 'excel' ? 'csv' : $formato);

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
            ->where('competencia', $mes)
            ->with(['cliente', 'venda'])
            ->get();

        $callback = function () use ($comissoes) {
            $file = fopen('php://output', 'w');
            // BOM para Excel reconhecer UTF-8 imediatamente
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'ID', 'Cliente', 'ID Venda', 'Plano', 'Valor Venda', 
                'Percentual (%)', 'Valor Comissão', 'Status', 'Tipo', 'Data'
            ], ';');

            foreach ($comissoes as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->cliente?->nome_igreja ?? $c->cliente?->nome ?? 'N/A',
                    $c->venda_id,
                    $c->venda?->plano ?? 'N/A',
                    number_format((float)($c->valor_venda ?? 0), 2, ',', '.'),
                    number_format((float)($c->percentual_aplicado ?? 0), 1, ',', '.'),
                    number_format((float)($c->valor_comissao ?? 0), 2, ',', '.'),
                    ucfirst($c->status),
                    ucfirst($c->tipo_comissao),
                    $c->created_at->format('d/m/Y'),
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
        ];

        $comissoes = Comissao::where(function($q) use ($user, $vendedorId) {
                $q->where('vendedor_id', $vendedorId)
                  ->orWhere('gerente_id', $user->id);
            })
            ->with(['cliente', 'venda'])
            ->orderByDesc('created_at')
            ->get();

        $callback = function () use ($comissoes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Competência', 'Cliente', 'ID Venda', 'Valor Comissão', 'Status', 'Tipo'], ';');

            foreach ($comissoes as $c) {
                fputcsv($file, [
                    $c->competencia,
                    $c->cliente->nome_igreja ?? $c->cliente->nome ?? 'N/A',
                    $c->venda_id,
                    number_format($c->valor_comissao, 2, ',', '.'),
                    $c->status,
                    $c->tipo_comissao,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
