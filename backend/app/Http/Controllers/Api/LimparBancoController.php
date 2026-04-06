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
        // Apenas para usuário master autenticado
        if (!auth()->check() || auth()->user()->perfil !== 'master') {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $tabelas = [
            'comissaos',
            'comissoes',
            'pagamentos',
            'cobrancas',
            'vendas',
            'clientes',
            'vendedores',
            'planos',
        ];

        // Tables que não devem ser truncadas
        $naoTruncar = ['users', 'settings', 'migrations'];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tabelas as $tabela) {
            if (Schema::hasTable($tabela)) {
                DB::table($tabela)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Log
        \Illuminate\Support\Facades\Log::info('Banco de dados limpo pelo usuário: ' . auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Banco de dados limpo com sucesso!',
            'tabelas_limpas' => $tabelas,
        ]);
    }
}
