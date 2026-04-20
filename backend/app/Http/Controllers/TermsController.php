<?php

namespace App\Http\Controllers;

use App\Models\TermsDocument;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function index()
    {
        $termos = TermsDocument::orderByDesc('created_at')->get();
        return view('master.termos.index', compact('termos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'titulo' => 'required|string|max:255',
            'versao' => 'required|string|max:20',
            'conteudo_html' => 'required',
        ]);

        TermsDocument::create($request->all());

        return back()->with('success', 'Termo criado com sucesso!');
    }

    public function update(Request $request, TermsDocument $termo)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'versao' => 'required|string|max:20',
            'conteudo_html' => 'required',
        ]);

        $termo->update($request->only(['titulo', 'versao', 'conteudo_html']));

        return back()->with('success', 'Termo atualizado!');
    }

    public function destroy(TermsDocument $termo)
    {
        $termo->delete();
        return back()->with('success', 'Termo removido!');
    }

    public function download(TermsDocument $termo)
    {
        $html = "<html><head><meta charset='UTF-8'><title>{$termo->titulo}</title></head><body>{$termo->conteudo_html}</body></html>";
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$termo->titulo}-v{$termo->versao}.html\"");
    }

    public function toggleAtivo(TermsDocument $termo)
    {
        $termo->update(['ativo' => !$termo->ativo]);
        return back()->with('success', $termo->ativo ? 'Termo ativado!' : 'Termo desativado!');
    }
}