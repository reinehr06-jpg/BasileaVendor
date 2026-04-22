<?php

use App\Http\Controllers\AprovacaoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\BasileiaChurchWebhookController;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutNewController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\ExternalCheckoutController;
use App\Http\Controllers\GestorEquipeController;
use App\Http\Controllers\GitWebhookController;
use App\Http\Controllers\Integration\CheckoutWebhookController;
use App\Http\Controllers\Master\AsaasClienteSyncController;
use App\Http\Controllers\Master\ConfiguracaoController;
use App\Http\Controllers\Master\IAController;
use App\Http\Controllers\Master\StrictAIController;
use App\Http\Controllers\Master\IntegracaoController;
use App\Http\Controllers\Master\IntegracaoEventoController;
use App\Http\Controllers\Master\IntegracaoVendasController;
use App\Http\Controllers\Master\SubscriptionController;
use App\Http\Controllers\MasterPanelController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\VendedorConfiguracaoController;
use App\Http\Controllers\VendedorSettingsController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\PrimeiraMensagemController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\CheckMaster;
use App\Http\Middleware\CheckVendedor;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Cliente;
use App\Models\Setting;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    // Se for uma requisição de API/json, retorna info
    if (request()->expectsJson()) {
        return response()->json([
            'status' => 'ok',
            'app' => 'BasileiaVendas',
            'version' => '1.0',
            'route' => 'home',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    return redirect()->route('login.generate');
});

// Fallback: se o Asaas enviar POST para a raiz, encaminha para o webhook
Route::post('/', function (Request $request) {
    $payload = $request->all();
    $event = $payload['event'] ?? '';

    if (str_starts_with($event, 'PAYMENT_') || str_starts_with($event, 'ACCESS_TOKEN_') || str_starts_with($event, 'SUBSCRIPTION_') || str_starts_with($event, 'FINANCIAL_') || ! empty($payload['payment'])) {
        Log::info('Webhook Asaas recebido na raiz (/), encaminhando...', [
            'event' => $event,
            'ip' => $request->ip(),
        ]);

        $controller = new WebhookController;

        return $controller->asaasWebhook($request);
    }

    return redirect()->route('login.generate');
});

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
// Webhooks e Manutenção (Deploy AWS)
// ==========================================
Route::match(['get', 'post'], '/webhooks/asaas', [WebhookController::class, 'asaasWebhook'])->name('webhooks.asaas');
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
Route::get('/up', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
})->withoutMiddleware(SecurityHeaders::class);

Route::get('/login/gerar', function () {
    return redirect(\App\Services\LoginTokenService::getLoginUrl());
})->name('login.generate');

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/login/{token}', [LoginController::class, 'showLoginFormWithToken'])->name('login.token');
    Route::get('/Login/{token}', [LoginController::class, 'showLoginFormWithToken']);
    Route::post('/login/{token}', [LoginController::class, 'loginWithToken'])->name('login.post');
    Route::get('/login', function () {
        return redirect()->route('login.generate');
    });
    Route::get('/Login', function () {
        return redirect()->route('login.generate');
    });
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// ONBOARDING (Termos + Split)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/termos', [OnboardingController::class, 'verTermos'])->name('onboarding.termos');
    Route::post('/onboarding/termos', [OnboardingController::class, 'aceitarTermos'])->name('onboarding.termos.aceitar');
    Route::get('/onboarding/split', [OnboardingController::class, 'verSplit'])->name('onboarding.split');
    Route::post('/onboarding/split/ativar', [OnboardingController::class, 'ativarSplit'])->name('onboarding.split.ativar');
    Route::post('/onboarding/split/pular', [OnboardingController::class, 'pularSplit'])->name('onboarding.split.pular');
    Route::post('/onboarding/tour/completo', function () {
        auth()->user()->update(['tour_completo' => true]);
        return response()->json(['ok' => true]);
    })->name('onboarding.tour.completo');
});

// ==========================================
// TERMOS (Admin)
// ==========================================
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/termos', [TermsController::class, 'index'])->name('admin.termos.index');
    Route::post('/termos', [TermsController::class, 'store'])->name('admin.termos.store');
    Route::put('/termos/{termo}', [TermsController::class, 'update'])->name('admin.termos.update');
    Route::delete('/termos/{termo}', [TermsController::class, 'destroy'])->name('admin.termos.destroy');
    Route::get('/termos/{termo}/download', [TermsController::class, 'download'])->name('admin.termos.download');
    Route::post('/termos/{termo}/toggle', [TermsController::class, 'toggleAtivo'])->name('admin.termos.toggle');
});

