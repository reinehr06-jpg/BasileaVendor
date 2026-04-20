<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IAEvaluation;
use App\Models\Chat\ChatProviderConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IAController extends Controller
{
    /**
     * Obter lista de modelos/provedores disponíveis
     */
    public function providers()
    {
        $providers = ChatProviderConfig::where('is_active', true)->get();
        return response()->json($providers);
    }

    /**
     * Testar um prompt (Sandbox)
     */
    public function test(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:chat_provider_configs,id',
            'prompt' => 'required|string',
        ]);

        $config = ChatProviderConfig::findOrFail($request->provider_id);
        
        // Simulação de resposta da IA (visto que não integraremos com APIs externas agora por seguranca)
        // Em um cenário real, aqui chamaríamos o serviço de IA configurado.
        
        $response = "Esta é uma resposta simulada para o modelo {$config->name}. Em um ambiente de produção real, o prompt seria enviado para a API de LLM correspondente.";

        return response()->json([
            'response' => $response,
            'model' => $config->name,
        ]);
    }

    /**
     * Salvar avaliação do prompt
     */
    public function evaluate(Request $request)
    {
        $request->validate([
            'ia_model' => 'required|string',
            'prompt' => 'required|string',
            'response' => 'required|string',
            'approved' => 'required|boolean',
            'disapproval_reason' => 'nullable|string',
        ]);

        $evaluation = IAEvaluation::create([
            'user_id' => Auth::id(),
            'ia_model' => $request->ia_model,
            'prompt' => $request->prompt,
            'response' => $request->response,
            'approved' => $request->approved,
            'disapproval_reason' => $request->disapproval_reason,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avaliação salva com sucesso e integrada à memória da IA.',
            'evaluation' => $evaluation
        ]);
    }

    /**
     * Obter histórico de avaliações (Memória)
     */
    public function history()
    {
        $history = IAEvaluation::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($history);
    }
}
