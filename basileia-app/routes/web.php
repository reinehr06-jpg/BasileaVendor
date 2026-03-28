<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterPanelController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\AprovacaoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\VendedorConfiguracaoController;
use App\Http\Controllers\GestorEquipeController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\Master\IntegracaoController;
use App\Http\Controllers\Master\LegacyCustomerController;
use App\Http\Controllers\Master\ConfiguracaoController;
use App\Http\Controllers\Master\SubscriptionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutNewController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\SubscriptionCardController;
use App\Http\Middleware\CheckMaster;
use App\Http\Middleware\CheckVendedor;

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// Checkout Público (sem autenticação)
// ==========================================
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/{hash}', [CheckoutController::class, 'show'])->name('show');
    Route::post('/{hash}/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/{hash}/success', [CheckoutController::class, 'success'])->name('success');
    Route::get('/{hash}/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
});

// ==========================================
// Link de indicação do vendedor
// ==========================================
Route::get('/indicar/{vendedor_hash}', [CheckoutController::class, 'indicacao'])->name('indicacao');
Route::post('/checkout/criar', [CheckoutController::class, 'criarVenda'])->name('checkout.criar');

// ==========================================
// NOVO Checkout SaaS (Alta Conversão)
// ==========================================
Route::prefix('co')->name('checkout.new.')->group(function () {
    Route::get('/{offerSlug}', [CheckoutNewController::class, 'start'])->name('start');
    Route::get('/resume/{token}', [CheckoutNewController::class, 'resume'])->name('resume');
    Route::post('/identify', [CheckoutNewController::class, 'identify'])->name('identify');
    Route::post('/pricing', [CheckoutNewController::class, 'calculatePricing'])->name('pricing');
    Route::post('/validate-coupon', [CheckoutNewController::class, 'validateCoupon'])->name('validate-coupon');
    Route::post('/pay', [CheckoutNewController::class, 'pay'])->name('pay');
    Route::get('/success/{orderNumber}', [CheckoutNewController::class, 'success'])->name('success');
    Route::get('/payment-status/{paymentUuid}', [CheckoutNewController::class, 'paymentStatus'])->name('payment-status');

    // API - Cartões salvos
    Route::get('/api/cards', [SubscriptionCardController::class, 'list'])->name('api.cards');
    Route::delete('/api/cards/{cardId}', [SubscriptionCardController::class, 'delete'])->name('api.cards.delete');
});

// ==========================================
// Webhooks e Manutenção (Deploy AWS)
// ==========================================
Route::post('/webhooks/asaas', [WebhookController::class, 'asaasWebhook'])->name('webhooks.asaas');
Route::get('/webhooks/asaas/status', [WebhookController::class, 'webhookStatus'])->name('webhooks.status');

// Git Auto-Deploy (Garante atualizações em tempo real no servidor AWS)
Route::post('/webhooks/git-deploy', [\App\Http\Controllers\GitWebhookController::class, 'deploy'])->name('webhooks.git-deploy');

// Master Recovery (Recuperação de acesso no servidor AWS - Use apenas uma vez)
Route::get('/master-recovery-fix', [\App\Http\Controllers\MasterFixController::class, 'fix'])->name('master.recovery-fix');

// Database Emergency Reset (Limpeza total + Novo Admin)
Route::get('/emergency-database-reset-2026', [\App\Http\Controllers\DatabaseResetController::class, 'reset'])->name('database.emergency-reset');