// ==========================================
// IMPORTAÇÃO
// ==========================================
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/importar', function () {
        return view('admin.importar.index');
    })->name('admin.importar.index');
    Route::post('/importar', [ImportacaoController::class, 'importar'])->name('admin.importar.processar');
});

// ==========================================
// CONTATOS
// ==========================================
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/contatos', [ContatoController::class, 'index'])->name('admin.contatos.index');
    Route::post('/contatos', [ContatoController::class, 'store'])->name('admin.contatos.store');
    Route::get('/contatos/{contato}', [ContatoController::class, 'show'])->name('admin.contatos.show');
    Route::put('/contatos/{contato}', [ContatoController::class, 'update'])->name('admin.contatos.update');
    Route::get('/contatos/{contato}/drawer', [ContatoController::class, 'drawer'])->name('admin.contatos.drawer');
    Route::post('/contatos/{contato}/status', [ContatoController::class, 'mudarStatus'])->name('admin.contatos.status');
    Route::post('/contatos/{contato}/gerar-observacao', [ContatoController::class, 'gerarObservacao'])->name('admin.contatos.gerar-observacao');
    Route::get('/contatos/exportar', [ContatoController::class, 'exportar'])->name('admin.contatos.exportar');
});

// ==========================================
// IA PROMPTS (Admin)
// ==========================================
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/ia/prompts', [App\Http\Controllers\Admin\AiPromptController::class, 'index'])->name('admin.ia.prompts.index');
    Route::get('/ia/prompts/create', [App\Http\Controllers\Admin\AiPromptController::class, 'create'])->name('admin.ia.prompts.create');
    Route::post('/ia/prompts', [App\Http\Controllers\Admin\AiPromptController::class, 'store'])->name('admin.ia.prompts.store');
    Route::get('/ia/prompts/{prompt}/edit', [App\Http\Controllers\Admin\AiPromptController::class, 'edit'])->name('admin.ia.prompts.edit');
    Route::put('/ia/prompts/{prompt}', [App\Http\Controllers\Admin\AiPromptController::class, 'update'])->name('admin.ia.prompts.update');
    Route::delete('/ia/prompts/{prompt}', [App\Http\Controllers\Admin\AiPromptController::class, 'destroy'])->name('admin.ia.prompts.destroy');
    Route::post('/ia/prompts/{prompt}/toggle', [App\Http\Controllers\Admin\AiPromptController::class, 'toggle'])->name('admin.ia.prompts.toggle');
});

