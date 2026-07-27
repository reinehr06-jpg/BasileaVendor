<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendaApiTest extends TestCase
{
    use RefreshDatabase;

    /** Cria venda com sucesso. */
    public function test_cria_venda_com_sucesso(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendas', [
            'cliente_id' => $cliente->id,
            'plano' => 'Growth',
            'valor_final' => 197,
            'tipo_pagamento' => 'pix',
            'tipo_negociacao' => 'mensal',
        ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'status']]);
    }

    /** Vendedor não pode criar outro vendedor (trava de segurança). */
    public function test_vendedor_nao_pode_criar_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        Vendedor::factory()->create(['usuario_id' => $user->id]);
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendedores', [
            'nome' => 'Test',
            'email' => 'test@test.com',
            'senha' => 'password123',
        ]);
        
        $response->assertStatus(403);
    }

    /** Master pode criar vendedor. */
    public function test_master_pode_criar_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendedores', [
            'nome' => 'Test',
            'email' => 'test@test.com',
            'senha' => 'password123',
            'percentual_comissao' => 10,
        ]);
        
        $response->assertStatus(201);
    }

    /** Lista vendas do vendedor logado. */
    public function test_lista_vendas_do_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        
        // Criar venda para este vendedor
        $venda = \App\Models\Venda::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'valor' => 100,
            'valor_final' => 100,
            'plano' => 'Growth',
            'status' => 'concluida',
            'forma_pagamento' => 'pix',
            'tipo_negociacao' => 'mensal',
            'data_venda' => now(),
        ]);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/vendas');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** Validação de dados inválidos retorna 422. */
    public function test_validacao_dados_invalidos(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        Vendedor::factory()->create(['usuario_id' => $user->id]);
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendas', [
            'cliente_id' => 99999, // Não existe
            'plano' => 'Growth',
            'valor_final' => -100, // Negativo
        ]);
        
        $response->assertStatus(422);
    }
}
