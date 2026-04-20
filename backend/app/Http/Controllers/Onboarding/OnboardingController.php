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
                'conteudo_html' => '<h1>Termos de Uso</h1><p>Bem-vindo ao Basileia Vendas. Ao utilizar este sistema, você concorda com os termos aqui estabelecidos.</p><h2>1. Uso do Sistema</h2><p>O sistema deve ser utilizado de acordo com as políticas internas da organização.</p><h2>2. Privacidade</h2><p>Seus dados serão tratados conforme nossa política de privacidade.</p><h2>3. Responsabilidades</h2><p>O usuário é responsável por manter suas credenciais de acesso seguras.</p>',
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

        TermsAcceptance::registrar(
            auth()->id(),
            $request->terms_document_id,
            $request->ip(),
            $request->userAgent()
        );

        auth()->user()->update([
            'termos_aceitos' => true,
            'termos_aceitos_em' => now(),
        ]);

        $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);

        if ($splitAtivo) {
            return redirect()->route('onboarding.split');
        }

        return redirect()->route('dashboard')->with('iniciar_tour', true);
    }

    public function verSplit()
    {
        $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);
        abort_unless($splitAtivo, 403);

        return view('onboarding.split');
    }

    public function ativarSplit(Request $request)
    {
        $request->validate(['confirmar_split' => 'required|accepted']);

        auth()->user()->update(['split_configurado' => true]);

        return redirect()->route('dashboard')->with('iniciar_tour', true);
    }

    public function pularSplit()
    {
        auth()->user()->update(['split_configurado' => true]);
        return redirect()->route('dashboard')->with('iniciar_tour', true);
    }

    public function iniciarTour()
    {
        return view('onboarding.tour');
    }
}