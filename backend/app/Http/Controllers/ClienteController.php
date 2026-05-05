<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Services\ClienteStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    /**
     * Tela de listagem de clientes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isMaster = $user->perfil === 'master';

        $query = Cliente::query();

        if ($isMaster) {
            // Master vê todos os clientes do sistema
        } else {
            // Vendedor vê todos os clientes que possuem alguma venda atribuída a ele ou à sua equipe
            $query->whereHas('vendas', function ($q) use ($user) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('usuario_id', $user->id)
                      ->orWhere('gestor_id', $user->id);
                });
            });
        }

        // Sincronizar status antes de exibir (opcional, mas garante dados novos)
        // $clientesParaSync = (clone $query)->with('vendas.pagamentos')->get();
        // ClienteStatusService::sincronizarStatus($clientesParaSync);

        // Filtros Adicionais (Status e Busca)
        if ($request->filled('busca')) {
            $busca = $request->get('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('documento', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $clientes = $query->with('vendas')->orderBy('created_at', 'desc')->paginate(15);

        // Cards de Resumo — conta todos os clientes que possuem vendas vinculadas
        if ($isMaster) {
            $allClientes = Cliente::whereHas('vendas');
        } else {
            // Para vendedor/gestor, resumo deve refletir clientes atribuídos à ele ou equipe
            $allClientes = Cliente::whereHas('vendas', function ($q) use ($user) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('usuario_id', $user->id)
                      ->orWhere('gestor_id', $user->id);
                });
            });
        }
        $cards = [
            'total' => (clone $allClientes)->count(),
            'ativos' => (clone $allClientes)->where('status', 'ativo')->count(),
            'pendentes' => (clone $allClientes)->where('status', 'pendente')->count(),
            'inadimplentes' => (clone $allClientes)->where('status', 'inadimplente')->count(),
            'churn' => (clone $allClientes)->where('status', 'churn')->count(),
            'cancelados' => (clone $allClientes)->where('status', 'cancelado')->count(),
        ];

        $viewPath = $isMaster ? 'master.clientes.index' : 'vendedor.clientes.index';

        if ($request->expectsJson()) {
            return response()->json([
                'clientes' => $clientes,
                'cards' => $cards,
                'isMaster' => $isMaster
            ]);
        }

        return view($viewPath, compact('clientes', 'cards', 'isMaster'));
    }

    /**
     * Tela de Detalhes do Cliente
     */
    public function show($id)
    {
        $user = Auth::user();
        $isMaster = $user->perfil === 'master';

        $cliente = Cliente::with(['vendas' => function ($q) use ($user, $isMaster) {
            if (! $isMaster) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('usuario_id', $user->id)
                      ->orWhere('gestor_id', $user->id);
                });
            }
            $q->with('vendedor.user', 'pagamentos');
        }])->findOrFail($id);

        // Sincronizar status deste cliente
        ClienteStatusService::atualizarCliente($cliente);
        $cliente->refresh();

        // Se for vendedor, garantir que ele tem acesso a este cliente
        if (! $isMaster) {
            $temAcesso = $cliente->vendas()->whereHas('vendedor', function ($q) use ($user) {
                $q->where('usuario_id', $user->id);
            })->exists();

            if (! $temAcesso) {
                abort(403, 'Acesso não autorizado a este cliente.');
            }
        }

        // Buscar histórico de vendas e pagamentos do cliente
        $vendas = $cliente->vendas()->orderBy('created_at', 'desc')->get();

        // Calcular totais
        $totalVendas = $vendas->count();
        $valorTotalPago = $vendas->whereIn('status', ['Pago', 'PAGO', 'pago'])->sum('valor');
        $ticketMedio = $totalVendas > 0 ? $valorTotalPago / $totalVendas : 0;

        // Obter pagamentos através das vendas
        $pagamentos = collect();
        foreach ($vendas as $venda) {
            foreach ($venda->pagamentos as $pagamento) {
                $pagamentos->push($pagamento);
            }
        }
        $pagamentos = $pagamentos->sortByDesc('data_vencimento')->values();

        $viewPath = $isMaster ? 'master.clientes.show' : 'vendedor.clientes.show';

        if (request()->expectsJson()) {
            return response()->json([
                'cliente' => $cliente,
                'vendas' => $vendas,
                'pagamentos' => $pagamentos,
                'isMaster' => $isMaster,
                'metrics' => [
                    'totalVendas' => $totalVendas,
                    'valorTotalPago' => $valorTotalPago,
                    'ticketMedio' => $ticketMedio
                ]
            ]);
        }

        return view($viewPath, compact('cliente', 'vendas', 'pagamentos', 'isMaster', 'totalVendas', 'valorTotalPago', 'ticketMedio'));
    }

    /**
     * Atualizar Status do Cliente via API/AJAX
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:ativo,pendente,inadimplente,cancelado,churn',
        ]);

        $cliente = Cliente::findOrFail($id);

        // Authorization check
        if ($user->perfil !== 'master') {
            $temAcesso = $cliente->vendas()->whereHas('vendedor', function ($q) use ($user) {
                $q->where('usuario_id', $user->id);
            })->exists();
            if (! $temAcesso) {
                return response()->json(['error' => 'Acesso não autorizado.'], 403);
            }
        }

        $cliente->status = $request->status;
        $cliente->save();

        return response()->json([
            'success' => true,
            'message' => 'Status do cliente atualizado com sucesso.',
            'status' => $cliente->status,
        ]);
    }
}
