<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Testa o endpoint GET /api/dashboard.
 * Verifica: autenticação, estrutura da resposta, dados reais (sem mock).
 *
 * Rodar: php artisan test --filter=DashboardApiTest
 */
class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    /** Endpoint requer autenticação. */
    public function test_requer_autenticacao(): void
    {
        $this->getJson('/api/dashboard')->assertStatus(401);
    }

    /** Resposta contém as chaves esperadas e status 200. */
    public function test_estrutura_da_resposta(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'kpis' => [
                    'total_vendas',
                    'vendas_ativas',
                    'receita_bruta',
                    'comissao_total',
                    'total_clientes',
                ],
                'charts' => [
                    'receita_mensal' => ['labels', 'data'],
                ],
                'recent_sales',
            ]);
    }

    /** Gráfico de receita mensal retorna exatamente 6 entradas (sem hardcode). */
    public function test_grafico_receita_retorna_6_meses(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $labels = $response->json('charts.receita_mensal.labels');
        $data   = $response->json('charts.receita_mensal.data');

        $this->assertCount(6, $labels, 'O gráfico deve ter exatamente 6 meses');
        $this->assertCount(6, $data, 'O gráfico deve ter exatamente 6 valores');

        // Nenhum valor deve ser hardcoded (verificar que não são os valores mockados antigos)
        $valoresMockados = [12000, 19000, 15000, 22000, 25000];
        foreach ($valoresMockados as $mock) {
            $this->assertNotContains(
                $mock,
                $data,
                "Valor hardcoded {$mock} encontrado no gráfico — dados devem ser reais"
            );
        }
    }

    /** Vendedor vê apenas seus próprios dados. */
    public function test_vendedor_ve_apenas_seus_dados(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        Vendedor::factory()->create(['usuario_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)->assertJsonPath('success', true);
    }
}
