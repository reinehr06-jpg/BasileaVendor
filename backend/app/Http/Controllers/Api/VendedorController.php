<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    public function index(Request $request)
    {
        $vendedores = Vendedor::with(['user', 'gestor', 'vendas'])->get()->map(function ($v) {
            $equipeNome = 'Sem Equipe';
            if ($v->equipe_id) {
                $equipe = \App\Models\Equipe::find($v->equipe_id);
                $equipeNome = $equipe ? $equipe->nome : 'Sem Equipe';
            }

            return [
                'id' => $v->id,
                'nome' => $v->user->name ?? 'Sem Nome',
                'email' => $v->user->email ?? '',
                'telefone' => $v->telefone ?? '',
                'equipe' => $equipeNome,
                'gestor' => $v->gestor->name ?? 'Sem Gestor',
                'status' => ucfirst($v->status ?? 'ativo'),
                'vendas' => $v->vendas->count(),
                'avatarColor' => 'bg-[#7C3AED]',
                'cpfCnpj' => '' // Add if available
            ];
        });

        return response()->json($vendedores);
    }
}
