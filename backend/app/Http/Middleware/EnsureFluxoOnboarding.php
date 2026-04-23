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
        // Bypass temporário para resolver loop de 500
        return $next($request);
    }
}