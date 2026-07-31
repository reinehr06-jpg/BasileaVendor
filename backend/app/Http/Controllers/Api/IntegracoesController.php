<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class IntegracoesController extends Controller
{
    public function getStatus()
    {
        $asaasKey = Setting::get('asaas_api_key');
        $split = Setting::get('split_enabled');
        $meta = Setting::get('meta_token');
        $ia = Setting::get('openai_api_key');

        return response()->json([
            'asaas' => $asaasKey ? 'Conectado' : 'Disponível',
            'split' => $split ? 'Ativo' : 'Desativado',
            'meta' => $meta ? 'Conectado' : 'Pendente',
            'ia' => $ia ? 'Ativo' : 'Disponível',
            'checkout' => 'Configurado', // Assumindo estático por agora
            'email' => 'Pendente',
            'chat' => 'Disponível',
            'webhooks' => 'Disponível'
        ]);
    }
}
