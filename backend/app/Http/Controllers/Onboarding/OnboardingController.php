<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\TermsAcceptance;
use App\Models\TermsDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function verTermos()
    {
        $termo = TermsDocument::ativoPorTipo('uso');
        
        if (!$termo) {
            $termo = TermsDocument::create([
                'tipo' => 'uso',
                'titulo' => 'Termos de Uso',
                'versao' => '1.0.0',
                'conteudo_html' => '<h1>Termos de Uso</h1><p>Bem-vindo ao Basiléia Vendas.</p>',
                'ativo' => true,
            ]);
        }

        return view('onboarding.termos', compact('termo'));
    }

    public function aceitarTermos(Request $request)
    {
        $request->validate([
            'termos_aceitos' => 'required|accepted',
            'terms_document_id' => 'required|exists:terms_documents,id',
        ]);

        // Registrar o aceite no log
        TermsAcceptance::registrar(
            auth()->id(),
            $request->terms_document_id,
            $request->ip(),
            $request->userAgent()
        );

        // Atualizar o usuário - IMPORTANTE: O model User deve ter 'termos_aceitos' no $fillable
        $user = auth()->user();
        $user->termos_aceitos = true;
        $user->termos_aceitos_em = now();
        $user->save();

        // Limpar cache de permissões se necessário
        
        $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);

        if ($splitAtivo && !$user->split_configurado) {
            return redirect()->route('onboarding.split');
        }

        return redirect()->route('dashboard')->with('iniciar_tour', true);
    }

    public function verSplit()
    {
        $user = auth()->user();
        
        if ($user->split_configurado) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.split');
    }

    public function ativarSplit(Request $request)
    {
        $request->validate([
            'asaas_wallet_id' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $user->vendedor->update([
            'asaas_wallet_id' => $request->asaas_wallet_id,
            'wallet_status' => 'pendente'
        ]);

        $user->update(['split_configurado' => true]);

        return redirect()->route('dashboard')->with('success', 'Configurações de Split enviadas para validação!');
    }

    public function pularSplit()
    {
        auth()->user()->update(['split_configurado' => true]);
        return redirect()->route('dashboard');
    }

    public function iniciarTour()
    {
        return view('onboarding.tour');
    }
}