// Teste: gerar link de checkout rápido
Route::get('/test-checkout', function() {
    $plano = \App\Models\Plano::first();
    
    if (!$plano) {
        return response('Nenhum plano cadastrado', 400);
    }
    
    $venda = \App\Models\Venda::create([
        'cliente_id' => 1,
        'vendedor_id' => 1,
        'plano_id' => $plano->id,
        'plano' => $plano->nome,
        'valor' => $plano->valor_mensal ?? 97,
        'valor_original' => $plano->valor_mensal ?? 97,
        'valor_final' => $plano->valor_mensal ?? 97,
        'forma_pagamento' => 'pix',
        'status' => 'AGUARDANDO_PAGAMENTO',
        'data_venda' => now()->format('Y-m-d'),
        'checkout_hash' => \Illuminate\Support\Str::random(32),
        'checkout_status' => 'PENDENTE',
        'origem' => 'checkout_proprio',
    ]);
    
    return redirect('/checkout/' . $venda->checkout_hash);
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/Login', function() { return redirect('/login'); });
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rotas de Troca de Senha Obrigatória
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/update', [App\Http\Controllers\Auth\PasswordChangeController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    
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
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelarMaster'])->name('vendas.cancelar');
        Route::post('/vendas/{id}/estornar', [VendaController::class, 'estornarMaster'])->name('vendas.estornar');
        Route::get('/vendas/{venda}/checkout-link', [VendaController::class, 'gerarLinkCheckout'])->name('vendas.checkout-link');
        Route::get('/pagamentos', [PagamentoController::class, 'indexMaster'])->name('pagamentos');
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

        // Clientes Legados Asaas
        Route::get('/legados', [LegacyCustomerController::class, 'index'])->name('legados.index');
        Route::get('/legados/create', [LegacyCustomerController::class, 'create'])->name('legados.create');
        Route::post('/legados', [LegacyCustomerController::class, 'store'])->name('legados.store');
        Route::get('/legados/{legado}', [LegacyCustomerController::class, 'show'])->name('legados.show');
        Route::put('/legados/{legado}', [LegacyCustomerController::class, 'update'])->name('legados.update');
        Route::delete('/legados/{legado}', [LegacyCustomerController::class, 'destroy'])->name('legados.destroy');
        Route::post('/legados/import-single', [LegacyCustomerController::class, 'importSingle'])->name('legados.importSingle');
        Route::post('/legados/import-batch', [LegacyCustomerController::class, 'importBatch'])->name('legados.importBatch');
        Route::post('/legados/{legado}/sync', [LegacyCustomerController::class, 'sync'])->name('legados.sync');
        
        // Comissões Legadas
        Route::get('/legados/comissoes', [LegacyCustomerController::class, 'commissions'])->name('legados.commissions');
        Route::post('/legados/comissoes/generate-recurring', [LegacyCustomerController::class, 'generateRecurring'])->name('legados.generateRecurring');
        Route::post('/legados/comissoes/{commission}/paid', [LegacyCustomerController::class, 'markCommissionPaid'])->name('legados.commission.paid');
        Route::post('/legados/comissoes/mark-multiple-paid', [LegacyCustomerController::class, 'markMultiplePaid'])->name('legados.markMultiplePaid');
        Route::post('/legados/comissoes/{commission}/block', [LegacyCustomerController::class, 'blockCommission'])->name('legados.commission.block');
        
        // Importação CSV
        Route::post('/legados/import-csv', [LegacyCustomerController::class, 'importCsv'])->name('legados.importCsv');
        Route::get('/legados/template', [LegacyCustomerController::class, 'downloadTemplate'])->name('legados.template');

        // Assinaturas e Cartões Salvos
        Route::get('/assinaturas', [SubscriptionController::class, 'index'])->name('assinaturas');
        Route::get('/assinaturas/{id}', [SubscriptionController::class, 'show'])->name('assinaturas.show');
        Route::post('/assinaturas/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('assinaturas.cancel');
        Route::post('/assinaturas/{id}/pause', [SubscriptionController::class, 'pause'])->name('assinaturas.pause');
        Route::post('/assinaturas/{id}/resume', [SubscriptionController::class, 'resume'])->name('assinaturas.resume');
        Route::get('/assinaturas/{id}/card', [SubscriptionController::class, 'viewCard'])->name('assinaturas.card');

        // Aprovações Comerciais
        Route::get('/aprovacoes', [AprovacaoController::class, 'index'])->name('aprovacoes');
        Route::patch('/aprovacoes/{id}/aprovar', [AprovacaoController::class, 'aprovar'])->name('aprovacoes.aprovar');
        Route::patch('/aprovacoes/{id}/rejeitar', [AprovacaoController::class, 'rejeitar'])->name('aprovacoes.rejeitar');
        
        // Notificações
        Route::post('/notificacoes/{id}/marcar-lida', [NotificacaoController::class, 'marcarComoLida'])->name('notificacoes.marcar-lida');
        Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasComoLidas'])->name('notificacoes.marcar-todas-lidas');

        // Configurações Unificadas (Estilo Materio)
        Route::get('/configuracoes/{tab?}', [ConfiguracaoController::class, 'index'])->name('configuracoes');
        Route::post('/configuracoes/geral', [ConfiguracaoController::class, 'updateProfile'])->name('configuracoes.geral.update');
        Route::post('/configuracoes/seguranca', [ConfiguracaoController::class, 'updatePassword'])->name('configuracoes.seguranca.update');
        
        // Mantendo as rotas de POST das integrações para não quebrar os formulários portados
        Route::post('/configuracoes/integracoes', [IntegracaoController::class, 'update'])->name('configuracoes.integracoes.update');
        Route::post('/configuracoes/integracoes/split', [IntegracaoController::class, 'updateSplit'])->name('configuracoes.integracoes.split');
        Route::post('/configuracoes/integracoes/email', [IntegracaoController::class, 'updateEmail'])->name('configuracoes.integracoes.email');
        Route::post('/configuracoes/integracoes/church', [IntegracaoController::class, 'updateChurch'])->name('configuracoes.integracoes.church');
        Route::post('/configuracoes/integracoes/google-calendar', [IntegracaoController::class, 'updateGoogleCalendar'])->name('configuracoes.integracoes.google-calendar');
        Route::post('/configuracoes/integracoes/google-gmail', [IntegracaoController::class, 'updateGoogleGmail'])->name('configuracoes.integracoes.google-gmail');
        Route::post('/configuracoes/integracoes/testar', [IntegracaoController::class, 'testarConexao'])->name('configuracoes.integracoes.testar');
        Route::post('/configuracoes/integracoes/validar-wallet', [IntegracaoController::class, 'validarWallet'])->name('configuracoes.integracoes.validar-wallet');
        
        // Comissões por Plano
        Route::put('/configuracoes/comissoes/{id}', [IntegracaoController::class, 'updateComissaoRule'])->name('configuracoes.comissoes.update');

        // Rotas legadas que serão removidas ou redirecionadas
        Route::get('/configuracoes-gerais', function() { return redirect()->route('master.configuracoes'); });
        Route::get('/configuracoes/integracoes', function() { return redirect()->route('master.configuracoes', ['tab' => 'integracoes']); })->name('configuracoes.integracoes');
        Route::get('/configuracoes/comissoes', function() { return redirect()->route('master.configuracoes', ['tab' => 'comissoes']); })->name('configuracoes.comissoes');
    });

    // ==========================================
    // Módulo Vendedor
    // ==========================================
    Route::middleware([CheckVendedor::class, 'force.password.change'])->prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/vendas', [VendaController::class, 'index'])->name('vendas');
        Route::get('/vendas/canceladas', [VendaController::class, 'canceladas'])->name('vendas.canceladas');
        Route::get('/vendas/nova', [VendaController::class, 'create'])->name('vendas.create');
        Route::get('/vendas/verificar-documento', [VendaController::class, 'verificarDocumento'])->name('vendas.verificar-documento');
        Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');
        Route::get('/vendas/{id}/boleto', [PagamentoBoletoController::class, 'download'])->name('vendas.boleto');
        Route::get('/vendas/{id}/boleto/baixar', [PagamentoBoletoController::class, 'forceDownload'])->name('vendas.boleto.baixar');
        Route::get('/vendas/{id}/cobranca', [VendaController::class, 'cobranca'])->name('vendas.cobranca');
        Route::post('/vendas/{id}/sync', [VendaController::class, 'syncPagamento'])->name('vendas.sync');
        Route::post('/vendas/{id}/estornar', [VendaController::class, 'estornar'])->name('vendas.estornar');
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelar'])->name('vendas.cancelar');
        Route::get('/vendas/{venda}/checkout-link', [VendaController::class, 'gerarLinkCheckout'])->name('vendas.checkout-link');

        Route::get('/pagamentos', [PagamentoController::class, 'indexVendedor'])->name('pagamentos');
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/comissoes', [ComissaoController::class, 'index'])->name('comissoes');
        Route::get('/comissoes/exportar', [ComissaoController::class, 'exportar'])->name('comissoes.exportar');
        Route::get('/comissao', function() { return redirect()->route('vendedor.comissoes'); })->name('comissao');
        
        // Configurações do Vendedor (Comissões e Repasse)
        Route::get('/configuracoes', [VendedorConfiguracaoController::class, 'index'])->name('configuracoes');
        Route::put('/configuracoes/split', [VendedorConfiguracaoController::class, 'updateSplit'])->name('configuracoes.split.update');
        
        // Equipe do Gestor
        Route::get('/equipe', [GestorEquipeController::class, 'index'])->name('equipe');
        Route::post('/equipe/adicionar-membro', [GestorEquipeController::class, 'adicionarMembro'])->name('equipe.adicionar-membro');
        Route::delete('/equipe/remover-membro/{vendedorId}', [GestorEquipeController::class, 'removerMembro'])->name('equipe.remover-membro');
        Route::put('/equipe/atualizar-meta', [GestorEquipeController::class, 'atualizarMeta'])->name('equipe.atualizar-meta');
        Route::get('/equipe/vendedor/{vendedorId}', [GestorEquipeController::class, 'vendedorDetalhes'])->name('equipe.vendedor-detalhes');
    });

});

Route::post('/webhook/saque', function () {
    return response()->json(['authorized' => true]);
});

// Webhooks externos (sem autenticação)
Route::post('/webhook/asaas', [\App\Http\Controllers\BasileiaChurchWebhookController::class, 'webhookAsaas']);
Route::post('/webhook/basileia-church/sync', [\App\Http\Controllers\BasileiaChurchWebhookController::class, 'syncCliente']);
