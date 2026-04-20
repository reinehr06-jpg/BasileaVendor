<?php

namespace App\Services\AI\Contracts;

interface IAProviderInterface
{
    /**
     * Gera sugestões de primeira mensagem baseada no contexto
     *
     * @param string $contexto Descrição do contexto/lead
     * @param int $quantidade Número de sugestões desejadas
     * @return array Lista de sugestões de mensagem
     */
    public function gerarSugestoes(string $contexto, int $quantidade = 5): array;
}
