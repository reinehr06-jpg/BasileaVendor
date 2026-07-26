<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Testa o comportamento do endpoint POST /api/limpar-banco.
 * Garante que está bloqueado em produção e protegido por perfil em dev.
 *
 * Rodar: php artisan test --filter=LimparBancoTest
 */
class LimparBancoTest extends TestCase
{
    use RefreshDatabase;

    /** Em produção, o endpoint retorna 404 independente de quem chama. */
    public function test_retorna_404_em_producao(): void
    {
        // Simula ambiente de produção
        app()->instance('env', 'production');
        $this->app->detectEnvironment(fn () => 'production');

        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);

        // Acessa via rota de api.key (api_key protegida) — qualquer api key válida
        $this->postJson('/api/limpar-banco', [], ['X-API-Key' => 'qualquer'])->assertStatus(404);
    }

    /** Usuário sem perfil master é rejeitado (403) em ambiente não-produtivo. */
    public function test_rejeita_usuario_sem_perfil_master(): void
    {
        app()->instance('env', 'testing');

        $user = User::factory()->create(['perfil' => 'vendedor']);
        Sanctum::actingAs($user);

        $this->postJson('/api/limpar-banco', [], ['X-API-Key' => 'qualquer'])->assertStatus(403);
    }
}
