<?php

namespace App\Http\Controllers;

use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIServiceController extends Controller
{
    public function sugestaoResposta(Request $request)
    {
        $request->validate([
            'mensagens' => 'required|array',
            'mensagens.*.texto' => 'required|string',
        ]);

        $mensagens = $request->input('mensagens');
        $userId = Auth::id();

        $result = app(AIService::class)->executar('sugestao_resposta', [
            'mensagens' => $mensagens,
        ], $userId);

        return response()->json($result);
    }

    public function proximaAcao(Request $request)
    {
        $request->validate([
            'historico' => 'required|array',
            'dias_sem_contato' => 'required|integer',
            'ultimo_status' => 'nullable|string',
        ]);

        $historico = $request->input('historico');
        $userId = Auth::id();

        $result = app(AIService::class)->executar('proxima_acao', [
            'interacoes' => implode("\n", $historico),
            'dias_sem_contato' => $request->input('dias_sem_contato'),
            'ultimo_status' => $request->input('ultimo_status'),
        ], $userId);

        return response()->json($result);
    }

    public function scoreLead(Request $request)
    {
        $request->validate([
            'nome' => 'nullable|string',
            'email' => 'nullable|string',
            'telefone' => 'nullable|string',
            'church_name' => 'nullable|string',
            'members_count' => 'nullable|integer',
            'source' => 'nullable|string',
            'campanha' => 'nullable|string',
        ]);

        $dados = $request->only([
            'nome', 'email', 'telefone', 'church_name', 
            'members_count', 'source', 'campanha'
        ]);

        $userId = Auth::id();

        $result = app(AIService::class)->executar('score_lead', $dados, $userId);

        return response()->json($result);
    }

    public function resumoConversa(Request $request)
    {
        $request->validate([
            'mensagens' => 'required|array',
            'mensagens.*.remetente' => 'nullable|string',
            'mensagens.*.texto' => 'required|string',
        ]);

        $mensagens = $request->input('mensagens');
        $userId = Auth::id();

        $result = app(AIService::class)->executar('resumo_conversa', [
            'mensagens' => $mensagens,
        ], $userId);

        return response()->json($result);
    }

    public function observacaoContato(Request $request)
    {
        $request->validate([
            'nome_cliente' => 'nullable|string',
            'ultimo_contato' => 'nullable|string',
            'historico' => 'nullable|string',
        ]);

        $nomeCliente = $request->input('nome_cliente', 'Cliente');
        $ultimoContato = $request->input('ultimo_contato', now()->format('d/m/Y'));
        $historico = $request->input('historico', '');

        $userId = Auth::id();

        $result = app(AIService::class)->executar('observacao_contato', [
            'nome_cliente' => $nomeCliente,
            'ultimo_contato' => $ultimoContato,
            'historico' => $historico,
        ], $userId);

        return response()->json($result);
    }

    public function analiseVendedor(Request $request)
    {
        $request->validate([
            'vendedor_id' => 'required|integer',
            'mes' => 'required|string',
        ]);

        // Dispatch job para processamento assíncrono
        $job = new \App\Jobs\GerarAnaliseVendedorJob(
            $request->input('vendedor_id'),
            $request->input('mes')
        );

        dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Análise sendo gerada em background',
        ]);
    }

    public function analiseCampanha(Request $request)
    {
        $request->validate([
            'campanha_id' => 'required|integer',
        ]);

        // Dispatch job para processamento assíncrono
        $job = new \App\Jobs\GerarAnaliseCampanhaJob(
            $request->input('campanha_id')
        );

        dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Análise sendo gerada em background',
        ]);
    }
}