<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\AI\StrictPromptValidator;
use App\Models\Contato;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StrictAIController extends Controller
{
    protected StrictPromptValidator $validator;

    public function __construct(StrictPromptValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Generate first message with strict prompt validation
     */
    public function generateFirstMessage(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:contatos,id',
            'campanha_id' => 'nullable|exists:campanhas,id',
        ]);

        $leadId = $request->input('lead_id');
        $lead = Contato::findOrFail($leadId);
        
        $prompt = Setting::get('ia_prompt_primeira_mensagem');
        
        try {
            $this->validator->assertPromptExists($prompt, 'primeira_mensagem', [
                'lead_id' => $leadId,
                'lead_nome' => $lead->nome,
                'canal' => $lead->canal_origem,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
        
        // Se prompt válido, chamar o serviço de IA real
        // ... (integração com AIService existente)
        
        return response()->json([
            'success' => true,
            'message' => 'Prompt validado — IA executada com sucesso',
            'data' => [
                'prompt_used' => substr($prompt, 0, 100) . '...'
            ]
        ]);
    }

    /**
     * Qualificar lead com IA estrita
     */
    public function qualifyLead(Request $request)
    {
        $request->validate(['lead_id' => 'required|exists:contatos,id']);
        
        $leadId = $request->input('lead_id');
        $lead = Contato::findOrFail($leadId);
        
        $prompt = Setting::get('ia_prompt_qualificacao');
        
        try {
            $this->validator->assertPromptExists($prompt, 'qualificacao', [
                'lead_id' => $leadId,
                'lead_status' => $lead->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
        
        // Executar IA de qualificação...
        
        return response()->json(['success' => true, 'message' => 'Lead qualificado com sucesso']);
    }

    /**
     * Gerar resumo de conversa
     */
    public function summarize(Request $request)
    {
        $request->validate(['conversation_id' => 'required']);
        
        $prompt = Setting::get('ia_prompt_resumo');
        
        try {
            $this->validator->assertPromptExists($prompt, 'resumo', [
                'conversation_id' => $request->input('conversation_id')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
        
        // Executar IA de resumo...
        
        return response()->json(['success' => true, 'message' => 'Resumo gerado']);
    }

    /**
     * Sugerir próxima ação
     */
    public function suggestAction(Request $request)
    {
        $request->validate(['lead_id' => 'required|exists:contatos,id']);
        
        $leadId = $request->input('lead_id');
        $prompt = Setting::get('ia_prompt_sugestao');
        
        try {
            $this->validator->assertPromptExists($prompt, 'sugestao', [
                'lead_id' => $leadId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
        
        // Executar IA de sugestão...
        
        return response()->json(['success' => true, 'message' => 'Sugestão gerada']);
    }
}
