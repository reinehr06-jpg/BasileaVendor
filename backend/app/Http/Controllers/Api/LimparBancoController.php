<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LimparBancoController extends Controller
{
    public function limpar(Request $request)
    {
        // Endpoint exclusivo para ambiente não-produtivo.
        // Em produção esta rota retorna 404 para não expor nem confirmar
        // a existência de uma operação destrutiva.
        if (app()->environment('production')) {
            abort(404);
        }

        if (!auth()->check() || auth()->user()->perfil !== 'master') {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        try {
            // PostgreSQL: TRUNCATE com CASCADE ignora foreign keys
            $tabelas = [
                'comissoes',
                'pagamentos',
                'cobrancas',
                'vendas',
                'clientes',
                'vendedores',
                'notas_fiscais',
                'aprovacoes_venda',
                'venda_participantes',
                'subscription_invoices',
                'subscription_cards',
            ];

            // Montar query TRUNCATE com CASCADE
            $tabelasStr = implode(', ', $tabelas);
            DB::statement("TRUNCATE TABLE {$tabelasStr} RESTART IDENTITY CASCADE");

            Log::info('Banco limpo pelo usuário: ' . auth()->id());

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
