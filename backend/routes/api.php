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

// Rotas protegidas por API Key
Route::middleware('api.key')->group(function () {
    // Endpoint de Criação de Cobrança (Wizard)
    Route::post('/vendas/criar-cobranca', [VendaCobrancaController::class, 'createBilling']);

    // Excluir Cobrança/Pagamento (ambas as rotas)
    Route::delete('/cobrancas/{id}', [CobrancaController::class, 'destroy']);
    Route::delete('/pagamentos/{id}', [CobrancaController::class, 'destroyPagamento']);

    // Basiléia Church — Verificar status do cliente (ativado via Bearer token)
    Route::get('/client-status/{venda_id}', [ClienteStatusController::class, 'show']);

    // Checkout Session
    Route::post('/checkout/session', [\App\Http\Controllers\Api\CheckoutSessionController::class, 'create']);
});

// ==========================================
// API Pública: Verificação de Duplicidade (usada no formulário de nova venda)
// ==========================================
Route::get('/verificar-email', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    if (empty($email)) {
        return response()->json(['exists' => false]);
    }
    $existe = \App\Models\Cliente::where('email', $email)->exists();
    return response()->json(['exists' => $existe]);
});

Route::get('/verificar-whatsapp', function (\Illuminate\Http\Request $request) {
    $whatsapp = $request->query('whatsapp');
    if (empty($whatsapp)) {
        return response()->json(['exists' => false]);
    }
    $existe = \App\Models\Cliente::where('whatsapp', $whatsapp)->exists();
    return response()->json(['exists' => $existe]);
});

Route::get('/verificar-documento', function (\Illuminate\Http\Request $request) {
    $documento = preg_replace('/\D/', '', $request->query('documento', ''));
    if (strlen($documento) < 11) {
        return response()->json(['exists' => false]);
    }
    $cliente = \App\Models\Cliente::where('documento', $documento)->first();
    if (!$cliente) {
        return response()->json(['exists' => false]);
    }
    $vendaAtiva = \App\Models\Venda::where('cliente_id', $cliente->id)
        ->whereNotIn('status', ['Cancelado', 'Expirado'])
        ->exists();
    return response()->json([
        'exists' => true,
        'has_active_sale' => $vendaAtiva,
    ]);
});

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
