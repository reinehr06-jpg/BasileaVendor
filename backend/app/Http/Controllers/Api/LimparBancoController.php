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

            // PostgreSQL: desabilitar triggers
            DB::statement('ALTER TABLE comissaos DISABLE TRIGGER ALL');
            DB::statement('ALTER TABLE pagamentos DISABLE TRIGGER ALL');
            DB::statement('ALTER TABLE cobrancas DISABLE TRIGGER ALL');
            DB::statement('ALTER TABLE vendas DISABLE TRIGGER ALL');
            DB::statement('ALTER TABLE clientes DISABLE TRIGGER ALL');
            DB::statement('ALTER TABLE vendedores DISABLE TRIGGER ALL');

            foreach ($tabelas as $tabela) {
                if (Schema::hasTable($tabela)) {
                    DB::table($tabela)->delete();
                }
            }

            // Reabilitar triggers
            DB::statement('ALTER TABLE comissaos ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE pagamentos ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE cobrancas ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE vendas ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE clientes ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE vendedores ENABLE TRIGGER ALL');

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
