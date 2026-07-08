<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AprovacaoVenda;

class AprovacaoController extends Controller
{
    public function index(Request $request)
    {
        $aprovacoes = AprovacaoVenda::with(['venda.cliente', 'venda.vendedor.user', 'solicitadoPor', 'aprovadoPor'])
            ->orderByDesc('created_at')
            ->get();

        $formatted = $aprovacoes->map(function ($a) {
            $valorFormatado = '';
            if ($a->tipo_aprovacao === 'desconto') {
                $valorFormatado = $a->percentual_solicitado ? "{$a->percentual_solicitado}%" : "R$ " . number_format($a->valor_solicitado, 2, ',', '.');
            } else {
                $valorFormatado = $a->percentual_solicitado ? "{$a->percentual_solicitado}%" : ($a->valor_solicitado ? "R$ " . number_format($a->valor_solicitado, 2, ',', '.') : '-');
            }

            $por = '-';
            if ($a->status === 'APROVADO' || $a->status === 'REJEITADO') {
                $por = $a->aprovadoPor ? $a->aprovadoPor->name : '-';
            }

            return [
                'id' => '#' . str_pad($a->id, 5, '0', STR_PAD_LEFT),
                'vendedor' => $a->venda && $a->venda->vendedor && $a->venda->vendedor->user ? $a->venda->vendedor->user->name : 'Desconhecido',
                'cliente' => $a->venda && $a->venda->cliente ? $a->venda->cliente->nome : 'Desconhecido',
                'tipo' => ucfirst($a->tipo_aprovacao ?? 'Desconto'),
                'valor' => $valorFormatado,
                'status' => ucfirst(strtolower($a->status ?? 'Pendente')),
                'por' => $por,
                'data' => $a->created_at ? $a->created_at->format('d/m/Y H:i') : ''
            ];
        });

        return response()->json(['data' => $formatted]);
    }
}
