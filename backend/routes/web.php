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
use App\Http\Controllers\AsaasWebhookController;
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
// TERMOS (Geral)
// ==========================================
Route::middleware('auth')->group(function () {
    // Google Calendar OAuth Routes
    Route::get('/google/redirect', [\App\Http\Controllers\GoogleCalendarController::class, 'redirect'])->name('google.redirect');
    Route::get('/google/callback', [\App\Http\Controllers\GoogleCalendarController::class, 'callback'])->name('google.callback');
    Route::post('/google/disconnect', [\App\Http\Controllers\GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');

    Route::get('/termos/{termo}/pdf', [TermsController::class, 'exportPdf'])->name('termos.pdf');
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

Route::middleware(['auth', 'verified', '2fa'])->group(function () {

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

});

// ==========================================
// Módulos Extraídos
// ==========================================
require __DIR__ . '/web/checkout.php';
require __DIR__ . '/web/webhooks.php';
require __DIR__ . '/web/admin.php';
require __DIR__ . '/web/vendedor.php';
require __DIR__ . '/web/gestor.php';
require __DIR__ . '/web/chat.php';
