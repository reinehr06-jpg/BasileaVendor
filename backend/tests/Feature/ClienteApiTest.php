<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClienteApiTest extends TestCase
{
    use RefreshDatabase;

    /** Cria cliente com sucesso. */
    public function test_cria_cliente_com_sucesso(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/clientes', [
            'nome_igreja' => 'Igreja Teste',
            'documento' => '12345678900',
            'email' => 'teste@test.com',
        ]);
        
        $response->assertStatus(201);
    }

    /** Validação de documento retorna 422. */
    public function test_validacao_documento(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/clientes', [
            'nome_igreja' => 'Igreja Teste',
            'documento' => '123', // Inválido
        ]);
        
        $response->assertStatus(422);
    }

    /** Soft delete funciona. */
    public function test_soft_delete(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        $cliente = Cliente::factory()->create();
        
        Sanctum::actingAs($user);
        
        $response = $this->deleteJson("/api/clientes/{$cliente->id}");
        
        $response->assertStatus(200);
        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }
}
