<?php

namespace Tests\Unit;

use App\Models\Pagamento;
use App\Models\Venda;
use App\Services\PagamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoServiceTest extends TestCase
{
    use RefreshDatabase;

    /** getCicloDeComissao retorna início e fim do mês. */
    public function test_get_ciclo_de_comissao(): void
    {
        $data = \Carbon\Carbon::parse('2026-03-15');
        $ciclo = PagamentoService::getCicloDeComissao($data);
        
        $this->assertEquals('2026-03-01', $ciclo['inicio']->format('Y-m-d'));
        $this->assertEquals('2026-03-31', $ciclo['fim']->format('Y-m-d'));
    }

    /** sync retorna false se pagamento não tem asaas_payment_id. */
    public function test_sync_sem_asaas_payment_id(): void
    {
        $pagamento = Pagamento::factory()->create(['asaas_payment_id' => null]);
        
        $service = new PagamentoService();
        $resultado = $service->sync($pagamento);
        
        $this->assertFalse($resultado);
    }
}
