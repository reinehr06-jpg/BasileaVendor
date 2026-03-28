<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\Vendedor;

class VendedorConfiguracaoController extends Controller
{
    /**
     * Exibir página de configurações do vendedor (Comissões e Repasse)
     */
    public function index()
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        
        if (!$vendedor) {
            return redirect()->route('vendedor.dashboard')->with('error', 'Perfil de vendedor não encontrado.');
        }
        
        $splitGlobalAtivo = Setting::get('asaas_split_global_ativo', false);
        
        return view('vendedor.configuracoes.split', compact('vendedor', 'splitGlobalAtivo'));
    }

    /**
     * Atualizar configurações de split do vendedor
     * NOTA: Comissão só pode ser alterada pelo Master
     */
    public function updateSplit(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;
        
        if (!$vendedor) {
            return back()->with('error', 'Perfil de vendedor não encontrado.');
        }
        
        // Verificar se split global está ativo
        $splitGlobalAtivo = Setting::get('asaas_split_global_ativo', false);
        if (!$splitGlobalAtivo) {
            return back()->with('error', 'O split global não está ativo. Entre em contato com o administrador.');
        }
        
        // Se já tem wallet validado, não permitir alteração
        if ($vendedor->wallet_status === 'validado') {
            return back()->with('error', 'Sua carteira já está validada. Entre em contato com o Master para alterações.');
        }

        $request->validate([
            'split_ativo' => 'nullable|boolean',
            'asaas_wallet_id' => 'nullable|string|max:255',
            'tipo_split' => 'required|in:percentual,fixo',
            'valor_split_inicial' => 'required|numeric|min:0',
            'valor_split_recorrencia' => 'required|numeric|min:0',
        ]);

        $vendedor->update([
            'split_ativo' => $request->boolean('split_ativo'),
            'asaas_wallet_id' => $request->asaas_wallet_id,
            'tipo_split' => $request->tipo_split,
            'valor_split_inicial' => $request->valor_split_inicial,
            'valor_split_recorrencia' => $request->valor_split_recorrencia,
            'wallet_status' => 'pendente', // Resetar status ao alterar wallet
        ]);

        return back()->with('success', 'Configurações de split atualizadas! Aguarde a validação do Master.');
    }
}
