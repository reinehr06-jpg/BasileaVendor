<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\Venda;
use App\Services\Commission\CommissionService;

/**
 * DEPRECATED como implementação própria.
 *
 * A lógica de comissão foi consolidada em App\Services\Commission\CommissionService
 * (motor único). Esta classe permanece apenas como fachada para não quebrar os
 * chamadores existentes (ex.: ProcessAsaasEventsCommand). Toda a regra de negócio
 * e a trava de idempotência vivem agora no motor único.
 */
class CommissionEngineService
{
    /**
     * Processa e gera comissões para um pagamento (webhook ou manual).
     * Delega ao motor único.
     */
    public static function processarPagamento(Pagamento $pagamento, Venda $venda)
    {
        return CommissionService::gerarParaPagamento($pagamento);
    }
}
