<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\GitWebhookController;
use App\Http\Controllers\Integration\CheckoutWebhookController;
use App\Http\Controllers\BasileiaChurchWebhookController;

// ==========================================
// Webhooks e Manutenção (Deploy AWS)
// ==========================================
// Rota canônica do webhook Asaas: POST /api/asaas/webhook (AsaasWebhookController)
// A rota legacy /webhooks/asaas foi removida para evitar processamento duplicado.

Route::get('/webhooks/asaas/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Webhook Asaas está funcionando!',
        'route' => '/webhooks/asaas',
        'method' => 'POST',
        'timestamp' => now()->toDateTimeString(),
    ]);
})->name('webhooks.asaas.test');
Route::get('/webhooks/asaas/status', [WebhookController::class, 'webhookStatus'])->name('webhooks.status');

// Git Auto-Deploy (protegido por HMAC signature no controller)
Route::post('/webhooks/git-deploy', [GitWebhookController::class, 'deploy'])->name('webhooks.git-deploy');

// Health check público (sem middleware para funcionar sempre)

// Webhooks externos (sem CSRF, com validacao propria)
// Rota canônica do webhook Asaas está em routes/api.php: POST /api/asaas/webhook

// Rota de migrations removida por segurança

Route::post('/webhook/basileia-church/sync', [BasileiaChurchWebhookController::class, 'syncCliente']);
// Checkout - Webhook que recebe eventos do Checkout (servico externo)
Route::post('/webhook/checkout', [CheckoutWebhookController::class, 'handle'])->name('webhook.checkout');

// ──────────────────────────────────────────────────────────────────────────────
// LEAD CAPTURE WEBHOOKS (Sem CSRF, sem auth)
// ──────────────────────────────────────────────────────────────────────────────
Route::prefix('webhook')->group(function () {
    // Meta Ads Lead Ads
    Route::get('/meta',       [WebhookController::class, 'metaVerify']);  // Verificação Facebook
    Route::post('/meta',      [WebhookController::class, 'metaLead']);    // Leads Meta

    // Google Ads
    Route::post('/google',    [WebhookController::class, 'googleLead']);

    // WhatsApp Links
    Route::post('/whatsapp',  [WebhookController::class, 'whatsappLead']);

    // Formulários Web
    Route::post('/form',      [WebhookController::class, 'formLead']);
});

