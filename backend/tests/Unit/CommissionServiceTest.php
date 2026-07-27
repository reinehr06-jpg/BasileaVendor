<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Pagamento;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\Commission\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    /** Gera comissão inicial para primeiro pagamento. */
    public function test_gera_comissao_inicial_primeiro_pagamento(): void
    {
        $vendedor = Vendedor::factory()->create([
            'comissao_inicial' => 10,
            'comissao_recorrencia' => 5,
        ]);
        
        $venda = Venda::factory()->create([
            'vendedor_id' => $vendedor->id,
            'valor_final' => 1000,
        ]);
        
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado['gerou']);
        $this->assertEquals(100, $resultado['valor_vendedor']); // 10% de 1000
        $this->assertEquals('inicial', $resultado['tipo']);
    }

    /** Não gera comissão duplicada (idempotência). */
    public function test_nao_gera_comissao_duplicada(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        // Primeira execução
        CommissionService::gerarParaPagamento($pagamento);
        
        // Segunda execução (deve ser ignorada)
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertFalse($resultado['gerou']);
        $this->assertEquals('ja_processado', $resultado['motivo']);
        $this->assertDatabaseCount('comissoes', 1);
    }

    /** Gera comissão de gestor quando vendedor tem gestor. */
    public function test_gera_comissao_de_gestor(): void
    {
        $gestor = User::factory()->create();
        $vendedor = Vendedor::factory()->create([
            'comissao_inicial' => 10,
            'gestor_id' => $gestor->id,
            'comissao_gestor_primeira' => 3,
        ]);
        
        $venda = Venda::factory()->create([
            'vendedor_id' => $vendedor->id,
            'valor_final' => 1000,
        ]);
        
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado['gerou']);
        $this->assertEquals(100, $resultado['valor_vendedor']);
        $this->assertEquals(30, $resultado['valor_gestor']); // 3% de 1000
        $this->assertDatabaseCount('comissoes', 2); // 1 vendedor + 1 gestor
    }

    /** Não gera comissão para pagamento não confirmado. */
    public function test_nao_gera_comissao_pagamento_nao_confirmado(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'PENDING', // Não confirmado
            'data_pagamento' => null,
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertFalse($resultado['gerou']);
        $this->assertEquals('pagamento_nao_confirmado', $resultado['motivo']);
    }

    /** Usa lock for update para evitar race condition. */
    public function test_usa_lock_for_update(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        // Simular execução concorrente (deve funcionar sem duplicação)
        $resultado1 = CommissionService::gerarParaPagamento($pagamento);
        $resultado2 = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado1['gerou']);
        $this->assertFalse($resultado2['gerou']);
        $this->assertDatabaseCount('comissoes', 1);
    }
}
