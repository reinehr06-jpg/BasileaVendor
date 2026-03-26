<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcluirCobrancaService;
use App\Models\Cobranca;

class CobrancaController extends Controller
{
    protected ExcluirCobrancaService $excluirService;

    public function __construct(ExcluirCobrancaService $excluirService)
    {
        $this->excluirService = $excluirService;
    }

    /**
     * DELETE /api/cobrancas/{id}
     * Exclui uma cobrança específica
     */
    public function destroy(int $id)
    {
        $result = $this->excluirService->executar($id);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * DELETE /api/pagamentos/{id}
     * Exclui um pagamento específico
     */
    public function destroyPagamento(int $id)
    {
        $result = $this->excluirService->excluirPagamento($id);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}