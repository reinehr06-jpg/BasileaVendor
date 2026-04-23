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

        // 1. Verificar Termos (Busca direta no DB para evitar cache de Model)
        $termosAceitos = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->value('termos_aceitos');

        if (!$termosAceitos) {
            // Backup check: verificar na tabela de aceites
            $temRegistro = \Illuminate\Support\Facades\DB::table('terms_acceptances')
                ->where('user_id', $user->id)
                ->exists();
            
            if (!$temRegistro) {
                \Illuminate\Support\Facades\Log::info('ONBOARDING_REDIRECT_TERMOS_FORCED', [
                    'user_id' => $user->id,
                    'db_val' => $termosAceitos
                ]);
                return redirect()->route('onboarding.termos');
            }
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