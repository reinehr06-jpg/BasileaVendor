<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testa o comportamento do endpoint /api/asaas/webhook.
 * Cobre: validação de token, idempotência e rejeição de payload inválido.
 *
 * Rodar: php artisan test --filter=AsaasWebhookTest
 */
class AsaasWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookUrl = '/api/asaas/webhook';

    private array $validPayload = [
        'id'      => 'evt_test_abc123',
        'event'   => 'PAYMENT_CONFIRMED',
        'payment' => [
            'id'     => 'pay_test_001',
            'status' => 'CONFIRMED',
            'value'  => 197.00,
        ],
    ];

    /** Webhook sem token retorna 403 quando ASAAS_WEBHOOK_TOKEN está configurado. */
    public function test_rejeita_sem_token_quando_configurado(): void
    {
        config(['services.asaas.webhook_token' => 'token-secreto']);

        $response = $this->postJson($this->webhookUrl, $this->validPayload);

        $response->assertStatus(403);
    }

    /** Webhook com token errado retorna 403. */
    public function test_rejeita_token_invalido(): void
    {
        config(['services.asaas.webhook_token' => 'token-secreto']);

        $response = $this->postJson(
            $this->webhookUrl,
            $this->validPayload,
            ['asaas-access-token' => 'token-ERRADO']
        );

        $response->assertStatus(403);
    }

    /** Webhook com token correto e payload válido retorna 200. */
    public function test_aceita_token_correto(): void
    {
        config(['services.asaas.webhook_token' => 'token-secreto']);

        $response = $this->postJson(
            $this->webhookUrl,
            $this->validPayload,
            ['asaas-access-token' => 'token-secreto']
        );

        $response->assertStatus(200)->assertJson(['ok' => true]);
    }

    /** Sem token configurado, qualquer requisição passa (modo permissivo). */
    public function test_aceita_sem_token_quando_nao_configurado(): void
    {
        config(['services.asaas.webhook_token' => '']);

        $response = $this->postJson($this->webhookUrl, $this->validPayload);

        $response->assertStatus(200);
    }

    /** Payload sem 'id' ou 'event' retorna 400. */
    public function test_rejeita_payload_sem_event_id(): void
    {
        config(['services.asaas.webhook_token' => '']);

        $response = $this->postJson($this->webhookUrl, ['payment' => ['id' => 'pay_001']]);

        $response->assertStatus(400);
    }

    /** Segundo POST com mesmo event ID é ignorado com 200 (idempotência). */
    public function test_idempotencia_duplicata_retorna_200(): void
    {
        config(['services.asaas.webhook_token' => '']);

        // Primeiro envio — deve criar o evento
        $this->postJson($this->webhookUrl, $this->validPayload)->assertStatus(200);

        // Segundo envio com mesmo ID — deve ignorar (não lança exceção, retorna 200)
        $response = $this->postJson($this->webhookUrl, $this->validPayload);

        $response->assertStatus(200);

        // Apenas 1 registro no banco (idempotência garantida)
        $this->assertDatabaseCount('asaas_events', 1);
    }
}
