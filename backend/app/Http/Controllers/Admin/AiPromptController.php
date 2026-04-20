<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AiPromptController extends Controller
{
    public function index()
    {
        $prompts = AiPrompt::with('creator')
            ->orderBy('funcao')
            ->orderBy('nome')
            ->get();

        $funcoes = AiPrompt::funcoesDisponiveis();
        $cores = AiPrompt::coresPredefinidas();

        return view('admin.ia.prompts.index', compact('prompts', 'funcoes', 'cores'));
    }

    public function create()
    {
        $funcoes = AiPrompt::funcoesDisponiveis();
        $cores = AiPrompt::coresPredefinidas();

        return view('admin.ia.prompts.create', compact('funcoes', 'cores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'funcao' => 'required|string|max:100|' . Rule::in(array_keys(AiPrompt::funcoesDisponiveis())),
            'cor' => 'required|string|max:7',
            'prompt_personalizado' => 'required|string|min:20',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['criado_por'] = Auth::id();

        AiPrompt::create($validated);

        return redirect()->route('admin.ia.prompts.index')
            ->with('success', 'Prompt customizado criado com sucesso!');
    }

    public function edit(AiPrompt $prompt)
    {
        $funcoes = AiPrompt::funcoesDisponiveis();
        $cores = AiPrompt::coresPredefinidas();

        return view('admin.ia.prompts.edit', compact('prompt', 'funcoes', 'cores'));
    }

    public function update(Request $request, AiPrompt $prompt)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'funcao' => 'required|string|max:100|' . Rule::in(array_keys(AiPrompt::funcoesDisponiveis())),
            'cor' => 'required|string|max:7',
            'prompt_personalizado' => 'required|string|min:20',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->boolean('ativo', true);

        $prompt->update($validated);

        return redirect()->route('admin.ia.prompts.index')
            ->with('success', 'Prompt atualizado com sucesso!');
    }

    public function destroy(AiPrompt $prompt)
    {
        $prompt->delete();

        return redirect()->route('admin.ia.prompts.index')
            ->with('success', 'Prompt excluído com sucesso!');
    }

    public function toggle(AiPrompt $prompt)
    {
        $prompt->update(['ativo' => !$prompt->ativo]);

        $status = $prompt->ativo ? 'ativado' : 'desativado';

        return back()->with('success', "Prompt {$status} com sucesso!");
    }
}