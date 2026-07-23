<?php

namespace App\Services\Commission;

use Carbon\Carbon;

/**
 * Calculador PURO de comissão (sem dependência de banco/Eloquent).
 *
 * Implementa as 5 regras de negócio da Basiléia:
 *
 *  1) Comissão INICIAL: primeira venda paga -> % inicial do vendedor.
 *  2) Comissão de RECORRÊNCIA: pagamentos seguintes -> % (menor) de recorrência.
 *  3) GESTOR: recebe uma % (definida no cadastro) das vendas do vendedor,
 *     desde que o vendedor esteja vinculado a um gestor (gestor_id).
 *  4) GESTOR em venda direta (sem gestor acima dele) -> recebe SÓ a parte de
 *     vendedor; nenhuma comissão de gestor é gerada.
 *  5) Venda ANUAL/PARCELADA no cartão (parcelas > 1): em vez de pingar a cada
 *     parcela, a comissão é ANTECIPADA 100% na 1ª parcela paga e as demais
 *     parcelas não geram nada. O valor antecipado espelha o fluxo mensal:
 *     (% inicial sobre 1 parcela) + (% recorrência sobre as parcelas restantes).
 *
 *  Trava de tempo (regra do fim do mês): a comissão de RECORRÊNCIA só é gerada
 *  se o pagamento foi confirmado até o ÚLTIMO DIA do mês do vencimento
 *  (28/29/30/31, conforme o mês). Pagou depois disso -> não gera aquele ciclo.
 *
 * Este objeto não sabe nada sobre banco; recebe números e datas e devolve o
 * quanto pagar. Isso o torná testável de forma isolada e determinística.
 */
class CommissionCalculator
{
    /**
     * @param array $in {
     *   tipo_negociacao: string  (avulso|mensal|anual|parcelado)
     *   parcelas: int
     *   valor_total: float       valor total da venda (para o modelo antecipado)
     *   pagamento_valor: float   valor deste pagamento
     *   pagamento_data: string   data de pagamento (Y-m-d)
     *   vencimento_data: ?string data de vencimento da fatura (Y-m-d) ou null
     *   perc_inicial: float
     *   perc_recorrencia: float
     *   perc_gestor_inicial: float
     *   perc_gestor_recorrencia: float
     *   tem_gestor: bool         vendedor tem gestor_id válido acima dele
     *   primeira_comissao_da_venda: bool  não existe nenhuma comissão para a venda ainda
     * }
     * @return array {
     *   gerar: bool
     *   motivo: string           (quando gerar=false)
     *   tipo: string             inicial_antecipada|inicial|recorrencia
     *   valor_vendedor: float
     *   valor_gestor: float
     *   perc_vendedor: float
     *   perc_gestor: float
     * }
     */
    public static function calcular(array $in): array
    {
        $parcelas   = max(1, (int) ($in['parcelas'] ?? 1));
        $tipo       = strtolower((string) ($in['tipo_negociacao'] ?? 'avulso'));
        $temGestor  = (bool) ($in['tem_gestor'] ?? false);
        $primeira   = (bool) ($in['primeira_comissao_da_venda'] ?? false);

        $percIni  = (float) ($in['perc_inicial'] ?? 0);
        $percRec  = (float) ($in['perc_recorrencia'] ?? 0);
        $percGIni = (float) ($in['perc_gestor_inicial'] ?? 0);
        $percGRec = (float) ($in['perc_gestor_recorrencia'] ?? 0);

        // ─────────────────────────────────────────────────────────────
        // MODELO ANTECIPADO (parcelado/anual no cartão, parcelas > 1)
        // ─────────────────────────────────────────────────────────────
        if ($parcelas > 1) {
            // Só a PRIMEIRA parcela paga gera comissão; as demais, nada.
            if (! $primeira) {
                return self::naoGerar('parcela_subsequente_antecipado');
            }

            $valorTotal = (float) ($in['valor_total'] ?? 0);
            $parcela    = $parcelas > 0 ? round($valorTotal / $parcelas, 2) : 0.0;
            $restantes  = $parcelas - 1;

            $vend = round(($parcela * $percIni / 100) + ($parcela * $percRec / 100 * $restantes), 2);
            $gest = $temGestor
                ? round(($parcela * $percGIni / 100) + ($parcela * $percGRec / 100 * $restantes), 2)
                : 0.0;

            return self::gerar('inicial_antecipada', $vend, $gest, $percIni, $temGestor ? $percGIni : 0);
        }

        // ─────────────────────────────────────────────────────────────
        // MODELO RECORRENTE / AVULSO (parcelas <= 1)
        // ─────────────────────────────────────────────────────────────
        $base = (float) ($in['pagamento_valor'] ?? 0);

        if ($primeira) {
            // 1º pagamento da venda -> comissão inicial
            $vend = round($base * $percIni / 100, 2);
            $gest = $temGestor ? round($base * $percGIni / 100, 2) : 0.0;

            return self::gerar('inicial', $vend, $gest, $percIni, $temGestor ? $percGIni : 0);
        }

        // Pagamentos seguintes -> recorrência
        if ($tipo === 'avulso') {
            // Avulso não tem recorrência (só deveria ter 1 pagamento mesmo).
            return self::naoGerar('avulso_sem_recorrencia');
        }

        // Trava do fim do mês: precisa ter sido pago até o último dia do mês
        // do vencimento. Sem vencimento conhecido, não trava (gera).
        $vencimento = $in['vencimento_data'] ?? null;
        $pagamento  = $in['pagamento_data'] ?? null;
        if ($vencimento && $pagamento) {
            $fimDoMes = Carbon::parse($vencimento)->endOfMonth();
            if (Carbon::parse($pagamento)->gt($fimDoMes)) {
                return self::naoGerar('pago_apos_fim_do_mes');
            }
        }

        $vend = round($base * $percRec / 100, 2);
        $gest = $temGestor ? round($base * $percGRec / 100, 2) : 0.0;

        return self::gerar('recorrencia', $vend, $gest, $percRec, $temGestor ? $percGRec : 0);
    }

    private static function gerar(string $tipo, float $vend, float $gest, float $percV, float $percG): array
    {
        return [
            'gerar'          => true,
            'motivo'         => '',
            'tipo'           => $tipo,
            'valor_vendedor' => $vend,
            'valor_gestor'   => $gest,
            'perc_vendedor'  => $percV,
            'perc_gestor'    => $percG,
        ];
    }

    private static function naoGerar(string $motivo): array
    {
        return [
            'gerar'          => false,
            'motivo'         => $motivo,
            'tipo'           => '',
            'valor_vendedor' => 0.0,
            'valor_gestor'   => 0.0,
            'perc_vendedor'  => 0.0,
            'perc_gestor'    => 0.0,
        ];
    }
}