// 2FA Routes
Route::middleware('auth')->prefix('2fa')->name('2fa.')->group(function () {
    Route::get('/verify', [TwoFactorController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [TwoFactorController::class, 'verify'])->name('verify.post');
    Route::get('/setup', [TwoFactorController::class, 'showSetup'])->name('setup');
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
});

// Diagnóstico Asaas (protegido por auth + master)
Route::middleware(['auth', 'admin.security'])->get('/debug-asaas', function () {
    try {
        $result = [];

        // 1. Descobrir IP do servidor
        try {
            $ipResponse = Http::timeout(5)->get('https://api.ipify.org?format=json');
            $result['server_ip'] = $ipResponse->successful() ? $ipResponse->json()['ip'] : 'N/A';
        } catch (Exception $e) {
            $result['server_ip'] = 'ERRO: '.$e->getMessage();
        }

        // 2. Verificar settings table
        try {
            $hasSettings = Schema::hasTable('settings');
            $result['settings_table'] = $hasSettings ? 'EXISTS' : 'NOT FOUND';
        } catch (Exception $e) {
            $result['settings_table'] = 'ERROR: '.$e->getMessage();
        }

        // 3. Verificar API key
        try {
            $apiKey = Setting::get('asaas_api_key', '');
            $result['api_key_configured'] = ! empty($apiKey);
            $result['api_key_prefix'] = ! empty($apiKey) ? substr($apiKey, 0, 10).'...' : 'EMPTY';
        } catch (Exception $e) {
            $result['api_key_error'] = $e->getMessage();
        }

        // 4. Verificar ambiente
        try {
            $env = Setting::get('asaas_environment', 'sandbox');
            $result['environment'] = $env;
        } catch (Exception $e) {
            $result['environment_error'] = $e->getMessage();
        }

        // 5. Testar conexão HTTP
        try {
            $asaas = new AsaasService;
            $result['base_url'] = $asaas->baseUrl;
            $response = Http::withHeaders(['access_token' => $asaas->getApiKey()])
                ->timeout(10)
                ->get("{$asaas->baseUrl}/payments?limit=1");
            $result['http_status'] = $response->status();
            $result['http_ok'] = $response->successful();
            if (! $response->successful()) {
                $result['http_body'] = substr($response->body(), 0, 500);
            }
        } catch (Exception $e) {
            $result['http_error'] = $e->getMessage();
        }

        return response()->json($result);
    } catch (Exception $e) {
        return response()->json(['fatal_error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

// API pública de verificação (usada pelo formulário de nova venda)
Route::get('/api/verificar-email', function (Request $request) {
    $email = $request->query('email');
    if (empty($email)) {
        return response()->json(['exists' => false]);
    }
    $existe = Cliente::where('email', $email)->exists();

    return response()->json(['exists' => $existe]);
})->name('api.verificar-email');

Route::get('/api/verificar-whatsapp', function (Request $request) {
    $whatsapp = $request->query('whatsapp');
    if (empty($whatsapp)) {
        return response()->json(['exists' => false]);
    }
    $existe = Cliente::where('whatsapp', $whatsapp)->exists();

    return response()->json(['exists' => $existe]);
})->name('api.verificar-whatsapp');

// Rotas de Troca de Senha Obrigatória
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/update', [PasswordChangeController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', '2fa'])->group(function () {

    // Fallback inteligente: Quem acessar apenas /dashboard será jogado para seu respectivo painel
    Route::get('/dashboard', function () {
        if (Auth::user()->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }

        // Vendedor e Gestor vão para o mesmo dashboard
        return redirect()->route('vendedor.dashboard');
    })->name('dashboard');

    // API interna: buscar planos por quantidade de membros
    Route::get('/api/planos', [VendaController::class, 'buscarPlanos'])->name('api.planos');

    // API: verificar se documento já possui venda ativa
    Route::get('/vendas/verificar-documento', [VendaController::class, 'verificarDocumento'])->name('vendas.verificar-documento');

    // ==========================================
    // Módulo Master
    // ==========================================
    Route::middleware([CheckMaster::class, 'admin.security', 'force.password.change'])->prefix('master')->name('master.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/vendedores', [MasterPanelController::class, 'vendedores'])->name('vendedores');
        Route::post('/vendedores', [MasterPanelController::class, 'storeVendedor'])->name('vendedores.store');
        Route::put('/vendedores/{id}', [MasterPanelController::class, 'updateVendedor'])->name('vendedores.update');
        Route::patch('/vendedores/{id}/toggle', [MasterPanelController::class, 'toggleVendedor'])->name('vendedores.toggle');

        // Equipes
        Route::get('/equipes', [EquipeController::class, 'index'])->name('equipes');
        Route::post('/equipes', [EquipeController::class, 'store'])->name('equipes.store');
        Route::put('/equipes/{id}', [EquipeController::class, 'update'])->name('equipes.update');
        Route::delete('/equipes/{id}', [EquipeController::class, 'destroy'])->name('equipes.destroy');
        Route::post('/equipes/{id}/adicionar-membro', [EquipeController::class, 'adicionarMembro'])->name('equipes.adicionar-membro');
        Route::delete('/equipes/{equipeId}/membros/{vendedorId}', [EquipeController::class, 'removerMembro'])->name('equipes.remover-membro');

        Route::get('/vendas', [VendaController::class, 'indexMaster'])->name('vendas');
        Route::get('/vendas/exportar', [VendaController::class, 'exportar'])->name('vendas.exportar');
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelarMaster'])->name('vendas.cancelar');
        Route::delete('/vendas/{id}/excluir', [VendaController::class, 'excluirVenda'])->name('vendas.excluir');
        Route::post('/vendas/{id}/estornar', [VendaController::class, 'estornarMaster'])->name('vendas.estornar');
        Route::get('/vendas/{venda}/checkout-link', [VendaController::class, 'gerarLinkCheckout'])->name('vendas.checkout-link');
        Route::get('/vendas/corrigir-links', [VendaController::class, 'corrigirLinksCheckout'])->name('vendas.corrigir-links');
        Route::get('/vendas/{id}/boleto', [PagamentoBoletoController::class, 'download'])->name('vendas.boleto');
        Route::get('/vendas/{id}/boleto/baixar', [PagamentoBoletoController::class, 'forceDownload'])->name('vendas.boleto.baixar');
        Route::get('/pagamentos', [PagamentoController::class, 'indexMaster'])->name('pagamentos');
        Route::get('/pagamentos/exportar', [PagamentoController::class, 'exportar'])->name('pagamentos.exportar');
        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios');
        Route::get('/relatorios/exportar', [RelatorioController::class, 'exportar'])->name('relatorios.exportar');

        // Endpoints de API para Relatórios
        Route::prefix('api/relatorios')->name('relatorios.api.')->group(function () {
            Route::get('/resumo', [RelatorioController::class, 'apiResumo'])->name('resumo');
            Route::get('/vendas-por-vendedor', [RelatorioController::class, 'apiVendasPorVendedor'])->name('vendas_vendedor');
            Route::get('/pagamentos', [RelatorioController::class, 'apiPagamentos'])->name('pagamentos');
            Route::get('/churn-renovacoes', [RelatorioController::class, 'apiChurnRenovacoes'])->name('churn_renovacoes');
            Route::get('/formas-pagamento', [RelatorioController::class, 'apiFormasPagamento'])->name('formas_pagamento');
            Route::get('/metas-por-equipe', [RelatorioController::class, 'apiMetasPorEquipe'])->name('metas_equipe');
        });

        Route::get('/metas', [MetaController::class, 'index'])->name('metas');
        Route::post('/metas', [MetaController::class, 'store'])->name('metas.store');
        Route::put('/metas/{id}', [MetaController::class, 'update'])->name('metas.update');
        Route::delete('/metas/{id}', [MetaController::class, 'destroy'])->name('metas.destroy');

        // Endpoints de API para Metas
        Route::prefix('api/metas')->name('metas.api.')->group(function () {
            Route::get('/', [MetaController::class, 'apiListar'])->name('index');
            Route::get('/resumo', [MetaController::class, 'apiResumo'])->name('resumo');
            Route::post('/', [MetaController::class, 'apiStore'])->name('store');
            Route::put('/{id}', [MetaController::class, 'apiUpdate'])->name('update');
        });

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::patch('/clientes/{id}/status', [ClienteController::class, 'updateStatus'])->name('clientes.updateStatus');

        Route::get('/comissoes', [ComissaoController::class, 'indexMaster'])->name('comissoes');
        Route::get('/comissoes/exportar', [ComissaoController::class, 'exportar'])->name('comissoes.exportar');
        Route::get('/comissoes/{vendedorId}/historico', [ComissaoController::class, 'historicoVendedor'])->name('comissoes.historico')->where('vendedorId', '[0-9]+');
        Route::get('/comissoes/{vendedorId}/exportar-historico', [ComissaoController::class, 'exportarHistorico'])->name('comissoes.exportar-historico');
        Route::get('/comissoes/nota-fiscal/{notaId}/download', [ComissaoController::class, 'downloadNotaFiscal'])->name('comissoes.download-nota');
        Route::prefix('api/comissoes')->name('comissoes.api.')->group(function () {
            Route::get('/', [ComissaoController::class, 'apiListar'])->name('index');
            Route::get('/resumo', [ComissaoController::class, 'apiResumo'])->name('resumo');
        });

        // Assinaturas e Cartões Salvos
        Route::get('/assinaturas', [SubscriptionController::class, 'index'])->name('assinaturas');
        Route::get('/assinaturas/{id}', [SubscriptionController::class, 'show'])->name('assinaturas.show');
        Route::post('/assinaturas/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('assinaturas.cancel');
        Route::post('/assinaturas/{id}/pause', [SubscriptionController::class, 'pause'])->name('assinaturas.pause');
        Route::post('/assinaturas/{id}/resume', [SubscriptionController::class, 'resume'])->name('assinaturas.resume');
        Route::get('/assinaturas/{id}/card', [SubscriptionController::class, 'viewCard'])->name('assinaturas.card');

        // Ciclo de Assinatura — Migração e Verificação
        Route::post('/assinaturas/migrar', [SubscriptionController::class, 'migrar'])->name('assinaturas.migrar');
        Route::post('/assinaturas/verificar', [SubscriptionController::class, 'verificar'])->name('assinaturas.verificar');

        // Aprovações Comerciais
        Route::get('/aprovacoes', [AprovacaoController::class, 'index'])->name('aprovacoes');
        Route::patch('/aprovacoes/{id}/aprovar', [AprovacaoController::class, 'aprovar'])->name('aprovacoes.aprovar');
        Route::patch('/aprovacoes/{id}/rejeitar', [AprovacaoController::class, 'rejeitar'])->name('aprovacoes.rejeitar');

        // Notificações
        Route::post('/notificacoes/{id}/marcar-lida', [NotificacaoController::class, 'marcarComoLida'])->name('notificacoes.marcar-lida');
        Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasComoLidas'])->name('notificacoes.marcar-todas-lidas');

        // Configurações Unificadas (Estilo Materio)
        Route::get('/configuracoes/{tab?}', [ConfiguracaoController::class, 'index'])->name('configuracoes');

        // IA - Logs e Métricas
        Route::get('/ia', [\App\Http\Controllers\Master\IAController::class, 'index'])->name('ia');

        // IA Strict Endpoints (com validação de prompt)
        Route::post('/ia/generate-first-message', [StrictAIController::class, 'generateFirstMessage'])->name('ia.generate.first');
        Route::post('/ia/qualify-lead', [StrictAIController::class, 'qualifyLead'])->name('ia.qualify.lead');
        Route::post('/ia/summarize', [StrictAIController::class, 'summarize'])->name('ia.summarize');
        Route::post('/ia/suggest-action', [StrictAIController::class, 'suggestAction'])->name('ia.suggest.action');
        Route::post('/configuracoes/geral', [ConfiguracaoController::class, 'updateProfile'])->name('configuracoes.geral.update');
        Route::post('/configuracoes/seguranca', [ConfiguracaoController::class, 'updatePassword'])->name('configuracoes.seguranca.update');
        Route::post('/configuracoes/seguranca/2fa/toggle', [ConfiguracaoController::class, 'toggleUser2fa'])->name('configuracoes.seguranca.2fa.toggle');
        Route::post('/configuracoes/seguranca/2fa/reset', [ConfiguracaoController::class, 'resetUser2fa'])->name('configuracoes.seguranca.2fa.reset');
        Route::post('/configuracoes/seguranca/2fa/add-device', [ConfiguracaoController::class, 'addUser2faDevice'])->name('configuracoes.seguranca.2fa.add-device');
        Route::post('/configuracoes/seguranca/2fa/enable', [ConfiguracaoController::class, 'enableUser2fa'])->name('configuracoes.seguranca.2fa.enable');
        Route::post('/configuracoes/seguranca/2fa/remove-device', [ConfiguracaoController::class, 'removeUser2faDevice'])->name('configuracoes.seguranca.2fa.remove-device');
        Route::post('/configuracoes/seguranca/settings', [ConfiguracaoController::class, 'updateSecuritySettings'])->name('configuracoes.seguranca.settings.update');
        Route::get('/configuracoes/seguranca/logs', [ConfiguracaoController::class, 'getLoginLogs'])->name('configuracoes.seguranca.logs');

        // Mantendo as rotas de POST das integrações para não quebrar os formulários portados
        Route::post('/configuracoes/integracoes', [IntegracaoController::class, 'update'])->name('configuracoes.integracoes.update');
        Route::post('/configuracoes/integracoes/split', [IntegracaoController::class, 'updateSplit'])->name('configuracoes.integracoes.split');
        Route::post('/configuracoes/integracoes/email', [IntegracaoController::class, 'updateEmail'])->name('configuracoes.integracoes.email');
        Route::post('/configuracoes/integracoes/email/test', [IntegracaoController::class, 'testEmail'])->name('configuracoes.integracoes.email.test');
        Route::post('/configuracoes/integracoes/church', [IntegracaoController::class, 'updateChurch'])->name('configuracoes.integracoes.church');
        Route::post('/configuracoes/integracoes/chat-leads', [IntegracaoController::class, 'updateChatLeads'])->name('configuracoes.integracoes.chat-leads');
        Route::post('/configuracoes/integracoes/google-calendar', [IntegracaoController::class, 'updateGoogleCalendar'])->name('configuracoes.integracoes.google-calendar');
        Route::post('/configuracoes/integracoes/google-gmail', [IntegracaoController::class, 'updateGoogleGmail'])->name('configuracoes.integracoes.google-gmail');
        Route::post('/configuracoes/integracoes/ia', [IntegracaoController::class, 'updateIA'])->name('configuracoes.integracoes.ia');

        // Testes de Integração (AJAX)
        Route::get('/configuracoes/integracoes/test/asaas', [IntegracaoController::class, 'testAsaas'])->name('configuracoes.integracoes.test.asaas');
        Route::get('/configuracoes/integracoes/test/checkout', [IntegracaoController::class, 'testCheckout'])->name('configuracoes.integracoes.test.checkout');
        Route::get('/configuracoes/integracoes/test/church', [IntegracaoController::class, 'testBasileiaChurch'])->name('configuracoes.integracoes.test.church');
        Route::get('/configuracoes/integracoes/test/calendar', [IntegracaoController::class, 'testGoogleCalendar'])->name('configuracoes.integracoes.test.calendar');
        Route::get('/configuracoes/integracoes/test/openai', [IntegracaoController::class, 'testOpenAI'])->name('configuracoes.integracoes.test.openai');
        Route::get('/configuracoes/integracoes/test/ollama', [IntegracaoController::class, 'testOllama'])->name('configuracoes.integracoes.test.ollama');
        Route::post('/configuracoes/integracoes/test/email', [IntegracaoController::class, 'testEmail'])->name('configuracoes.integracoes.test.email');
        Route::get('/configuracoes/integracoes/test/all', [IntegracaoController::class, 'testAll'])->name('configuracoes.integracoes.test.all');
        Route::post('/configuracoes/integracoes/testar', [IntegracaoController::class, 'testarConexao'])->name('configuracoes.integracoes.testar');
        Route::post('/configuracoes/integracoes/test-checkout-api', [IntegracaoController::class, 'testarCheckoutApi'])->name('configuracoes.integracoes.test-checkout-api');
        Route::post('/configuracoes/integracoes/test-webhook', [IntegracaoController::class, 'testarWebhook'])->name('configuracoes.integracoes.test-webhook');
        Route::post('/configuracoes/integracoes/validar-wallet', [IntegracaoController::class, 'validarWallet'])->name('configuracoes.integracoes.validar-wallet');

        // Comissões por Plano
        Route::put('/configuracoes/comissoes/{id}', [IntegracaoController::class, 'updateComissaoRule'])->name('configuracoes.comissoes.update');

        // Rotas legadas que serão removidas ou redirecionadas
        Route::get('/configuracoes-gerais', function () {
            return redirect()->route('master.configuracoes');
        });
        Route::get('/configuracoes/integracoes', function () {
            return redirect()->route('master.configuracoes', ['tab' => 'integracoes']);
        })->name('configuracoes.integracoes');
        Route::get('/configuracoes/comissoes', function () {
            return redirect()->route('master.configuracoes', ['tab' => 'comissoes']);
        })->name('configuracoes.comissoes');

        // ==========================================
        // Integrações
        // ==========================================
        Route::get('/integracoes/basileia-vendas', [IntegracaoVendasController::class, 'index'])->name('integracoes.vendas');

        // Módulo: Clientes Asaas (Importação + Comissões Março/2026)
        Route::get('/clientes-asaas', [AsaasClienteSyncController::class, 'index'])->name('clientes-asaas.index');
        Route::get('/clientes-asaas/auditoria', [AsaasClienteSyncController::class, 'auditoriaRetroativa'])->name('clientes-asaas.auditoria');
        Route::get('/clientes-asaas/{id}', [AsaasClienteSyncController::class, 'show'])->name('clientes-asaas.show');
        Route::get('/clientes-asaas/{id}/editar', [AsaasClienteSyncController::class, 'edit'])->name('clientes-asaas.edit');
        Route::put('/clientes-asaas/{id}', [AsaasClienteSyncController::class, 'update'])->name('clientes-asaas.update');
        Route::post('/clientes-asaas/sincronizar', [AsaasClienteSyncController::class, 'sincronizar'])->name('clientes-asaas.sincronizar');
        Route::patch('/clientes-asaas/{id}/vendedor', [AsaasClienteSyncController::class, 'atribuirVendedor'])->name('clientes-asaas.vendedor');
        Route::post('/clientes-asaas/{id}/confirmar', [AsaasClienteSyncController::class, 'confirmarCliente'])->name('clientes-asaas.confirmar');
        Route::post('/clientes-asaas/bulk-assign', [AsaasClienteSyncController::class, 'bulkAssign'])->name('clientes-asaas.bulk-assign');
        Route::post('/clientes-asaas/preview-assign', [AsaasClienteSyncController::class, 'previewAssign'])->name('clientes-asaas.preview-assign');
        Route::post('/clientes-asaas/calculate-preview', [AsaasClienteSyncController::class, 'calculatePreview'])->name('clientes-asaas.calculate-preview');

        Route::get('/integracoes/eventos', [IntegracaoEventoController::class, 'index'])->name('integracoes.eventos');
        Route::post('/integracoes/eventos', [IntegracaoEventoController::class, 'store'])->name('integracoes.eventos.store');
        Route::patch('/integracoes/eventos/{evento}', [IntegracaoEventoController::class, 'toggle'])->name('integracoes.eventos.toggle');
        Route::delete('/integracoes/eventos/{evento}', [IntegracaoEventoController::class, 'destroy'])->name('integracoes.eventos.destroy');
    });

    // ==========================================
    // Módulo Vendedor
    // ==========================================
    Route::middleware([CheckVendedor::class, 'force.password.change'])->prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/vendas', [VendaController::class, 'index'])->name('vendas');
        Route::get('/vendas/exportar', [VendaController::class, 'exportar'])->name('vendas.exportar');
        Route::get('/vendas/canceladas', [VendaController::class, 'canceladas'])->name('vendas.canceladas');
        Route::get('/vendas/nova', [VendaController::class, 'create'])->name('vendas.create');
        Route::get('/vendas/verificar-documento', [VendaController::class, 'verificarDocumento'])->name('vendas.verificar-documento');
        Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');
        Route::get('/vendas/{id}/boleto', [PagamentoBoletoController::class, 'download'])->name('vendas.boleto');
        Route::get('/vendas/{id}/boleto/baixar', [PagamentoBoletoController::class, 'forceDownload'])->name('vendas.boleto.baixar');
        Route::get('/vendas/{id}/cobranca', [VendaController::class, 'cobranca'])->name('vendas.cobranca');
        Route::post('/vendas/{id}/sync', [VendaController::class, 'syncPagamento'])->name('vendas.sync');
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelar'])->name('vendas.cancelar');
        Route::get('/vendas/{venda}/checkout-link', [VendaController::class, 'gerarLinkCheckout'])->name('vendas.checkout-link');

        Route::get('/pagamentos', [PagamentoController::class, 'indexVendedor'])->name('pagamentos');
        Route::get('/pagamentos/exportar', [PagamentoController::class, 'exportar'])->name('pagamentos.exportar');
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/comissoes', [ComissaoController::class, 'index'])->name('comissoes');
        Route::get('/comissoes/exportar', [ComissaoController::class, 'exportar'])->name('comissoes.exportar');
        Route::get('/comissao', function () {
            return redirect()->route('vendedor.comissoes');
        })->name('comissao');

        // Configurações do Vendedor (Perfil, Segurança, Split)
        Route::get('/configuracoes/{tab?}', [VendedorSettingsController::class, 'index'])->name('configuracoes');
        Route::post('/configuracoes/perfil', [VendedorSettingsController::class, 'updateProfile'])->name('configuracoes.perfil.update');
        Route::post('/configuracoes/senha', [VendedorSettingsController::class, 'updatePassword'])->name('configuracoes.senha.update');
        Route::get('/configuracoes/2fa/setup', [VendedorSettingsController::class, 'setup2fa'])->name('configuracoes.2fa.setup');
        Route::post('/configuracoes/2fa/enable', [VendedorSettingsController::class, 'enable2fa'])->name('configuracoes.2fa.enable');
        Route::post('/configuracoes/2fa/disable', [VendedorSettingsController::class, 'disable2fa'])->name('configuracoes.2fa.disable');
        Route::post('/configuracoes/2fa/rotate', [VendedorSettingsController::class, 'rotate2fa'])->name('configuracoes.2fa.rotate');
        Route::post('/configuracoes/2fa/add-device', [VendedorSettingsController::class, 'add2faDevice'])->name('configuracoes.2fa.add-device');
        Route::get('/configuracoes/2fa/devices', [VendedorSettingsController::class, 'list2faDevices'])->name('configuracoes.2fa.devices');
        Route::put('/configuracoes/split', [VendedorConfiguracaoController::class, 'updateSplit'])->name('configuracoes.split.update');

        // Equipe do Gestor
        Route::get('/equipe', [GestorEquipeController::class, 'index'])->name('equipe');
        Route::post('/equipe/adicionar-membro', [GestorEquipeController::class, 'adicionarMembro'])->name('equipe.adicionar-membro');
        Route::delete('/equipe/remover-membro/{vendedorId}', [GestorEquipeController::class, 'removerMembro'])->name('equipe.remover-membro');
        Route::put('/equipe/atualizar-meta', [GestorEquipeController::class, 'atualizarMeta'])->name('equipe.atualizar-meta');
        Route::get('/equipe/vendedor/{vendedorId}', [GestorEquipeController::class, 'vendedorDetalhes'])->name('equipe.vendedor-detalhes');

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
    });

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

});

// Webhooks externos (sem CSRF, com validacao propria)
Route::post('/webhook/asaas', [BasileiaChurchWebhookController::class, 'webhookAsaas']);

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

// Contatos
    Route::get('/contatos', [ContatoController::class, 'index'])->name('admin.contatos.index');
    Route::post('/contatos/importar', [ImportacaoController::class, 'importar'])->name('admin.contatos.importar');
    Route::post('/contatos', [ContatoController::class, 'store'])->name('admin.contatos.store');
    Route::get('/contatos/{contato}', [ContatoController::class, 'show'])->name('admin.contatos.show');
    Route::put('/contatos/{contato}', [ContatoController::class, 'update'])->name('admin.contatos.update');
    Route::get('/contatos/{contato}/drawer', [ContatoController::class, 'drawer'])->name('admin.contatos.drawer');
    Route::post('/contatos/{contato}/status', [ContatoController::class, 'mudarStatus'])->name('admin.contatos.status');
    Route::post('/contatos/{contato}/gerar-observacao', [ContatoController::class, 'gerarObservacao'])->name('admin.contatos.gerar-observacao');

    // Calendário Admin
    Route::get('/calendario', [CalendarioController::class, 'adminIndex'])->name('admin.calendario.index');

    // Atualização do Sistema
    Route::get('/atualizacao', [App\Http\Controllers\Admin\AtualizacaoController::class, 'index'])->name('admin.atualizacao.instrucoes');
});

