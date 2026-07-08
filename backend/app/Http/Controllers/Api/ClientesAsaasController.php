<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendedor;

class ClientesAsaasController extends Controller
{
    public function index(Request $request)
    {
        $vendedores = Vendedor::whereIn('status', ['ativo', '1', 1])->with('user')->get()->map(function($v) {
            return [
                'id' => $v->id,
                'nome' => $v->user->name ?? '—'
            ];
        });

        $aba = $request->get('aba', 'todos');

        $base = DB::table('legacy_customer_imports as lci')
            ->leftJoin('vendedores as v', 'lci.vendedor_id', '=', 'v.id')
            ->leftJoin('users as u', 'v.usuario_id', '=', 'u.id')
            ->select('lci.*', 'u.name as vendedor_nome');

        // Filtros de aba
        $base = match($aba) {
            'ativos'      => $base->where('lci.diagnostico_status', 'ATIVO'),
            'churn'       => $base->where('lci.diagnostico_status', 'CHURN'),
            'cancelados'  => $base->where('lci.diagnostico_status', 'CANCELADO'),
            'sem_vendedor'=> $base->whereNull('lci.vendedor_id'),
            default       => $base, // todos
        };

        // Filtros adicionais
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $base->where(fn($q) => $q
                ->where('lci.nome', 'like', $s)
                ->orWhere('lci.documento', 'like', $s)
                ->orWhere('lci.email', 'like', $s)
            );
        }
        
        if ($request->filled('vendedor_id')) {
            $request->vendedor_id === 'sem_vendedor'
                ? $base->whereNull('lci.vendedor_id')
                : $base->where('lci.vendedor_id', $request->vendedor_id);
        }
        
        if ($request->filled('tipo_cobranca')) {
            $base->where('lci.tipo_cobranca', $request->tipo_cobranca);
        }

        $clientes = $base->orderBy('lci.created_at', 'desc')->paginate(15);

        // KPIs
        $totais = DB::table('legacy_customer_imports')->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN diagnostico_status = 'ATIVO' THEN 1 END) as ativos,
            COUNT(CASE WHEN diagnostico_status = 'CHURN' THEN 1 END) as churn,
            COUNT(CASE WHEN diagnostico_status = 'CANCELADO' THEN 1 END) as cancelados,
            COUNT(CASE WHEN vendedor_id IS NULL THEN 1 END) as sem_vendedor
        ")->first();

        return response()->json([
            'success' => true,
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'total' => $clientes->total(),
            ],
            'kpis' => $totais,
            'vendedores' => $vendedores,
            'aba' => $aba
        ]);
    }

    public function show($id)
    {
        $cliente = DB::table('legacy_customer_imports')->where('id', $id)->first();

        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cliente
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = DB::table('legacy_customer_imports')->where('id', $id)->first();

        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        $data = $request->only([
            'nome', 'email', 'documento', 'telefone',
            'tipo_cobranca', 'valor_plano_mensal',
            'parcelas_total', 'parcelas_pagas',
            'primeiro_pagamento_at', 'ultimo_pagamento_at', 'proximo_vencimento_at',
            'diagnostico_status', 'vendedor_id'
        ]);

        // Limpa campos vazios para não sobrescrever com string vazia
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        DB::table('legacy_customer_imports')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso']);
    }
}
