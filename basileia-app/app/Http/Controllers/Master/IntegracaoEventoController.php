<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegracaoEventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::with('creator')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('master.integracoes.eventos', compact('eventos'));
    }

    public function store(Request $request, AsaasService $asaas)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'valor' => 'required|numeric|min:0.01',
            'vagas_total' => 'required|integer|min:1|max:10000',
            'whatsapp_vendedor' => 'required|string|max:20',
            'telefone_vendedor' => 'nullable|string|max:20',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $slug = $request->slug ?: Str::slug($request->titulo);
        $baseSlug = $slug;
        $i = 1;
        while (Evento::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $checkoutUrl = url("/co/evento/{$slug}");

        $evento = Evento::create([
            'slug' => $slug,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'moeda' => 'BRL',
            'vagas_total' => $request->vagas_total,
            'whatsapp_vendedor' => preg_replace('/\D/', '', $request->whatsapp_vendedor),
            'telefone_vendedor' => $request->telefone_vendedor ? preg_replace('/\D/', '', $request->telefone_vendedor) : null,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'status' => 'ativo',
            'checkout_url' => $checkoutUrl,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', "Evento criado! Link: {$checkoutUrl}");
    }

    public function toggle(Evento $evento)
    {
        if ($evento->status === 'ativo') {
            $evento->update(['status' => 'expirado']);
        } elseif ($evento->status === 'expirado' && $evento->vagasRestantes() > 0) {
            $evento->update(['status' => 'ativo']);
        }

        return back()->with('success', 'Status do evento atualizado');
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();

        return back()->with('success', 'Evento removido');
    }
}