// ──────────────────────────────────────────────────────────────────────────────
// GESTOR
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'gestor'])->prefix('gestor')->group(function () {
    // Configurações do Gestor - Rotas específicas PRIMEIRO (antes do catch-all {tab?})
    Route::post('/configuracoes/perfil', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'updateProfile'])->name('gestor.configuracoes.perfil.update');
    Route::post('/configuracoes/senha', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'updatePassword'])->name('gestor.configuracoes.senha.update');
    Route::post('/configuracoes/whatsapp', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'updateWhatsapp'])->name('gestor.configuracoes.whatsapp.update');
    Route::put('/configuracoes/split', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'updateSplit'])->name('gestor.configuracoes.split.update');
    // 2FA Gestor
    Route::post('/configuracoes/2fa/enable', [\App\Http\Controllers\VendedorSettingsController::class, 'enable2fa'])->name('gestor.configuracoes.2fa.enable');
    Route::post('/configuracoes/2fa/disable', [\App\Http\Controllers\VendedorSettingsController::class, 'disable2fa'])->name('gestor.configuracoes.2fa.disable');
    Route::post('/configuracoes/2fa/add-device', [\App\Http\Controllers\VendedorSettingsController::class, 'add2faDevice'])->name('gestor.configuracoes.2fa.add-device');
    // Primeira Mensagem - Aprovação
    Route::get('/configuracoes/aprovar-mensagem', [PrimeiraMensagemController::class, 'pendentes'])->name('gestor.aprovar-mensagem');
    Route::post('/configuracoes/aprovar-mensagem/{mensagem}/aprovar', [PrimeiraMensagemController::class, 'aprovar'])->name('gestor.aprovar-mensagem.aprovar');
    Route::post('/configuracoes/aprovar-mensagem/{mensagem}/rejeitar', [PrimeiraMensagemController::class, 'rejeitar'])->name('gestor.aprovar-mensagem.rejeitar');
    // Catch-all: Configurações index (DEVE ser por último para não capturar as rotas acima)
    Route::get('/configuracoes/{tab?}', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'index'])->name('gestor.configuracoes');

    // Campanhas (visualização)
    Route::get('/campanhas', [CampanhaController::class, 'index'])->name('gestor.campanhas.index');
    Route::get('/campanhas/{campanha}', [CampanhaController::class, 'show'])->name('gestor.campanhas.show');
    
    // Contatos
    Route::get('/contatos', [ContatoController::class, 'index'])->name('gestor.contatos.index');
    Route::get('/contatos/{contato}', [ContatoController::class, 'show'])->name('gestor.contatos.show');
    Route::put('/contatos/{contato}', [ContatoController::class, 'update'])->name('gestor.contatos.update');
    Route::post('/contatos/{contato}/status', [ContatoController::class, 'mudarStatus'])->name('gestor.contatos.status');
    Route::post('/contatos/{contato}/gerar-observacao', [ContatoController::class, 'gerarObservacao'])->name('gestor.contatos.gerar-observacao');
    
    // Calendário
    Route::get('/calendario', [CalendarioController::class, 'gestorIndex'])->name('gestor.calendario.index');
});

