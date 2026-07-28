<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\CalendarioController;

// ==========================================
        // Módulo Chat - Vendedor
        // ==========================================
        Route::get('/chat', [App\Http\Controllers\Chat\ChatController::class, 'index'])->name('chat');
        Route::get('/chat/conversa/{id}', [App\Http\Controllers\Chat\ChatController::class, 'show'])->name('chat.conversa');
        Route::post('/chat/conversa/{id}/mensagem', [App\Http\Controllers\Chat\ChatController::class, 'sendMessage'])->name('chat.mensagem');
        Route::post('/chat/conversa/{id}/status', [App\Http\Controllers\Chat\ChatController::class, 'updateStatus'])->name('chat.status');
        Route::post('/chat/conversa/{id}/fixar', [App\Http\Controllers\Chat\ChatController::class, 'pin'])->name('chat.pin');
        Route::get('/chat/nao-lidos', [App\Http\Controllers\Chat\ChatController::class, 'unreadCount'])->name('chat.nao-lidos');
        Route::get('/chat/buscar', [App\Http\Controllers\Chat\ChatController::class, 'buscar'])->name('chat.buscar');
        
// ==========================================
    // Módulo Chat - Gestor
    // ==========================================
    Route::middleware(['auth', 'gestor'])->prefix('gestor/chat')->name('gestor.chat.')->group(function () {
        Route::get('/', [App\Http\Controllers\Chat\ChatGestorController::class, 'index'])->name('index');
        Route::get('/conversa/{id}', [App\Http\Controllers\Chat\ChatGestorController::class, 'show'])->name('conversa');
        Route::get('/config', [App\Http\Controllers\Chat\ChatGestorController::class, 'config'])->name('config');
        Route::post('/config', [App\Http\Controllers\Chat\ChatGestorController::class, 'updateWhatsappConfig'])->name('config.update');
        Route::get('/distribuicao', [App\Http\Controllers\Chat\ChatGestorController::class, 'distribuicao'])->name('distribuicao');
        Route::post('/distribuicao/reordenar', [App\Http\Controllers\Chat\ChatGestorController::class, 'reorderQueue'])->name('distribuicao.reorder');
        Route::post('/distribuicao/iniciar', [App\Http\Controllers\Chat\ChatGestorController::class, 'initQueue'])->name('distribuicao.init');
        Route::post('/conversa/{id}/atribuir', [App\Http\Controllers\Chat\ChatGestorController::class, 'atribuir'])->name('atribuir');
    });

    
// ==========================================
// Módulo Chat - Admin
// ==========================================
Route::prefix('admin/chat')->name('admin.chat.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::get('/contatos', [App\Http\Controllers\Chat\ChatAdminController::class, 'contatos'])->name('contatos');
        Route::post('/contatos/{id}/tags', [App\Http\Controllers\Chat\ChatAdminController::class, 'atualizarTags'])->name('contatos.tags');
        Route::get('/', [App\Http\Controllers\Chat\ChatAdminController::class, 'chatIndex'])->name('index');
        Route::get('/conversa/{id}', [App\Http\Controllers\Chat\ChatAdminController::class, 'show'])->name('conversa');
        Route::post('/conversa/{id}/mensagem', [App\Http\Controllers\Chat\ChatAdminController::class, 'sendMessage'])->name('mensagem');
        Route::get('/estatisticas', [App\Http\Controllers\Chat\ChatAdminController::class, 'estatisticas'])->name('estatisticas');
        Route::get('/exportar-contatos', [App\Http\Controllers\Chat\ChatAdminController::class, 'exportarContatos'])->name('exportar');
    });
});


// ==========================================
// Chat Webhooks (públicos)
// ==========================================
Route::prefix('webhooks/chat')->name('webhooks.chat.')->group(function () {
    Route::post('/google-ads', [App\Http\Controllers\Chat\ChatWebhookController::class, 'googleAds'])->name('google');
    Route::post('/meta-leads', [App\Http\Controllers\Chat\ChatWebhookController::class, 'metaLeads'])->name('meta');
    Route::post('/whatsapp', [App\Http\Controllers\Chat\ChatWebhookController::class, 'whatsapp'])->name('whatsapp');
    Route::get('/test/{webhookId}', [App\Http\Controllers\Chat\ChatWebhookController::class, 'test'])->name('test');
});

// API endpoint para landpage/formulário próprio
Route::post('/api/chat/lead', [App\Http\Controllers\Chat\ChatWebhookController::class, 'leadInterno'])->name('api.chat.lead');

// Feature flag do Chat
Route::middleware(['auth', 'admin'])->prefix('admin/chat/config')->name('admin.chat.config.')->group(function () {
    Route::get('/status', [App\Http\Controllers\Chat\ChatFeatureController::class, 'status'])->name('status');
    Route::post('/toggle', [App\Http\Controllers\Chat\ChatFeatureController::class, 'toggle'])->name('toggle');
});



// ──────────────────────────────────────────────────────────────────────────────
// ADMIN / MASTER - CAMPANHAS E CONTATOS
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    // Campanhas
    Route::get('/campanhas',                  [CampanhaController::class, 'index'])->name('admin.campanhas.index');
    Route::post('/campanhas',                 [CampanhaController::class, 'store'])->name('admin.campanhas.store');
    Route::get('/campanhas/{campanha}',       [CampanhaController::class, 'show'])->name('admin.campanhas.show');
    Route::put('/campanhas/{campanha}',       [CampanhaController::class, 'update'])->name('admin.campanhas.update');
    Route::get('/campanhas/{campanha}/metricas', [CampanhaController::class, 'metricas'])->name('admin.campanhas.metricas');

    // Calendário Admin
    Route::get('/calendario', [CalendarioController::class, 'adminIndex'])->name('admin.calendario.index');

    // Atualização do Sistema
    Route::get('/atualizacao', [App\Http\Controllers\Admin\AtualizacaoController::class, 'index'])->name('admin.atualizacao.instrucoes');
});

// ──────────────────────────────────────────────────────────────────────────────
// CALENDÁRIO (Compartilhado - criação e ações)
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::post('/calendario',                    [CalendarioController::class, 'store'])->name('calendario.store');
    Route::post('/calendario/{evento}/concluir',  [CalendarioController::class, 'concluir'])->name('calendario.concluir');
    Route::post('/calendario/{evento}/faltou',    [CalendarioController::class, 'marcarFaltou'])->name('calendario.faltou');
    Route::post('/calendario/sincronizar',        [CalendarioController::class, 'sincronizar'])->name('calendario.sincronizar');
    Route::get('/calendario/{evento}/ics',        [CalendarioController::class, 'downloadIcs'])->name('calendario.ics');
});
