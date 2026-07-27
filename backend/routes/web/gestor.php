<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrimeiraMensagemController;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\CalendarioController;

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
    Route::get('/configuracoes/termos', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'termos'])->name('gestor.configuracoes.termos');
    Route::get('/configuracoes/termos/pdf/{termo}', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'downloadPdf'])->name('gestor.configuracoes.termos.pdf');
    Route::get('/configuracoes/termos/html/{termo}', [\App\Http\Controllers\Gestor\GestorSettingsController::class, 'downloadHtml'])->name('gestor.configuracoes.termos.html');
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



