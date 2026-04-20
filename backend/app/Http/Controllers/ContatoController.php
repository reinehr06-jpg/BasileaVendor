<?php

namespace App\Http\Controllers;

use App\Models\Contato;
use App\Models\Campanha;
use App\Models\User;
use App\Models\Vendedor;
use App\Services\AI\AIService;
use App\Services\AtribuicaoLeadService;
use App\Services\ExportarContatosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContatoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $query = Contato::with('campanha', 'agente', 'vendedor')->orderByDesc('entry_date');

            if ($user->perfil === 'vendedor') {
                $vendedor = Vendedor::where('user_id', $user->id)->first();
                if ($vendedor) {
                    $query->where('vendedor_id', $vendedor->id);
                }
            } elseif ($user->perfil === 'gestor') {
                $vendedoresIds = Vendedor::where('gestor_id', $user->id)->pluck('id');
                if ($vendedoresIds->isNotEmpty()) {
                    $query->whereIn('vendedor_id', $vendedoresIds);
                }
            }

            if ($request->campanha_id) $query->porCampanha($request->campanha_id);
            if ($request->canal) $query->porCanal($request->canal);
            if ($request->status) $query->porStatus($request->status);
            if ($request->agente_id) $query->porAgente($request->agente_id);
            if ($request->data_inicio && $request->data_fim) {
                $query->porPeriodo($request->data_inicio, $request->data_fim);
            }
            if ($request->busca) {
                $busca = $request->busca;
                $query->where(function ($q) use ($busca) {
                    $q->where('nome', 'like', "%$busca%")
                      ->orWhere('telefone', 'like', "%$busca%")
                      ->orWhere('whatsapp', 'like', "%$busca%")
                      ->orWhere('email', 'like', "%$busca%");
                });
            }

            $contatos = $query->paginate(50)->withQueryString();
        } catch (\Exception $e) {
            $contatos = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }

        try {
            $campanhas = Campanha::orderBy('nome')->get();
        } catch (\Exception $e) {
            $campanhas = collect([]);
        }

        try {
            $agentes = User::orderBy('name')->get();
        } catch (\Exception $e) {
            $agentes = collect([]);
        }

        return view('admin.contatos.index', compact('contatos', 'campanhas', 'agentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'campanha_id' => 'nullable|exists:campanhas,id',
            'canal_origem' => 'nullable|string',
        ]);

        $contato = Contato::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'whatsapp' => $request->whatsapp,
            'campanha_id' => $request->campanha_id,
            'canal_origem' => $request->canal_origem ?? 'manual',
            'status' => 'lead',
            'entry_date' => now(),
        ]);

        try {
            $scoreResult = app(AIService::class)->executar('score_lead', [
                'nome' => $contato->nome,
                'email' => $contato->email,
                'telefone' => $contato->telefone,
                'church_name' => $contato->nome_igreja,
                'source' => $contato->canal_origem,
                'campanha' => $contato->campanha?->nome,
            ], Auth::id());

            if (isset($scoreResult['score'])) {
                $contato->update([
                    'ai_score' => $scoreResult['score'],
                    'ai_score_motivo' => $scoreResult['motivo'] ?? null,
                    'ai_avaliado_em' => now(),
                ]);
            }

            $proximaAcaoResult = app(AIService::class)->executar('proxima_acao', [
                'interacoes' => 'Contato recém-criado',
                'dias_sem_contato' => 0,
                'ultimo_status' => 'lead',
            ], Auth::id());

            if (isset($proximaAcaoResult['acao'])) {
                $contato->update(['ai_proxima_acao' => $proximaAcaoResult['acao']]);
            }

            if (setting('atribuicao_automatica_lead', true)) {
                app(AtribuicaoLeadService::class)->atribuir($contato);
            }
        } catch (\Exception $e) {
            \Log::warning('IA_ERRO_AO_AVALIAR_CONTATO', [
                'contato_id' => $contato->id,
                'erro' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Contato criado com sucesso! ' . ($contato->ai_score ? "Score IA: {$contato->ai_score}/5" : ''));
    }

    public function drawer(Contato $contato)
    {
        $contato->load(['campanha', 'agente', 'vendedor', 'statusLogs.usuario', 'eventos.criador']);
        return response()->json($contato);
    }

    public function mudarStatus(Request $request, Contato $contato)
    {
        $request->validate([
            'status' => 'required|in:lead,convertido,perdido,lead_ruim',
            'motivo' => 'nullable|string|max:500',
        ]);

        $statusAnterior = $contato->status;
        $contato->cambiarStatus($request->status, $request->motivo);

        if (in_array($request->status, ['perdido', 'lead_ruim'])) {
            try {
                $motivoPerdaIA = app(AIService::class)->executar('motivo_perda', [
                    'conversa' => $contato->observacoes ?? 'Sem observações',
                    'historico' => $contato->statusLogs->pluck('motivo')->implode(' | '),
                ], Auth::id());

                if (isset($motivoPerdaIA['motivo']) && in_array($motivoPerdaIA['motivo'], ['PRECO', 'CONCORRENTE', 'SEM_INTERESSE', 'SEM_RESPOSTA', 'TIMING', 'OUTRO'])) {
                    $contato->update(['motivo_perda' => $motivoPerdaIA['motivo']]);
                }
            } catch (\Exception $e) {
                \Log::warning('IA_ERRO_MOTIVO_PERDA', [
                    'contato_id' => $contato->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        if ($request->status === 'convertido') {
            try {
                $proximaAcaoResult = app(AIService::class)->executar('proxima_acao', [
                    'interacoes' => 'Contato convertido em cliente',
                    'dias_sem_contato' => 0,
                    'ultimo_status' => 'convertido',
                ], Auth::id());

                if (isset($proximaAcaoResult['acao'])) {
                    $contato->update(['ai_proxima_acao' => 'Encaminhar para onboarding: ' . $proximaAcaoResult['acao']]);
                }
            } catch (\Exception $e) {
                \Log::warning('IA_ERRO_PROXIMA_ACAO_CONVERTIDO', [
                    'contato_id' => $contato->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true, 
            'status' => $contato->status,
            'motivo_perda' => $contato->motivo_perda,
        ]);
    }

    public function show(Contato $contato)
    {
        $contato->load(['campanha', 'agente', 'vendedor', 'statusLogs.usuario', 'eventos']);
        return view('admin.contatos.show', compact('contato'));
    }

    public function update(Request $request, Contato $contato)
    {
        $contato->update($request->only([
            'nome', 'email', 'telefone', 'whatsapp', 'documento',
            'tags', 'observacoes', 'agente_id', 'vendedor_id',
        ]));

        return back()->with('success', 'Contato atualizado!');
    }

    public function gerarObservacao(Contato $contato)
    {
        try {
            $result = app(AIService::class)->executar('observacao_contato', [
                'nome' => $contato->nome,
                'canal' => $contato->canal_origem,
                'campanha' => $contato->campanha?->nome,
                'telefone' => $contato->telefone,
            ], Auth::id());

            if (isset($result['observacao'])) {
                $contato->update([
                    'ai_observacao' => $result['observacao'],
                    'observacoes' => $contato->observacoes . "\n\n[IA - " . now()->format('d/m/Y H:i') . "]: " . $result['observacao'],
                ]);
                return response()->json(['success' => true, 'observacao' => $result['observacao']]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'erro' => $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'erro' => 'Não foi possível gerar observação'], 500);
    }

    public function exportar(Request $request)
    {
        $query = Contato::with('campanha');

        if ($request->campanha_id) $query->porCampanha($request->campanha_id);
        if ($request->canal) $query->porCanal($request->canal);
        if ($request->status) $query->porStatus($request->status);
        if ($request->data_inicio && $request->data_fim) {
            $query->porPeriodo($request->data_inicio, $request->data_fim);
        }

        $contatos = $query->get();

        $exportService = app(ExportarContatosService::class);
        $filename = 'contatos_export_' . now()->format('Ymd_His');
        
        $data = $exportService->toArray($contatos);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]));
                
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }
            
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}