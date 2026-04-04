<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pagamento;
use App\Models\Venda;

class PagamentoController extends Controller
{
    // ==========================================
    // VENDEDOR: Pagamentos das minhas vendas
    // ==========================================
    public function indexVendedor()
    {
        $user = Auth::user();
        
        // Obter os IDs de vendedor autorizados (Próprio + Equipe se for Gestor)
        $vendedorIds = [];
        $vendedor = $user->vendedor;
        
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$vendedor->id ?? 0];
        } elseif ($user->perfil === 'gestor') {
            $vendedorIds = \App\Models\Vendedor::where('gestor_id', $user->id)
                ->orWhere('usuario_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        if (empty($vendedorIds) && !$vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        // Pagamentos ativos (exclui cancelados)
        $pagamentos = Pagamento::whereIn('vendedor_id', $vendedorIds)
            ->whereNotIn('status', ['cancelado'])
            ->with(['venda', 'cliente'])
            ->orderByDesc('created_at')
            ->get();

        // Cobranças de vendas ativas (excluir vendas canceladas/expiradas)
        $vendasComCobrancas = Venda::whereIn('vendedor_id', $vendedorIds)
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with(['cliente', 'cobrancas'])
            ->whereHas('cobrancas')
            ->orderByDesc('created_at')
            ->get();

        // -------------------------------------------------------
        // Unificar dados para a View ($todosPagamentos)
        // -------------------------------------------------------
        $todosPagamentos = collect();

        // 1. Inserir pagamentos confirmados
        foreach ($pagamentos as $p) {
            $statusNormalized = strtolower($p->status) === 'received' ? 'pago' : strtolower($p->status);
            $todosPagamentos->push((object)[
                'igreja' => $p->cliente->nome_igreja ?? $p->cliente->nome ?? '—',
                'pastor' => $p->cliente->nome_pastor ?? '',
                'valor' => $p->valor,
                'forma' => $p->forma_pagamento_real ?? $p->forma_pagamento,
                'status' => $statusNormalized,
                'link' => $p->link_pagamento ?? null,
                'vencimento' => $p->data_vencimento,
                'pagamento_data' => $p->data_pagamento,
                'created_at' => $p->created_at,
            ]);
        }

        // 2. Inserir cobranças pendentes (que não viraram pagamentos confirmados ainda)
        foreach ($vendasComCobrancas as $v) {
            foreach ($v->cobrancas as $c) {
                // Se o status da cobrança for diferente de RECEIVED, ela é relevante aqui como pendente/vencida
                if (strtolower($c->status) !== 'received') {
                    $todosPagamentos->push((object)[
                        'igreja' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                        'pastor' => $v->cliente->nome_pastor ?? '',
                        'valor' => $v->valor,
                        'forma' => $v->forma_pagamento ?? 'pix',
                        'status' => strtolower($c->status) === 'pending' ? 'pendente' : strtolower($c->status),
                        'link' => $c->link,
                        'vencimento' => $c->vencimento ?? ($c->data_vencimento ?? null),
                        'pagamento_data' => null,
                        'created_at' => $c->created_at,
                        'checkout_hash' => $v->checkout_hash ?? null, // Adicionado checkout_hash
                    ]);
                }
            }
        }

        // Ordenar e remover duplicados se houver (pela lógica de ID do Asaas ou link)
        $todosPagamentos = $todosPagamentos->sortByDesc('created_at')->unique(fn($p) => ($p->igreja ?? '') . ($p->valor ?? 0) . ($p->status ?? ''));

        return view('vendedor.pagamentos.index', compact('pagamentos', 'vendasComCobrancas', 'todosPagamentos', 'vendedor'));
    }

    // ==========================================
    // MASTER: Todos os pagamentos
    // ==========================================
    public function indexMaster()
    {
        // Pagamentos ativos (exclui cancelados)
        $pagamentos = Pagamento::whereNotIn('status', ['cancelado'])
            ->with(['venda', 'cliente', 'vendedor.user'])
            ->orderByDesc('created_at')
            ->get();

        // Cobranças de vendas ativas
        $vendasComCobrancas = Venda::whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with(['cliente', 'vendedor.user', 'cobrancas'])
            ->whereHas('cobrancas')
            ->orderByDesc('created_at')
            ->get();

        // Unificar dados de pagamentos + cobranças em uma collection só
        $todosPagamentos = collect();

        foreach ($pagamentos as $p) {
            $statusNormalized = strtolower($p->status) === 'received' ? 'pago' : strtolower($p->status);
            $todosPagamentos->push((object)[
                'igreja' => $p->cliente->nome_igreja ?? $p->cliente->nome ?? '—',
                'pastor' => $p->cliente->nome_pastor ?? '',
                'vendedor' => $p->vendedor?->user?->name ?? '',
                'valor' => $p->valor,
                'forma' => $p->forma_pagamento_real ?? $p->forma_pagamento,
                'status' => $statusNormalized,
                'link' => $p->link_pagamento ?? null,
                'checkout_hash' => $p->venda->checkout_hash ?? null,
                'pagamento_data' => $p->data_pagamento,
                'created_at' => $p->created_at,
            ]);
        }

        foreach ($vendasComCobrancas as $v) {
            foreach ($v->cobrancas as $c) {
                $statusNormalized = strtolower($c->status) === 'received' ? 'pago' : (strtolower($c->status) === 'pending' ? 'pendente' : strtolower($c->status));
                $todosPagamentos->push((object)[
                    'igreja' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                    'pastor' => $v->cliente->nome_pastor ?? '',
                    'vendedor' => $v->vendedor?->user?->name ?? '',
                    'valor' => $v->valor,
                    'forma' => $v->forma_pagamento ?? 'pix',
                    'status' => $statusNormalized,
                    'link' => $c->link,
                    'checkout_hash' => $v->checkout_hash ?? null,
                    'pagamento_data' => strtolower($c->status) === 'received' ? $c->updated_at : null,
                    'created_at' => $c->created_at,
                ]);
            }
        }

        $todosPagamentos = $todosPagamentos->sortByDesc('created_at')->unique(fn($p) => ($p->igreja ?? '') . ($p->valor ?? 0) . ($p->status ?? ''));

        return view('master.pagamentos.index', compact('pagamentos', 'vendasComCobrancas', 'todosPagamentos'));
    }

    public function exportar(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        $formato = $request->get('formato', 'csv');

        // Lógica de Identificação (Master vs Vendedor)
        if ($user->perfil === 'master') {
            $vendedorIds = null; // Todos
        } else {
            if ($user->perfil === 'vendedor') {
                $vendedorIds = [$vendedor->id ?? 0];
            } elseif ($user->perfil === 'gestor') {
                $vendedorIds = \App\Models\Vendedor::where('gestor_id', $user->id)
                    ->orWhere('usuario_id', $user->id)
                    ->pluck('id')
                    ->toArray();
            }
        }

        // Recuperar Dados (Unificado)
        $queryPagamentos = Pagamento::query()->whereNotIn('status', ['cancelado']);
        if ($vendedorIds) $queryPagamentos->whereIn('vendedor_id', $vendedorIds);
        $pagamentosData = $queryPagamentos->with(['venda', 'cliente', 'vendedor.user'])->orderByDesc('created_at')->get();

        $queryVendas = Venda::query()->whereNotIn('status', ['Cancelado', 'Expirado'])->whereHas('cobrancas');
        if ($vendedorIds) $queryVendas->whereIn('vendedor_id', $vendedorIds);
        $vendasData = $queryVendas->with(['cliente', 'vendedor.user', 'cobrancas'])->orderByDesc('created_at')->get();

        $todosPagamentos = collect();
        foreach ($pagamentosData as $p) {
            $todosPagamentos->push((object)[
                'igreja' => $p->cliente->nome_igreja ?? $p->cliente->nome ?? '—',
                'vendedor' => $p->vendedor?->user?->name ?? '',
                'valor' => $p->valor,
                'forma' => strtoupper($p->forma_pagamento_real ?? $p->forma_pagamento),
                'status' => strtolower($p->status) === 'received' ? 'pago' : strtolower($p->status),
                'data' => $p->data_pagamento ? \Carbon\Carbon::parse($p->data_pagamento)->format('d/m/Y') : '—',
                'created_at' => $p->created_at,
            ]);
        }
        foreach ($vendasData as $v) {
            foreach ($v->cobrancas as $c) {
                if (strtolower($c->status) !== 'received') {
                    $todosPagamentos->push((object)[
                        'igreja' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                        'vendedor' => $v->vendedor?->user?->name ?? '',
                        'valor' => $v->valor,
                        'forma' => strtoupper($v->forma_pagamento ?? 'pix'),
                        'status' => strtolower($c->status) === 'pending' ? 'pendente' : strtolower($c->status),
                        'data' => $c->vencimento ? \Carbon\Carbon::parse($c->vencimento)->format('d/m/Y') : '—',
                        'created_at' => $c->created_at,
                    ]);
                }
            }
        }
        $data = $todosPagamentos->sortByDesc('created_at')->unique(fn($p) => ($p->igreja ?? '') . ($p->valor ?? 0) . ($p->status ?? ''));

        // ==========================================
        // DOWNLOAD LOGIC
        // ==========================================
        $filename = "Pagamentos_" . now()->format('Y-m-d_His');

        if ($formato === 'pdf') {
            $resumo = [
                'total' => $data->sum('valor'),
                'count' => $data->count(),
                'pagos' => $data->where('status', 'pago')->count(),
                'pendentes' => $data->filter(fn($i) => in_array($i->status, ['pendente', 'vencido']))->count(),
            ];
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('vendedor.pagamentos.pdf', compact('data', 'resumo'));
            return $pdf->download($filename . '.pdf');
        }

        if ($formato === 'excel' || $formato === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $output = fopen('php://output', 'w');
            fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); 
            
            fputcsv($output, ['Igreja/Cliente', 'Vendedor', 'Valor', 'Forma', 'Status', 'Data']);
            foreach ($data as $item) {
                fputcsv($output, [
                    $item->igreja,
                    $item->vendedor,
                    'R$ ' . number_format($item->valor, 2, ',', '.'),
                    $item->forma,
                    ucfirst($item->status),
                    $item->data
                ]);
            }
            fclose($output);
            return;
        }

        return back()->withErrors(['formato' => 'Formato inválido']);
    }
}
