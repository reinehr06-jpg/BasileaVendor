<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\VendedorSettingsController;
use App\Http\Controllers\VendedorConfiguracaoController;
use App\Http\Controllers\GestorEquipeController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\PrimeiraMensagemController;

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
        Route::get('/configuracoes/termos/{termo}/pdf', [\App\Http\Controllers\TermsController::class, 'exportPdf'])->name('configuracoes.termos.pdf');

        // Equipe do Gestor
        Route::get('/equipe', [GestorEquipeController::class, 'index'])->name('equipe');
        Route::post('/equipe/adicionar-membro', [GestorEquipeController::class, 'adicionarMembro'])->name('equipe.adicionar-membro');
        Route::delete('/equipe/remover-membro/{vendedorId}', [GestorEquipeController::class, 'removerMembro'])->name('equipe.remover-membro');
        Route::put('/equipe/atualizar-meta', [GestorEquipeController::class, 'atualizarMeta'])->name('equipe.atualizar-meta');
        Route::get('/equipe/vendedor/{vendedorId}', [GestorEquipeController::class, 'vendedorDetalhes'])->name('equipe.vendedor-detalhes');

        // Contatos do Vendedor
        Route::get('/contatos', [ContatoController::class, 'index'])->name('contatos.index');
        Route::get('/contatos/{contato}', [ContatoController::class, 'show'])->name('contatos.show');
        Route::put('/contatos/{contato}', [ContatoController::class, 'update'])->name('contatos.update');
        Route::post('/contatos/{contato}/status', [ContatoController::class, 'mudarStatus'])->name('contatos.status');
        Route::post('/contatos/{contato}/gerar-observacao', [ContatoController::class, 'gerarObservacao'])->name('contatos.gerar-observacao');

        // Calendário do Vendedor
        Route::get('/calendario', [CalendarioController::class, 'vendedorIndex'])->name('calendario.index');

        // Primeira Mensagem
        Route::get('/primeira-mensagem', [PrimeiraMensagemController::class, 'index'])->name('primeira-mensagem');
        Route::post('/primeira-mensagem', [PrimeiraMensagemController::class, 'store'])->name('primeira-mensagem.store');
        Route::post('/primeira-mensagem/{mensagem}/enviar', [PrimeiraMensagemController::class, 'enviarParaAprovacao'])->name('primeira-mensagem.enviar');
        Route::post('/primeira-mensagem/gerar-ia', [PrimeiraMensagemController::class, 'gerarComIA'])->name('primeira-mensagem.ia');
    });

    