<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venda;
use Illuminate\Http\Request;

class ClienteStatusController extends Controller
{
    /**
     * O Basiléia Church chama esta rota para verificar
     * se a conta do cliente está ativa.
     *
     * GET /api/client-status/{venda_id}
     * Header: Authorization: Bearer {CHURCH_API_SECRET}
     */
    public function show(Request $request, int $vendaId)
    {
        $token = $request->bearerToken();
        $secret = config('services.church.secret', '');

        if (empty($secret) || $token !== $secret) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $venda = Venda::with('cliente')->find($vendaId);

        if (!$venda) {
            return response()->json([
                'error'   => 'Venda não encontrada',
                'venda_id' => $vendaId,
            ], 404);
        }

        $statusMap = [
            'PAGO'       => 'active',
            'Pago'       => 'active',
            'pago'       => 'active',
            'RECEIVED'   => 'active',
            'CONFIRMED'  => 'active',
            'VENCIDO'    => 'suspended',
            'OVERDUE'    => 'suspended',
            'CANCELADO'  => 'inactive',
            'CANCELED'   => 'inactive',
            'EXPIRADO'   => 'inactive',
            'ESTORNADO'  => 'inactive',
            'REFUNDED'   => 'inactive',
        ];

        $statusVenda = strtoupper($venda->status);
        $churchStatus = $statusMap[$statusVenda] ?? $statusMap[$venda->status] ?? 'inactive';

        // Verifica se tem parcelas pendentes
        $parcelasPagas = null;
        $parcelasTotal = null;
        if ($venda->parcelas > 1) {
            $parcelasTotal = $venda->parcelas;
            $parcelasPagas = $venda->getParcelaAtual();
        }

        return response()->json([
            'venda_id'       => $venda->id,
            'cliente_id'     => $venda->cliente_id,
            'cliente_nome'   => $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? '',
            'cliente_email'  => $venda->cliente->email ?? '',
            'status'         => $churchStatus,
            'plano'          => $venda->plano ?? 'basic',
            'valor'          => (float) $venda->valor,
            'forma_pagamento' => $venda->forma_pagamento,
            'parcelas_pagas'  => $parcelasPagas,
            'parcelas_total'  => $parcelasTotal,
            'data_venda'     => $venda->created_at?->toIso8601String(),
            'atualizado_em'  => $venda->updated_at?->toIso8601String(),
        ]);
    }
}
