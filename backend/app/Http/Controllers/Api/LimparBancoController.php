<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimparBancoController extends Controller
{
    public function limpar(Request $request)
    {
        if (!auth()->check() || auth()->user()->perfil !== 'master') {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        try {
            $tabelas = [
                'comissaos',
                'pagamentos',
                'cobrancas',
                'vendas',
                'clientes',
                'vendedores',
            ];

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tabelas as $tabela) {
                if (Schema::hasTable($tabela)) {
                    DB::table($tabela)->delete();
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            \Illuminate\Support\Facades\Log::info('Banco limpo pelo usuário: ' . auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Banco de dados limpo com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
