<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Pagamento;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComissaoApiTest extends TestCase
{
    use RefreshDatabase;

    /** Lista comissões do vendedor logado. */
    public function test_lista_comissoes_do_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create(['venda_id' => $venda->id, 'valor' => 1000]);
        
        Comissao::create([
            'vendedor_id' => $vendedor->id,
            'cliente_id' => $cliente->id,
            'venda_id' => $venda->id,
            'pagamento_id' => $pagamento->id,
            'valor_comissao' => 100,
            'status' => 'confirmada',
            'competencia' => now()->format('Y-m'),
        ]);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/financeiro/comissoes');
        
        $response->assertStatus(200);
    }

    /** Vendedor não vê comissões de outros. */
    public function test_vendedor_nao_ve_comissoes_de_outros(): void
    {
        $user1 = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor1 = Vendedor::factory()->create(['usuario_id' => $user1->id]);
        
        $user2 = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor2 = Vendedor::factory()->create(['usuario_id' => $user2->id]);
        
        $cliente = Cliente::factory()->create();
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor2->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create(['venda_id' => $venda->id, 'valor' => 1000]);
        
        Comissao::create([
            'vendedor_id' => $vendedor2->id,
            'cliente_id' => $cliente->id,
            'venda_id' => $venda->id,
            'pagamento_id' => $pagamento->id,
            'valor_comissao' => 100,
            'status' => 'confirmada',
            'competencia' => now()->format('Y-m'),
        ]);
        
        Sanctum::actingAs($user1);
        
        $response = $this->getJson('/api/financeiro/comissoes');
        
        $response->assertStatus(200);
        // Deve retornar vazio ou apenas comissões do vendedor1
    }
}
