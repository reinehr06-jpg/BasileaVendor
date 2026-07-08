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
                'cpfCnpj' => '' // Add se tiver no DB
            ];
        });

        return response()->json($vendedores);
    }

    public function show($id)
    {
        $vendedor = Vendedor::with(['user', 'gestor', 'vendas'])->findOrFail($id);
        
        return response()->json([
            'id' => $vendedor->id,
            'nome' => $vendedor->user->name ?? '',
            'email' => $vendedor->user->email ?? '',
            'telefone' => $vendedor->telefone ?? '',
            'equipe_id' => $vendedor->equipe_id,
            'gestor_id' => $vendedor->gestor_id,
            'is_gestor' => $vendedor->is_gestor,
            'comissao' => $vendedor->comissao,
            'percentual_comissao' => $vendedor->percentual_comissao,
            'status' => $vendedor->status ?? 'ativo',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|string|min:6',
            'telefone' => 'nullable|string',
            'equipe_id' => 'nullable|exists:equipes,id',
            'gestor_id' => 'nullable|exists:users,id',
            'is_gestor' => 'boolean',
            'percentual_comissao' => 'nullable|numeric',
        ]);

        \DB::beginTransaction();
        try {
            // Cria o usuário
            $user = \App\Models\User::create([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['senha']),
                'perfil' => empty($validated['is_gestor']) ? 'Vendedor' : 'Gestor',
                'status' => 'ativo'
            ]);

            // Cria o vendedor associado
            $vendedor = Vendedor::create([
                'usuario_id' => $user->id,
                'telefone' => $validated['telefone'] ?? null,
                'equipe_id' => $validated['equipe_id'] ?? null,
                'gestor_id' => $validated['gestor_id'] ?? null,
                'is_gestor' => $validated['is_gestor'] ?? false,
                'percentual_comissao' => $validated['percentual_comissao'] ?? 0,
                'status' => 'ativo',
            ]);

            \DB::commit();
            return response()->json(['success' => true, 'id' => $vendedor->id], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $vendedor = Vendedor::findOrFail($id);
        $user = $vendedor->user;

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'senha' => 'nullable|string|min:6',
            'telefone' => 'nullable|string',
            'equipe_id' => 'nullable|exists:equipes,id',
            'gestor_id' => 'nullable|exists:users,id',
            'is_gestor' => 'boolean',
            'percentual_comissao' => 'nullable|numeric',
            'status' => 'string'
        ]);

        \DB::beginTransaction();
        try {
            $user->update([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'perfil' => empty($validated['is_gestor']) ? 'Vendedor' : 'Gestor',
            ]);

            if (!empty($validated['senha'])) {
                $user->update(['password' => \Hash::make($validated['senha'])]);
            }

            if (isset($validated['status'])) {
                $user->update(['status' => $validated['status']]);
            }

            $vendedor->update([
                'telefone' => array_key_exists('telefone', $validated) ? $validated['telefone'] : $vendedor->telefone,
                'equipe_id' => $validated['equipe_id'] ?? $vendedor->equipe_id,
                'gestor_id' => $validated['gestor_id'] ?? $vendedor->gestor_id,
                'is_gestor' => $validated['is_gestor'] ?? $vendedor->is_gestor,
                'percentual_comissao' => $validated['percentual_comissao'] ?? $vendedor->percentual_comissao,
                'status' => $validated['status'] ?? $vendedor->status,
            ]);

            \DB::commit();
            return response()->json(['success' => true, 'id' => $vendedor->id]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $vendedor = Vendedor::findOrFail($id);
        $user = $vendedor->user;

        // Desativa em vez de deletar fisicamente
        $vendedor->update(['status' => 'inativo']);
        if ($user) {
            $user->update(['status' => 'inativo']);
        }

        return response()->json(['success' => true]);
    }
}
