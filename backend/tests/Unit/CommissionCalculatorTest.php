<?php

namespace Tests\Unit;

use App\Services\Commission\CommissionCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica PURA de comissão (as 5 regras + trava do fim do mês).
 * Não toca no banco — roda com: php artisan test --filter=CommissionCalculatorTest
 */
class CommissionCalculatorTest extends TestCase
{
    private function base(array $over = []): array
    {
        return array_merge([
            'tipo_negociacao'            => 'mensal',
            'parcelas'                   => 1,
            'valor_total'                => 0,
            'pagamento_valor'            => 100,
            'pagamento_data'             => '2026-03-15',
            'vencimento_data'            => '2026-03-10',
            'perc_inicial'               => 10,
            'perc_recorrencia'           => 5,
            'perc_gestor_inicial'        => 0,
            'perc_gestor_recorrencia'    => 0,
            'tem_gestor'                 => false,
            'primeira_comissao_da_venda' => true,
        ], $over);
    }

    /** Regra 1 — comissão inicial na primeira venda. */
    public function test_inicial_mensal(): void
    {
        $r = CommissionCalculator::calcular($this->base(['primeira_comissao_da_venda' => true]));
        $this->assertTrue($r['gerar']);
        $this->assertSame('inicial', $r['tipo']);
        $this->assertEqualsWithDelta(10.0, $r['valor_vendedor'], 0.001);
        $this->assertEqualsWithDelta(0.0, $r['valor_gestor'], 0.001);
    }

    /** Regra 2 — recorrência com % menor, paga dentro do mês. */
    public function test_recorrencia_no_prazo(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => false,
            'vencimento_data'            => '2026-03-10',
            'pagamento_data'             => '2026-03-28',
        ]));
        $this->assertTrue($r['gerar']);
        $this->assertSame('recorrencia', $r['tipo']);
        $this->assertEqualsWithDelta(5.0, $r['valor_vendedor'], 0.001);
    }

    /** Trava — pago depois do fim do mês do vencimento não gera. */
    public function test_trava_fim_do_mes_bloqueia(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => false,
            'vencimento_data'            => '2026-03-10',
            'pagamento_data'             => '2026-04-02',
        ]));
        $this->assertFalse($r['gerar']);
        $this->assertSame('pago_apos_fim_do_mes', $r['motivo']);
    }

    /** Trava — mês de 31 dias, pago no dia 31 ainda conta. */
    public function test_trava_dia_31_gera(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => false,
            'vencimento_data'            => '2026-01-05',
            'pagamento_data'             => '2026-01-31',
        ]));
        $this->assertTrue($r['gerar']);
        $this->assertSame('recorrencia', $r['tipo']);
    }

    /** Trava — fevereiro, pago no dia 28 conta. */
    public function test_trava_fevereiro_gera(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => false,
            'vencimento_data'            => '2026-02-10',
            'pagamento_data'             => '2026-02-28',
        ]));
        $this->assertTrue($r['gerar']);
    }

    /** Regra 3 — gestor recebe % quando o vendedor tem gestor. */
    public function test_gestor_recebe(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => true,
            'tem_gestor'                 => true,
            'perc_gestor_inicial'        => 3,
            'perc_gestor_recorrencia'    => 2,
        ]));
        $this->assertEqualsWithDelta(10.0, $r['valor_vendedor'], 0.001);
        $this->assertEqualsWithDelta(3.0, $r['valor_gestor'], 0.001);
    }

    /** Regra 4 — gestor em venda direta (sem gestor acima) não gera parte de gestor. */
    public function test_gestor_venda_direta_sem_parte_gestor(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'primeira_comissao_da_venda' => true,
            'tem_gestor'                 => false,
            'perc_gestor_inicial'        => 3,
        ]));
        $this->assertEqualsWithDelta(10.0, $r['valor_vendedor'], 0.001);
        $this->assertEqualsWithDelta(0.0, $r['valor_gestor'], 0.001);
    }

    /** Regra 5 — anual 10x100, inicial 10% + recorrência 5% × 9 = R$55 antecipado. */
    public function test_antecipado_valor(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'tipo_negociacao'            => 'anual',
            'parcelas'                   => 10,
            'valor_total'                => 1000,
            'pagamento_valor'            => 100,
            'primeira_comissao_da_venda' => true,
        ]));
        $this->assertTrue($r['gerar']);
        $this->assertSame('inicial_antecipada', $r['tipo']);
        $this->assertEqualsWithDelta(55.0, $r['valor_vendedor'], 0.001);
    }

    /** Regra 5 — antecipado com gestor: 3% + 2% × 9 = R$21. */
    public function test_antecipado_gestor(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'tipo_negociacao'            => 'anual',
            'parcelas'                   => 10,
            'valor_total'                => 1000,
            'pagamento_valor'            => 100,
            'primeira_comissao_da_venda' => true,
            'tem_gestor'                 => true,
            'perc_gestor_inicial'        => 3,
            'perc_gestor_recorrencia'    => 2,
        ]));
        $this->assertEqualsWithDelta(55.0, $r['valor_vendedor'], 0.001);
        $this->assertEqualsWithDelta(21.0, $r['valor_gestor'], 0.001);
    }

    /** Regra 5 — parcelas seguintes do antecipado não geram nada. */
    public function test_antecipado_parcela_subsequente_nao_gera(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'tipo_negociacao'            => 'anual',
            'parcelas'                   => 10,
            'valor_total'                => 1000,
            'primeira_comissao_da_venda' => false,
        ]));
        $this->assertFalse($r['gerar']);
        $this->assertSame('parcela_subsequente_antecipado', $r['motivo']);
    }

    /** Avulso não tem recorrência. */
    public function test_avulso_sem_recorrencia(): void
    {
        $r = CommissionCalculator::calcular($this->base([
            'tipo_negociacao'            => 'avulso',
            'primeira_comissao_da_venda' => false,
        ]));
        $this->assertFalse($r['gerar']);
        $this->assertSame('avulso_sem_recorrencia', $r['motivo']);
    }
}
