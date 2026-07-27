<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterPanelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\Master\SubscriptionController;
use App\Http\Controllers\AprovacaoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\Master\ConfiguracaoController;
use App\Http\Controllers\Master\IAController;
use App\Http\Controllers\Master\StrictAIController;
use App\Http\Controllers\Master\IntegracaoController;
use App\Http\Controllers\Master\AsaasClienteSyncController;
use App\Http\Controllers\Master\IntegracaoEventoController;
use App\Http\Controllers\Master\IntegracaoVendasController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CampanhaController;

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
        Route::post('/configuracoes/integracoes/commercial', [IntegracaoController::class, 'updateCommercial'])->name('configuracoes.integracoes.commercial');

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

    