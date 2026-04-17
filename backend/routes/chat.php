<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ChatWebhookController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/contacts', [ChatController::class, 'contacts'])->name('contacts');
        Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations');
        Route::get('/conversations/{id}', [ChatController::class, 'conversation'])->name('conversation');
        Route::post('/conversations/{id}/message', [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/conversations/{id}/resolve', [ChatController::class, 'resolve'])->name('resolve');
        Route::post('/conversations/{id}/transfer', [ChatController::class, 'transfer'])->name('transfer');
        Route::post('/conversations/{id}/read', [ChatController::class, 'markRead'])->name('read');
        Route::get('/stats', [ChatController::class, 'stats'])->name('stats');
    });
});

Route::prefix('webhook/chat')->name('chat.webhook.')->group(function () {
    Route::post('/whatsapp', [ChatWebhookController::class, 'handleWhatsApp'])->name('whatsapp');
    Route::post('/meta', [ChatWebhookController::class, 'handleMeta'])->name('meta');
    Route::post('/google', [ChatWebhookController::class, 'handleGoogle'])->name('google');
    Route::post('/{provider}', [ChatWebhookController::class, 'handleProvider'])->name('provider');
});