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
            'asaas_wallet_id' => 'required|string|max:255',
        ]);

        $vendedor->update([
            'asaas_wallet_id' => $request->asaas_wallet_id,
            'wallet_status' => 'pendente',
        ]);

        return back()->with('success', 'Configurações de split atualizadas! Aguarde a validação do Master.');
    }
}
