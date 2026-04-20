<?php

namespace App\Http\Middleware;

use App\Models\TermsAcceptance;
use App\Models\TermsDocument;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureFluxoOnboarding
{
    private array $except = [
        'onboarding*',
        'logout',
        'termos/download*',
        'api/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $termoAtivo = TermsDocument::ativoPorTipo('uso');
        
        if ($termoAtivo) {
            $temAceite = TermsAcceptance::where('user_id', $user->id)
                ->where('terms_document_id', $termoAtivo->id)
                ->exists();
            
            if (!$temAceite || !$user->termos_aceitos) {
                return redirect()->route('onboarding.termos');
            }
        }

        $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);
        
        if ($splitAtivo && !$user->split_configurado) {
            return redirect()->route('onboarding.split');
        }

        return $next($request);
    }
}