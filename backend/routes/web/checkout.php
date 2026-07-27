<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutNewController;
use App\Http\Controllers\ExternalCheckoutController;

// ==========================================
// Checkout Público (sem autenticação)
// ==========================================
// Route::prefix('checkout')->name('checkout.')->group(function () {
//     Route::get('/{hash}', [CheckoutController::class, 'show'])->name('show');
//     Route::post('/{hash}/process', [CheckoutController::class, 'process'])->name('process');
//     Route::get('/{hash}/success', [CheckoutController::class, 'success'])->name('success');
//     Route::get('/{hash}/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
// });

// ==========================================
// Link de indicação do vendedor
// ==========================================
// Route::get('/indicar/{vendedor_hash}', [CheckoutController::class, 'indicacao'])->name('indicacao');
// Route::post('/checkout/criar', [CheckoutController::class, 'criarVenda'])->name('checkout.criar');

// ==========================================
// NOVO Checkout SaaS (Alta Conversão)
// ==========================================
Route::prefix('co')->name('checkout.new.')->group(function () {
    Route::get('/evento/{slug}', [CheckoutNewController::class, 'evento'])->name('evento');
    Route::post('/evento/{slug}/pay', [CheckoutNewController::class, 'eventoPay'])->name('evento.pay');
    Route::get('/{offerSlug}', [CheckoutNewController::class, 'start'])->name('start');
    Route::get('/resume/{token}', [CheckoutNewController::class, 'resume'])->name('resume');
    Route::post('/identify', [CheckoutNewController::class, 'identify'])->name('identify');
    Route::post('/pricing', [CheckoutNewController::class, 'calculatePricing'])->name('pricing');
    Route::post('/validate-coupon', [CheckoutNewController::class, 'validateCoupon'])->name('validate-coupon');
    Route::post('/pay', [CheckoutNewController::class, 'pay'])->name('pay');
    Route::get('/success/{orderNumber}', [CheckoutNewController::class, 'success'])->name('success');
    Route::get('/session-status/{token}', [CheckoutNewController::class, 'sessionStatus'])->name('session-status');
});

// ==========================================
// Checkout Externo (via Vendor)
// ==========================================
Route::prefix('checkout')->name('checkout.external.')->group(function () {
    Route::get('/{uuid}', [ExternalCheckoutController::class, 'byUuid'])->name('byUuid');
    Route::get('/asaas/{asaasPaymentId}', [ExternalCheckoutController::class, 'byAsaas'])->name('byAsaas');
});

// ==========================================
// Contratação Pública (Self-Service)
// ==========================================
Route::get('/contratar', [\App\Http\Controllers\PublicHiringController::class, 'index'])->name('public.hiring');
Route::post('/contratar', [\App\Http\Controllers\PublicHiringController::class, 'store'])->name('public.hiring.store');

