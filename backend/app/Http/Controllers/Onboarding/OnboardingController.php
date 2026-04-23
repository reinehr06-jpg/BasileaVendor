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
                'conteudo_html' => '<h1>Termos de Uso</h1><p>Bem-vindo ao Basileia Vendas.</p>',
                'ativo' => true,
            ]);
        }

        return view('onboarding.termos', compact('termo'));
    }

    public function aceitarTermos(Request $request)
    {
        // Bypass total para entrar no sistema
        return redirect()->route('dashboard');
    }

    public function verSplit()
    {
        return redirect()->route('dashboard');
    }

    public function ativarSplit(Request $request)
    {
        return redirect()->route('dashboard');
    }

    public function pularSplit()
    {
        return redirect()->route('dashboard');
    }

    public function iniciarTour()
    {
        return view('onboarding.tour');
    }
}