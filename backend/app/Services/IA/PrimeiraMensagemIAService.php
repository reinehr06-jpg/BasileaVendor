<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\IAProviderInterface;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Log;

class PrimeiraMensagemIAService
{
    private IAProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->createProvider();
    }

    public function gerarSugestoes(string $contexto, int $quantidade = 5): array
    {
        return $this->provider->gerarSugestoes($contexto, $quantidade);
    }

    private function createProvider(): IAProviderInterface
    {
        $providerType = config('services.ia.provider', 'ollama');

        return match ($providerType) {
            'openai' => new OpenAIProvider(),
            'ollama', 'local' => new OllamaProvider(),
            default => new OllamaProvider(), // fallback
        };
    }
}
