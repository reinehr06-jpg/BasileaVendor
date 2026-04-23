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
        '2fa*',
        'login*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Se já está em uma rota de exceção, não faz nada
        if ($request->is($this->except)) {
            return $next($request);
        }

        // 1. Verificar Termos
        if (!$user->termos_aceitos) {
            \Illuminate\Support\Facades\Log::info('ONBOARDING_REDIRECT_TERMOS', ['user_id' => $user->id]);
            return redirect()->route('onboarding.termos');
        }

        // 2. Verificar Split (se ativado globalmente)
        $splitAtivo = \App\Models\Setting::get('asaas_split_global_ativo', false);
        if ($splitAtivo && !$user->split_configurado) {
            \Illuminate\Support\Facades\Log::info('ONBOARDING_REDIRECT_SPLIT', ['user_id' => $user->id]);
            return redirect()->route('onboarding.split');
        }

        return $next($request);
    }
}