// ──────────────────────────────────────────────────────────────────────────────
// VENDEDOR
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'vendedor'])->prefix('vendedor')->group(function () {
    // Contatos (seus leads)
    Route::get('/contatos', [ContatoController::class, 'index'])->name('vendedor.contatos.index');
    Route::get('/contatos/{contato}', [ContatoController::class, 'show'])->name('vendedor.contatos.show');
    Route::put('/contatos/{contato}', [ContatoController::class, 'update'])->name('vendedor.contatos.update');
    Route::post('/contatos/{contato}/status', [ContatoController::class, 'mudarStatus'])->name('vendedor.contatos.status');
    Route::post('/contatos/{contato}/gerar-observacao', [ContatoController::class, 'gerarObservacao'])->name('vendedor.contatos.gerar-observacao');
    
    // Calendário
    Route::get('/calendario', [CalendarioController::class, 'vendedorIndex'])->name('vendedor.calendario.index');
    
    // Primeira Mensagem
    Route::get('/configuracoes/primeira-mensagem', [PrimeiraMensagemController::class, 'index'])->name('configuracoes.primeira-mensagem');
    Route::post('/configuracoes/primeira-mensagem', [PrimeiraMensagemController::class, 'store']);
    Route::post('/configuracoes/primeira-mensagem/{mensagem}/enviar', [PrimeiraMensagemController::class, 'enviarParaAprovacao'])->name('configuracoes.primeira-mensagem.enviar');
    Route::post('/configuracoes/primeira-mensagem/gerar-ia', [PrimeiraMensagemController::class, 'gerarComIA'])->name('configuracoes.primeira-mensagem.ia');
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
