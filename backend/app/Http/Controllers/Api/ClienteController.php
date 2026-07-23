<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Cliente::query();
        
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        if (!$isGestor && $vendedorId) {
            $query->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('nome_igreja', 'like', "%{$search}%")
                  ->orWhere('documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clientes = $query->with('vendas.vendedor.user')->latest()->paginate(15);
        
        // Formatar para o frontend
        $clientes->getCollection()->transform(function($c) {
            // Obter o vendedor principal do cliente com base na venda mais recente
            $venda = $c->vendas->first();
            $vendedor = $venda ? ($venda->vendedor->user->name ?? $venda->vendedor->nome) : 'N/A';

            return [
                'id' => $c->id,
                'nome' => $c->nome_igreja ?? $c->nome,
                'cpfCnpj' => $c->documento ?? 'Não informado',
                'responsavel' => $c->nome_responsavel ?? $c->nome,
                'vendedor' => $vendedor,
                'financeiro' => $c->recorrencia_status === 'ACTIVE' ? 'Em dia' : ($c->recorrencia_status ?? 'Pendente'),
                'status' => $c->status,
                'email' => $c->email,
                'telefone' => $c->telefone ?? $c->whatsapp,
            ];
        });

        return response()->json($clientes);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Cliente::where('id', $id)->with('vendas.vendedor');

        if (!$isGestor && $vendedorId) {
            $query->whereHas('vendas', function($q) use ($vendedorId) {
                $q->where('vendedor_id', $vendedorId);
            });
        }

        $cliente = $query->firstOrFail();
        return response()->json($cliente);
    }

    /**
     * Histórico COMPLETO do cliente: vendas, pagamentos, comissões, vendedor
     * responsável, totais e ticket médio.
     *
     * Autorização: vendedor vê só clientes que ele vendeu; gestor/admin/master
     * veem qualquer cliente (com o vendedor responsável).
     */
    public function historico(Request $request, $id)
    {
        $user = $request->user();
        $vendedorId = $user->vendedor?->id ?? null;
        $isGestor = in_array($user->perfil, ['gestor', 'admin', 'master']);

        $query = Cliente::where('id', $id);
        if (! $isGestor && $vendedorId) {
            $query->whereHas('vendas', fn ($q) => $q->where('vendedor_id', $vendedorId));
        }
        $cliente = $query->firstOrFail();

        // Vendas do cliente (para vendedor, só as dele).
        $vendasQuery = $cliente->vendas()->with('vendedor.user')->orderByDesc('data_venda');
        if (! $isGestor && $vendedorId) {
            $vendasQuery->where('vendedor_id', $vendedorId);
        }
        $vendas = $vendasQuery->get();

        // Pagamentos do cliente.
        $pagamentos = $cliente->pagamentos()->orderByDesc('data_pagamento')->orderByDesc('data_vencimento')->get();

        // Comissões do cliente (respeitando o escopo do vendedor).
        $comissoesQuery = \App\Models\Comissao::where('cliente_id', $cliente->id)
            ->with(['vendedor.user', 'gerente'])
            ->orderByDesc('competencia');
        if (! $isGestor && $vendedorId) {
            $comissoesQuery->where('vendedor_id', $vendedorId);
        }
        $comissoes = $comissoesQuery->get();

        // Vendedor responsável (venda mais recente).
        $vendaPrincipal = $vendas->first();
        $vendedorNome = $vendaPrincipal && $vendaPrincipal->vendedor
            ? ($vendaPrincipal->vendedor->user->name ?? $vendaPrincipal->vendedor->nome ?? 'N/A')
            : 'N/A';

        // Totais / ticket médio.
        $pagos = $pagamentos->filter(fn ($p) => in_array(strtoupper($p->status), ['RECEIVED', 'CONFIRMED', 'PAGO']));
        $totalPago = (float) $pagos->sum('valor');
        $numPagos = $pagos->count();
        $ticketMedioPagamento = $numPagos > 0 ? round($totalPago / $numPagos, 2) : 0.0;
        $ticketMedioVenda = $vendas->count() > 0 ? round((float) $vendas->avg('valor'), 2) : 0.0;

        $datasPagas = $pagos->map(fn ($p) => $p->data_pagamento)->filter()->values();

        return response()->json([
            'cliente' => [
                'id'        => $cliente->id,
                'nome'      => $cliente->nome_igreja ?? $cliente->nome,
                'responsavel' => $cliente->nome_responsavel ?? $cliente->nome,
                'documento' => $cliente->documento,
                'email'     => $cliente->email,
                'telefone'  => $cliente->telefone ?? $cliente->whatsapp,
                'status'    => $cliente->status,
                'vendedor'  => $vendedorNome,
                'asaas_customer_id' => $cliente->asaas_customer_id,
                'origem'    => $vendaPrincipal->origem ?? null,
            ],
            'resumo' => [
                'total_pago'          => $totalPago,
                'num_pagamentos'      => $numPagos,
                'ticket_medio'        => $ticketMedioPagamento,
                'ticket_medio_venda'  => $ticketMedioVenda,
                'num_vendas'          => $vendas->count(),
                'primeiro_pagamento'  => $datasPagas->min(),
                'ultimo_pagamento'    => $datasPagas->max(),
                'total_comissao_vendedor' => (float) $comissoes->sum('valor_comissao'),
                'total_comissao_gestor'   => (float) $comissoes->sum('valor_gerente'),
            ],
            'vendas' => $vendas->map(fn ($v) => [
                'id'        => $v->id,
                'valor'     => (float) $v->valor,
                'status'    => $v->status,
                'plano'     => $v->plano,
                'tipo_negociacao' => $v->tipo_negociacao,
                'forma_pagamento' => $v->forma_pagamento,
                'parcelas'  => $v->parcelas,
                'origem'    => $v->origem,
                'data_venda'=> $v->data_venda,
                'vendedor'  => $v->vendedor ? ($v->vendedor->user->name ?? $v->vendedor->nome ?? 'N/A') : 'N/A',
            ]),
            'pagamentos' => $pagamentos->map(fn ($p) => [
                'id'             => $p->id,
                'valor'          => (float) $p->valor,
                'status'         => $p->status,
                'forma'          => $p->forma_pagamento ?? $p->billing_type,
                'data_pagamento' => $p->data_pagamento,
                'data_vencimento'=> $p->data_vencimento,
            ]),
            'comissoes' => $comissoes->map(fn ($c) => [
                'id'             => $c->id,
                'competencia'    => $c->competencia,
                'tipo'           => $c->tipo_comissao,
                'valor_comissao' => (float) $c->valor_comissao,
                'valor_gerente'  => (float) $c->valor_gerente,
                'status'         => $c->status,
                'data_pagamento' => $c->data_pagamento,
                'vendedor'       => $c->vendedor ? ($c->vendedor->user->name ?? $c->vendedor->nome ?? 'N/A') : 'N/A',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(['message' => 'Cliente removido com sucesso']);
    }
}
