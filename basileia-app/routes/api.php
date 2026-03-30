<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\VendaCobrancaController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\Api\ClienteStatusController;
use App\Http\Controllers\Api\CheckoutSessionController;
use App\Http\Middleware\ApiKeyAuth;

// ==========================================
// Rotas Públicas Integracoes
// ==========================================

// Asaas Webhook — Receber eventos de pagamento
Route::post('/asaas/webhook', [AsaasWebhookController::class, 'handle']);

// Endpoint de Criação de Cobrança (Wizard)
Route::post('/vendas/criar-cobranca', [VendaCobrancaController::class, 'createBilling']);

// Excluir Cobrança/Pagamento (ambas as rotas)
Route::delete('/cobrancas/{id}', [CobrancaController::class, 'destroy']);
Route::delete('/pagamentos/{id}', [CobrancaController::class, 'destroyPagamento']);

// Basiléia Church — Verificar status do cliente (ativado via Bearer token)
Route::get('/client-status/{venda_id}', [ClienteStatusController::class, 'show']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// API Pública para Serviços Integrados (Site, etc)
// ==========================================
Route::middleware(ApiKeyAuth::class)->prefix('checkout')->group(function () {
    Route::post('/session', [CheckoutSessionController::class, 'create']);
    Route::get('/session/{id}', [CheckoutSessionController::class, 'show']);
});
