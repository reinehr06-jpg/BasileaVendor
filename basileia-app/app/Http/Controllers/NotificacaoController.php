<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacao;

class NotificacaoController extends Controller
{
    /**
     * Marcar uma notificação como lida
     */
    public function marcarComoLida($id)
    {
        $notificacao = Notificacao::where('user_id', Auth::id())->findOrFail($id);
        $notificacao->marcarComoLida();
        
        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasComoLidas()
    {
        Notificacao::where('user_id', Auth::id())
            ->where('lida', false)
            ->update([
                'lida' => true,
                'lida_em' => now(),
            ]);
        
        return response()->json(['success' => true]);
    }
}
