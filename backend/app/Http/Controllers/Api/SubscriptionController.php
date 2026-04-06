<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function migrar(Request $request)
    {
        $lifecycle = new SubscriptionLifecycleService();
        $count = $lifecycle->migrarVendasExistentes();

        return response()->json([
            'success' => true,
            'migradas' => $count,
            'message' => "{$count} vendas migradas com sucesso.",
        ]);
    }

    public function migrarVenda(Request $request, int $vendaId)
    {
        $lifecycle = new SubscriptionLifecycleService();
        $success = $lifecycle->migrarVenda($vendaId);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venda migrada com sucesso.',
        ]);
    }

    public function verificarInadimplencia()
    {
        $lifecycle = new SubscriptionLifecycleService();
        $resultado = $lifecycle->verificarInadimplencia();

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
        ]);
    }
}
