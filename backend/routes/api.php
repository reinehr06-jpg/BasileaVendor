<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\VendaCobrancaController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\Api\ClienteStatusController;
use App\Http\Controllers\Api\CheckoutSessionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ChatWebhookController;
use App\Http\Controllers\Lead\LeadWebhookController;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Api\LimparBancoController;
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

    // Ciclo de Assinatura — Migrar vendas existentes
    Route::post('/subscriptions/migrate', [SubscriptionController::class, 'migrar']);
    Route::post('/subscriptions/migrate/{vendaId}', [SubscriptionController::class, 'migrarVenda']);

    // Ciclo de Assinatura — Verificar inadimplência
    Route::post('/subscriptions/verificar-inadimplencia', [SubscriptionController::class, 'verificarInadimplencia']);

    // Limpar banco de dados (apenas master)
    Route::post('/limpar-banco', [LimparBancoController::class, 'limpar']);
});

// ==========================================
// API Pública: Verificação de Duplicidade (usada no formulário de nova venda)
// Com rate limiting para prevenir enumeration
// ==========================================
Route::middleware('throttle:30,1')->group(function () {
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
            ->first();
        return response()->json([
            'exists' => true,
            'has_active_sale' => $vendaAtiva !== null,
            'cliente' => [
                'id' => $cliente->id,
                'nome_igreja' => $cliente->nome_igreja ?? $cliente->nome ?? '',
            ],
            'venda' => $vendaAtiva ? [
                'id' => $vendaAtiva->id,
                'status' => $vendaAtiva->status,
                'plano' => $vendaAtiva->plano,
            ] : null,
        ]);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==========================================
// API Chat Module
// ==========================================
Route::middleware(['auth:sanctum'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/contacts', [ChatController::class, 'contacts'])->name('contacts');
    Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations');
    Route::get('/conversations/{id}', [ChatController::class, 'conversation'])->name('conversation');
    Route::post('/conversations/{id}/message', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/conversations/{id}/resolve', [ChatController::class, 'resolve'])->name('resolve');
    Route::post('/conversations/{id}/transfer', [ChatController::class, 'transfer'])->name('transfer');
    Route::post('/conversations/{id}/read', [ChatController::class, 'markRead'])->name('read');
    Route::get('/stats', [ChatController::class, 'stats'])->name('stats');
});

// ==========================================
// Chat Webhooks (Públicos)
// ==========================================
Route::prefix('webhook/chat')->name('chat.webhook.')->group(function () {
    Route::post('/whatsapp', [ChatWebhookController::class, 'handleWhatsApp'])->name('whatsapp');
    Route::post('/meta', [ChatWebhookController::class, 'handleMeta'])->name('meta');
    Route::post('/google', [ChatWebhookController::class, 'handleGoogle'])->name('google');
    Route::post('/{provider}', [ChatWebhookController::class, 'handleProvider'])->name('provider');
});

// ==========================================
// API Pública para Serviços Integrados (Site, etc)
// ==========================================
Route::middleware(ApiKeyAuth::class)->prefix('checkout')->group(function () {
    Route::post('/session', [CheckoutSessionController::class, 'create']);
    Route::get('/session/{id}', [CheckoutSessionController::class, 'show']);
});

// ==========================================
// Lead Webhooks (Públicos)
// ==========================================
Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/meta-verify', [LeadWebhookController::class, 'verifyMeta'])->name('meta_verify');
    Route::post('/meta-ads', [LeadWebhookController::class, 'handleMeta'])->name('meta_ads');
    Route::post('/linkedin', [LeadWebhookController::class, 'handleLinkedIn'])->name('linkedin');
    Route::post('/tiktok', [LeadWebhookController::class, 'handleTikTok'])->name('tiktok');
    Route::post('/site', [LeadWebhookController::class, 'handleSite'])->name('site');
    Route::get('/google-ads', [LeadWebhookController::class, 'verifyGoogleAds'])->name('google_verify');
    Route::post('/google-ads', [LeadWebhookController::class, 'handleGoogleAds'])->name('google_ads');
});

// ==========================================
// Lead Management (Autenticado)
// ==========================================
Route::middleware(['auth:sanctum'])->prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadController::class, 'index']);
    Route::get('/kanban', [LeadController::class, 'kanban']);
    Route::get('/dashboard', [LeadController::class, 'dashboard']);
    Route::get('/export', [LeadController::class, 'exportar']);
    Route::get('/agendamentos', [LeadController::class, 'getAgendamentos']);
    Route::get('/quick-replies', [LeadController::class, 'quickReplies']);
    Route::get('/custom-fields', [LeadController::class, 'customFields']);
    
    Route::post('/{id}/etapa', [LeadController::class, 'updateEtapa']);
    Route::post('/{id}/transferir', [LeadController::class, 'transferir']);
    Route::post('/{id}/agendar', [LeadController::class, 'agendar']);
    Route::post('/{id}/custom-fields', [LeadController::class, 'updateLeadCustomFields']);
    Route::get('/{id}/historico', [LeadController::class, 'getTransferHistory']);
    
    Route::patch('/agendamentos/{id}/complete', [LeadController::class, 'completarAgendamento']);
    
    Route::post('/quick-replies', [LeadController::class, 'createQuickReply']);
    Route::delete('/quick-replies/{id}', [LeadController::class, 'deleteQuickReply']);
    
    Route::post('/custom-fields', [LeadController::class, 'createCustomField']);
});

// ==========================================
// IA - Inteligência Artificial (rotas síncronas)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ia/sugestao-resposta', [\App\Http\Controllers\AIServiceController::class, 'sugestaoResposta']);
    Route::post('/ia/proxima-acao', [\App\Http\Controllers\AIServiceController::class, 'proximaAcao']);
    Route::post('/ia/score-lead', [\App\Http\Controllers\AIServiceController::class, 'scoreLead']);
    Route::post('/ia/resumo-conversa', [\App\Http\Controllers\AIServiceController::class, 'resumoConversa']);
    Route::post('/ia/observacao-contato', [\App\Http\Controllers\AIServiceController::class, 'observacaoContato']);
});

// IA - Tarefas pesadas (assíncronas via Job)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ia/analise-vendedor', [\App\Http\Controllers\AIServiceController::class, 'analiseVendedor']);
    Route::post('/ia/analise-campanha', [\App\Http\Controllers\AIServiceController::class, 'analiseCampanha']);
});
