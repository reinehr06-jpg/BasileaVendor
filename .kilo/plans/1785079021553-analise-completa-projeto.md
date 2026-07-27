# Plano de Implementação — Fase 3: Qualidade de Código

## Objetivo
Remover código morto, dividir services grandes e organizar estrutura de rotas para facilitar manutenção.

## Status da Fase 2
✅ **COMPLETA** — Todas as 4 tarefas implementadas e validadas.

---

## Tarefa 3.1 — Remover Código Morto do Backend

### Problema
- `gerarComissoesLegado` em `PagamentoService.php` (linha 289) é um método privado marcado como `@deprecated`
- Não é chamado em nenhum lugar (apenas a definição existe)
- `CommissionEngineService` já foi removido anteriormente

### Ação
1. **Remover métodos legados de `PagamentoService.php`:**
   - `gerarComissoesLegado()` (linhas 285-349)
   - `gerarComissaoFixa()` (linhas 351-483)
   - `gerarComissaoPercentual()` (linhas 485-564)
   - `isComissaoCartaoIntegral()` (se existir)

2. **Remover import não utilizado:**
   - `use App\Models\CommissionRule;` (linha 8) — apenas usado nos métodos legados

### Validação
```bash
# Verificar que não há chamadas
grep -r "gerarComissoesLegado" backend/
grep -r "gerarComissaoFixa" backend/
grep -r "gerarComissaoPercentual" backend/

# Rodar testes
php artisan test

# Processar pagamento de teste → deve gerar comissão normalmente
```

---

## Tarefa 3.2 — Dividir AsaasService em Services Especializados

### Problema
- `AsaasService.php` tem 823 linhas (viola princípio da responsabilidade única)
- Dificulta testes e manutenção
- 29 lugares usam `new AsaasService` — precisa manter compatibilidade

### Ação

#### 1. Criar estrutura de services especializados

**Criar diretório:** `backend/app/Services/Asaas/`

**Criar services:**

**a) `Asaas/CustomerService.php`** (~75 linhas)
```php
<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        // Mover de AsaasService (linhas 54-73)
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        // Mover de AsaasService (linhas 75-126)
    }
}
```

**b) `Asaas/PaymentService.php`** (~250 linhas)
```php
<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createPayment(...): array
    {
        // Mover de AsaasService (linhas 192-248)
    }

    public function getPayment(string $paymentId): ?array
    {
        // Mover de AsaasService (linhas 253-276)
    }

    public function getPixQrCode(string $paymentId): ?array
    {
        // Mover de AsaasService (linhas 281-295)
    }

    public function getIdentificationField(string $paymentId): ?string
    {
        // Mover de AsaasService (linhas 300-314)
    }

    public function getInvoice(string $paymentId): ?array
    {
        // Mover de AsaasService (linhas 319-333)
    }

    public function refundPayment(string $paymentId, ?float $value = null): array
    {
        // Mover de AsaasService (linhas 338-371)
    }

    public function cancelPayment(string $paymentId): bool
    {
        // Mover de AsaasService (linhas 376-396)
    }

    public function deletePayment(string $paymentId): bool
    {
        // Mover de AsaasService (linhas 401-432)
    }

    public function createPaymentLink(array $data): array
    {
        // Mover de AsaasService (linhas 485-525)
    }

    public function deletePaymentLink(string $id): bool
    {
        // Mover de AsaasService (linhas 530-551)
    }

    public function getPaymentsByCustomer(...): array
    {
        // Mover de AsaasService (linhas 717-778)
    }
}
```

**c) `Asaas/SubscriptionService.php`** (~100 linhas)
```php
<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createSubscription(array $data): array
    {
        // Mover de AsaasService (linhas 628-662)
    }

    public function updateSubscription(string $subscriptionId, array $data): array
    {
        // Mover de AsaasService (linhas 667-677)
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        // Mover de AsaasService (linhas 682-701)
    }

    public function getSubscriptionsByCustomer(string $customerId, bool $includeDeleted = false): array
    {
        // Mover de AsaasService (linhas 791-822)
    }
}
```

**d) `Asaas/SplitService.php`** (~70 linhas)
```php
<?php

namespace App\Services\Asaas;

use App\Models\Vendedor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SplitService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function validateWallet(string $walletId): array
    {
        // Mover de AsaasService (linhas 556-584)
    }

    public function buildSplitArray(Vendedor $vendedor, float $valorVenda, string $tipoVenda = 'inicial'): array
    {
        // Mover de AsaasService (linhas 589-623)
    }
}
```

#### 2. Manter `AsaasService.php` como facade (compatibilidade)

```php
<?php

namespace App\Services;

use App\Models\Vendedor;
use App\Services\Asaas\CustomerService;
use App\Services\Asaas\PaymentService;
use App\Services\Asaas\SubscriptionService;
use App\Services\Asaas\SplitService;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    public string $baseUrl;
    protected string $apiKey;
    
    protected CustomerService $customerService;
    protected PaymentService $paymentService;
    protected SubscriptionService $subscriptionService;
    protected SplitService $splitService;

    public function __construct()
    {
        // Manter lógica de inicialização existente (linhas 14-35)
        $ambiente = \App\Models\Setting::get('asaas_environment', config('services.asaas.ambiente', env('ASAAS_ENVIRONMENT', 'sandbox')));
        
        $this->baseUrl = $ambiente === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://api-sandbox.asaas.com/v3';

        $this->apiKey = \App\Models\Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));

        if (empty($this->apiKey)) {
            Log::warning('AsaasService: API Key não configurada.');
        }

        // Inicializar services especializados
        $this->customerService = new CustomerService($this->baseUrl, $this->apiKey);
        $this->paymentService = new PaymentService($this->baseUrl, $this->apiKey);
        $this->subscriptionService = new SubscriptionService($this->baseUrl, $this->apiKey);
        $this->splitService = new SplitService($this->baseUrl, $this->apiKey);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'User-Agent'   => env('ASAAS_USER_AGENT', 'BasileaVendor/1.0'),
        ];
    }

    // Delegar métodos para services especializados (mantém compatibilidade)
    
    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        return $this->customerService->findCustomerByCpfCnpj($cpfCnpj);
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        return $this->customerService->createCustomer($name, $cpfCnpj, $phone, $email);
    }

    public function criarCobranca(string $customerAsaasId, array $dadosVenda, ?array $creditCard = null): array
    {
        // Manter lógica de mapeamento (linhas 132-187)
        // Chamar $this->paymentService->createPayment() internamente
    }

    public function createPayment(...): array
    {
        return $this->paymentService->createPayment(...);
    }

    public function getPayment(string $paymentId): ?array
    {
        return $this->paymentService->getPayment($paymentId);
    }

    // ... delegar todos os outros métodos ...

    public static function mapStatus(string $asaasStatus): string
    {
        // Manter método estático (linhas 437-448)
    }

    public function requestAsaas(string $method, string $endpoint, array $payload = []): array
    {
        // Manter método genérico (linhas 453-480)
    }
}
```

### Validação
```bash
# Verificar que todos os 29 lugares ainda funcionam
grep -r "new AsaasService" backend/

# Rodar testes
php artisan test

# Testar criação de cobrança
# Testar consulta de pagamento
# Testar cancelamento
```

---

## Tarefa 3.3 — Dividir web.php em Arquivos Menores

### Problema
- `routes/web.php` tem 748 linhas (dificulta navegação e manutenção)
- Rotas de diferentes módulos misturadas

### Ação

#### 1. Criar diretório e arquivos de rotas

```bash
mkdir -p backend/routes/web
```

**Criar arquivos:**

**a) `routes/web/checkout.php`** (~30 linhas)
```php
<?php

use App\Http\Controllers\CheckoutNewController;
use App\Http\Controllers\ExternalCheckoutController;
use App\Http\Controllers\PublicHiringController;

// Checkout Público (sem autenticação)
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

// Checkout Externo (via Vendor)
Route::prefix('checkout')->name('checkout.external.')->group(function () {
    Route::get('/{uuid}', [ExternalCheckoutController::class, 'byUuid'])->name('byUuid');
    Route::get('/asaas/{asaasPaymentId}', [ExternalCheckoutController::class, 'byAsaas'])->name('byAsaas');
});

// Contratação Pública (Self-Service)
Route::get('/contratar', [PublicHiringController::class, 'index'])->name('public.hiring');
Route::post('/contratar', [PublicHiringController::class, 'store'])->name('public.hiring.store');
```

**b) `routes/web/webhooks.php`** (~20 linhas)
```php
<?php

use App\Http\Controllers\GitWebhookController;
use App\Http\Controllers\WebhookController;

// Webhooks e Manutenção (Deploy AWS)
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

// Git Auto-Deploy
Route::post('/webhooks/git-deploy', [GitWebhookController::class, 'deploy'])->name('webhooks.git-deploy');
```

**c) `routes/web/admin.php`** (~200 linhas)
```php
<?php

use App\Http\Controllers\MasterPanelController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PagamentoBoletoController;
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
use App\Http\Controllers\Master\IntegracaoEventoController;
use App\Http\Controllers\Master\IntegracaoVendasController;
use App\Http\Controllers\Master\AsaasClienteSyncController;
use App\Http\Middleware\CheckMaster;

// Módulo Master
Route::middleware([CheckMaster::class, 'admin.security', 'force.password.change'])->prefix('master')->name('master.')->group(function () {
    // Copiar todas as rotas do módulo Master (linhas 355-542)
});
```

**d) `routes/web/vendedor.php`** (~100 linhas)
```php
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\VendedorSettingsController;
use App\Http\Controllers\VendedorConfiguracaoController;
use App\Http\Controllers\GestorEquipeController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\PrimeiraMensagemController;
use App\Http\Middleware\CheckVendedor;

// Módulo Vendedor
Route::middleware([CheckVendedor::class, 'force.password.change'])->prefix('vendedor')->name('vendedor.')->group(function () {
    // Copiar todas as rotas do módulo Vendedor (linhas 547-618)
});
```

**e) `routes/web/gestor.php`** (~50 linhas)
```php
<?php

use App\Http\Controllers\Gestor\GestorSettingsController;
use App\Http\Controllers\VendedorSettingsController;
use App\Http\Controllers\PrimeiraMensagemController;

// Módulo Gestor
Route::middleware(['auth', 'gestor'])->prefix('gestor')->group(function () {
    // Copiar todas as rotas do módulo Gestor (linhas 720-735)
});
```

**f) `routes/web/chat.php`** (~40 linhas)
```php
<?php

use App\Http\Controllers\Chat\ChatGestorController;
use App\Http\Controllers\Chat\ChatAdminController;
use App\Http\Controllers\Chat\ChatWebhookController;
use App\Http\Controllers\Chat\ChatFeatureController;

// Módulo Chat - Gestor
Route::middleware(['auth', 'gestor'])->prefix('gestor/chat')->name('gestor.chat.')->group(function () {
    // Copiar rotas do chat do gestor (linhas 623-632)
});

// Módulo Chat - Admin
Route::prefix('admin/chat')->name('admin.chat.')->group(function () {
    Route::middleware(['auth'])->group(function () {
        // Copiar rotas do chat admin (linhas 637-647)
    });
});

// Chat Webhooks (públicos)
Route::prefix('webhooks/chat')->name('webhooks.chat.')->group(function () {
    // Copiar rotas de webhook do chat (linhas 652-657)
});

// Feature flag do Chat
Route::middleware(['auth', 'admin'])->prefix('admin/chat/config')->name('admin.chat.config.')->group(function () {
    // Copiar rotas de config do chat (linhas 663-666)
});
```

#### 2. Atualizar `routes/web.php` para incluir os arquivos

```php
<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ImportacaoController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\BasileiaChurchWebhookController;
use App\Http\Controllers\Integration\CheckoutWebhookController;
use App\Http\Middleware\SecurityHeaders;
use App\Models\Cliente;
use App\Models\Setting;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// Rota raiz
Route::get('/', function () {
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

// Incluir arquivos de rotas organizados
require base_path('routes/web/checkout.php');
require base_path('routes/web/webhooks.php');

// Rotas de autenticação e onboarding (manter aqui por simplicidade)
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

// ONBOARDING
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

// TERMOS (Admin)
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/termos', [TermsController::class, 'index'])->name('admin.termos.index');
    Route::post('/termos', [TermsController::class, 'store'])->name('admin.termos.store');
    Route::put('/termos/{termo}', [TermsController::class, 'update'])->name('admin.termos.update');
    Route::delete('/termos/{termo}', [TermsController::class, 'destroy'])->name('admin.termos.destroy');
    Route::get('/termos/{termo}/download', [TermsController::class, 'download'])->name('admin.termos.download');
    Route::post('/termos/{termo}/toggle', [TermsController::class, 'toggleAtivo'])->name('admin.termos.toggle');
});

// Google Calendar OAuth
Route::middleware('auth')->group(function () {
    Route::get('/google/redirect', [GoogleCalendarController::class, 'redirect'])->name('google.redirect');
    Route::get('/google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');
    Route::post('/google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');
    Route::get('/termos/{termo}/pdf', [TermsController::class, 'exportPdf'])->name('termos.pdf');
});

// IMPORTAÇÃO
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/importar', function () {
        return view('admin.importar.index');
    })->name('admin.importar.index');
    Route::post('/importar', [ImportacaoController::class, 'importar'])->name('admin.importar.processar');
});

// CONTATOS
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

// 2FA Routes
Route::middleware('auth')->prefix('2fa')->name('2fa.')->group(function () {
    Route::get('/verify', [TwoFactorController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [TwoFactorController::class, 'verify'])->name('verify.post');
    Route::get('/setup', [TwoFactorController::class, 'showSetup'])->name('setup');
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
});

// Diagnóstico Asaas
Route::middleware(['auth', 'admin.security'])->get('/debug-asaas', function () {
    // Manter lógica de diagnóstico (linhas 248-305)
});

// API pública de verificação
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

// Rotas de Troca de Senha
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/update', [PasswordChangeController::class, 'update'])->name('password.update');
});

// Rotas autenticadas
Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    // Fallback inteligente para dashboard
    Route::get('/dashboard', function () {
        if (Auth::user()->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }
        return redirect()->route('vendedor.dashboard');
    })->name('dashboard');

    // API interna
    Route::get('/api/planos', [VendaController::class, 'buscarPlanos'])->name('api.planos');
    Route::get('/vendas/verificar-documento', [VendaController::class, 'verificarDocumento'])->name('vendas.verificar-documento');

    // Incluir módulos organizados
    require base_path('routes/web/admin.php');
    require base_path('routes/web/vendedor.php');
    require base_path('routes/web/gestor.php');
    require base_path('routes/web/chat.php');
});

// Webhooks externos (sem CSRF)
require base_path('routes/web/webhooks.php');

// Webhook Asaas (web)
Route::match(['get', 'post'], '/webhook/asaas', [AsaasWebhookController::class, 'handle'])->name('webhook.asaas.web');
Route::match(['get', 'post'], '/webhook/assas', [AsaasWebhookController::class, 'handle'])->name('webhook.assas.web');

// Basileia Church webhook
Route::post('/webhook/basileia-church/sync', [BasileiaChurchWebhookController::class, 'syncCliente']);

// Checkout webhook
Route::post('/webhook/checkout', [CheckoutWebhookController::class, 'handle'])->name('webhook.checkout');

// Lead capture webhooks
Route::prefix('webhook')->group(function () {
    Route::get('/meta', [WebhookController::class, 'metaVerify']);
    Route::post('/meta', [WebhookController::class, 'metaLead']);
    Route::post('/google', [WebhookController::class, 'googleLead']);
    Route::post('/whatsapp', [WebhookController::class, 'whatsappLead']);
    Route::post('/form', [WebhookController::class, 'formLead']);
});

// Admin - Campanhas e Contatos
Route::middleware(['auth', 'master'])->prefix('admin')->group(function () {
    Route::get('/campanhas', [CampanhaController::class, 'index'])->name('admin.campanhas.index');
    Route::post('/campanhas', [CampanhaController::class, 'store'])->name('admin.campanhas.store');
    Route::get('/campanhas/{campanha}', [CampanhaController::class, 'show'])->name('admin.campanhas.show');
    Route::put('/campanhas/{campanha}', [CampanhaController::class, 'update'])->name('admin.campanhas.update');
    Route::get('/campanhas/{campanha}/metricas', [CampanhaController::class, 'metricas'])->name('admin.campanhas.metricas');
    Route::get('/calendario', [CalendarioController::class, 'adminIndex'])->name('admin.calendario.index');
    Route::get('/atualizacao', [App\Http\Controllers\Admin\AtualizacaoController::class, 'index'])->name('admin.atualizacao.instrucoes');
});
```

### Validação
```bash
# Verificar que todas as rotas ainda funcionam
php artisan route:list

# Testar login
# Testar dashboard master
# Testar dashboard vendedor
# Testar dashboard gestor
# Testar webhooks
```

---

## Tarefa 3.4 — Remover Código Morto do Frontend

### Problema
- Páginas em `frontend/src/app/(menu)/contabilidade/` não são acessadas pelo menu (navigation.ts)
- Páginas em `frontend/src/app/(menu)/analises-e-contabil/` não são acessadas pelo menu
- Páginas em `frontend/src/app/(menu)/configuracoes/modelos/` não são acessadas pelo menu
- Links internos existem, mas não há entrada pelo menu principal

### Ação

#### 1. Verificar se há links para essas rotas no menu

```bash
# Verificar navigation.ts
grep -r "contabilidade" frontend/src/data/navigation.ts
grep -r "analises-e-contabil" frontend/src/data/navigation.ts
grep -r "modelos" frontend/src/data/navigation.ts
```

**Resultado esperado:** Nenhum link encontrado (confirmando que são código morto)

#### 2. Deletar rotas mortas

```bash
rm -rf frontend/src/app/\(menu\)/contabilidade/
rm -rf frontend/src/app/\(menu\)/analises-e-contabil/
rm -rf frontend/src/app/\(menu\)/configuracoes/modelos/
```

#### 3. Verificar se há links para rotas deletadas

```bash
# Buscar links para rotas deletadas
grep -r "/contabilidade" frontend/src/ --include="*.tsx" --include="*.ts"
grep -r "/analises-e-contabil" frontend/src/ --include="*.tsx" --include="*.ts"
grep -r "/configuracoes/modelos" frontend/src/ --include="*.tsx" --include="*.ts"
```

**Se encontrar links:**
- Remover ou redirecionar para rotas válidas
- Verificar se há componentes reutilizáveis que precisam ser mantidos

#### 4. Remover comentários de mock

```bash
# Buscar comentários de mock
grep -r "// MOCK" frontend/src/ --include="*.tsx" --include="*.ts"
grep -r "Gráfico Fake" frontend/src/ --include="*.tsx" --include="*.ts"
grep -r "mockOfx" frontend/src/ --include="*.tsx" --include="*.ts"
```

**Se encontrar:**
- Remover comentários e código mock relacionado
- Verificar se há dados reais disponíveis

### Validação
```bash
# Build do Next.js
cd frontend
npm run build

# Verificar que não há erros de compilação
# Navegar pelo app → não deve haver links quebrados
# Verificar que todas as rotas do menu funcionam
```

---

## Checklist da Fase 3

- [ ] **Tarefa 3.1** — Remover métodos legados de `PagamentoService.php`
  - [ ] Remover `gerarComissoesLegado()`
  - [ ] Remover `gerarComissaoFixa()`
  - [ ] Remover `gerarComissaoPercentual()`
  - [ ] Remover import de `CommissionRule`
  - [ ] Rodar testes

- [ ] **Tarefa 3.2** — Dividir `AsaasService` em services especializados
  - [ ] Criar `Asaas/CustomerService.php`
  - [ ] Criar `Asaas/PaymentService.php`
  - [ ] Criar `Asaas/SubscriptionService.php`
  - [ ] Criar `Asaas/SplitService.php`
  - [ ] Manter `AsaasService.php` como facade
  - [ ] Testar criação de cobrança
  - [ ] Testar consulta de pagamento
  - [ ] Testar cancelamento

- [ ] **Tarefa 3.3** — Dividir `web.php` em arquivos menores
  - [ ] Criar `routes/web/checkout.php`
  - [ ] Criar `routes/web/webhooks.php`
  - [ ] Criar `routes/web/admin.php`
  - [ ] Criar `routes/web/vendedor.php`
  - [ ] Criar `routes/web/gestor.php`
  - [ ] Criar `routes/web/chat.php`
  - [ ] Atualizar `routes/web.php` para incluir os arquivos
  - [ ] Testar todas as rotas

- [ ] **Tarefa 3.4** — Remover código morto do frontend
  - [ ] Deletar `frontend/src/app/(menu)/contabilidade/`
  - [ ] Deletar `frontend/src/app/(menu)/analises-e-contabil/` (se existir)
  - [ ] Deletar `frontend/src/app/(menu)/configuracoes/modelos/`
  - [ ] Verificar e remover links para rotas deletadas
  - [ ] Remover comentários de mock
  - [ ] Rodar build do Next.js

---

## Como validar a Fase 3 completa

1. **Backend:**
   ```bash
   php artisan test
   php artisan route:list
   ```

2. **Frontend:**
   ```bash
   cd frontend
   npm run build
   npm run lint
   ```

3. **Funcional:**
   - Login como master → dashboard funciona
   - Login como vendedor → dashboard funciona
   - Login como gestor → dashboard funciona
   - Criar venda → funciona
   - Processar pagamento → comissão gerada
   - Webhook do Asaas → processa normalmente

4. **Código:**
   - `grep -r "gerarComissoesLegado" backend/` → zero resultados
   - `grep -r "CommissionEngineService" backend/` → zero resultados
   - `grep -r "/contabilidade" frontend/src/data/navigation.ts` → zero resultados
   - `grep -r "user.role" frontend/src/` → zero resultados

---

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Dividir `AsaasService` quebra integrações | Manter facade com delegação, testar cada método |
| Dividir `web.php` quebra rotas | Testar todas as rotas após divisão, manter `route:list` atualizado |
| Remover código morto quebra funcionalidade | Verificar se há links/referências antes de deletar |
| Métodos legados ainda são usados | Grep para confirmar zero chamadas antes de remover |

---

# FASE 4: Testes e CI/CD 🟢

## Objetivo
Adicionar testes automatizados e pipeline de CI/CD para garantir qualidade e prevenir regressões.

## Status da Fase 3
⚠️ **PARCIALMENTE COMPLETA** — Tarefas 3.1, 3.2 e 3.3 completas. Tarefa 3.4 pendente.

### Pendências da Fase 3
- [ ] Deletar `frontend/src/app/(menu)/contabilidade/` (11 arquivos)
- [ ] Deletar `frontend/src/app/(menu)/configuracoes/modelos/` (1 arquivo)
- [ ] Remover links internos entre páginas de contabilidade (14 matches)

---

## Tarefa 4.1 — Testes Unitários para Services Críticos

### Problema
- Apenas `CommissionCalculatorTest.php` existe (12 testes)
- Services críticos sem testes: `CommissionService`, `AsaasService`, `PagamentoService`
- Cobertura de testes muito baixa (< 10%)

### Ação

#### 1. Criar `CommissionServiceTest.php`

**Arquivo:** `backend/tests/Unit/CommissionServiceTest.php`

**Testes a implementar:**
```php
<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Pagamento;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\Commission\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    /** Gera comissão inicial para primeiro pagamento. */
    public function test_gera_comissao_inicial_primeiro_pagamento(): void
    {
        $vendedor = Vendedor::factory()->create([
            'comissao_inicial' => 10,
            'comissao_recorrencia' => 5,
        ]);
        
        $venda = Venda::factory()->create([
            'vendedor_id' => $vendedor->id,
            'valor_final' => 1000,
        ]);
        
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado['gerou']);
        $this->assertEquals(100, $resultado['valor_vendedor']); // 10% de 1000
        $this->assertEquals('inicial', $resultado['tipo']);
    }

    /** Não gera comissão duplicada (idempotência). */
    public function test_nao_gera_comissao_duplicada(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        // Primeira execução
        CommissionService::gerarParaPagamento($pagamento);
        
        // Segunda execução (deve ser ignorada)
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertFalse($resultado['gerou']);
        $this->assertEquals('ja_processado', $resultado['motivo']);
        $this->assertDatabaseCount('comissoes', 1);
    }

    /** Gera comissão de gestor quando vendedor tem gestor. */
    public function test_gera_comissao_de_gestor(): void
    {
        $gestor = User::factory()->create();
        $vendedor = Vendedor::factory()->create([
            'comissao_inicial' => 10,
            'gestor_id' => $gestor->id,
            'comissao_gestor_primeira' => 3,
        ]);
        
        $venda = Venda::factory()->create([
            'vendedor_id' => $vendedor->id,
            'valor_final' => 1000,
        ]);
        
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado['gerou']);
        $this->assertEquals(100, $resultado['valor_vendedor']);
        $this->assertEquals(30, $resultado['valor_gestor']); // 3% de 1000
        $this->assertDatabaseCount('comissoes', 2); // 1 vendedor + 1 gestor
    }

    /** Não gera comissão para pagamento não confirmado. */
    public function test_nao_gera_comissao_pagamento_nao_confirmado(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'PENDING', // Não confirmado
            'data_pagamento' => null,
        ]);
        
        $resultado = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertFalse($resultado['gerou']);
        $this->assertEquals('pagamento_nao_confirmado', $resultado['motivo']);
    }

    /** Usa lock for update para evitar race condition. */
    public function test_usa_lock_for_update(): void
    {
        $vendedor = Vendedor::factory()->create(['comissao_inicial' => 10]);
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create([
            'venda_id' => $venda->id,
            'valor' => 1000,
            'status' => 'RECEIVED',
            'data_pagamento' => now(),
        ]);
        
        // Simular execução concorrente (deve funcionar sem duplicação)
        $resultado1 = CommissionService::gerarParaPagamento($pagamento);
        $resultado2 = CommissionService::gerarParaPagamento($pagamento);
        
        $this->assertTrue($resultado1['gerou']);
        $this->assertFalse($resultado2['gerou']);
        $this->assertDatabaseCount('comissoes', 1);
    }
}
```

#### 2. Criar `AsaasServiceTest.php`

**Arquivo:** `backend/tests/Unit/AsaasServiceTest.php`

**Testes a implementar:**
```php
<?php

namespace Tests\Unit;

use App\Services\AsaasService;
use App\Services\Asaas\CustomerService;
use App\Services\Asaas\PaymentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasServiceTest extends TestCase
{
    /** Service delega para CustomerService. */
    public function test_delega_para_customer_service(): void
    {
        Http::fake([
            'api-sandbox.asaas.com/v3/customers*' => Http::response([
                'data' => [['id' => 'cus_123', 'name' => 'Test']],
            ], 200),
        ]);
        
        $service = new AsaasService();
        $resultado = $service->findCustomerByCpfCnpj('12345678900');
        
        $this->assertNotNull($resultado);
        $this->assertEquals('cus_123', $resultado['id']);
    }

    /** Service delega para PaymentService. */
    public function test_delega_para_payment_service(): void
    {
        Http::fake([
            'api-sandbox.asaas.com/v3/payments' => Http::response([
                'id' => 'pay_123',
                'status' => 'PENDING',
            ], 200),
        ]);
        
        $service = new AsaasService();
        $resultado = $service->createPayment(
            'cus_123',
            100.00,
            '2026-12-31',
            'PIX',
            'Test'
        );
        
        $this->assertEquals('pay_123', $resultado['id']);
    }

    /** mapStatus converte status corretamente. */
    public function test_map_status(): void
    {
        $this->assertEquals('Pago', AsaasService::mapStatus('RECEIVED'));
        $this->assertEquals('Pago', AsaasService::mapStatus('CONFIRMED'));
        $this->assertEquals('Pendente', AsaasService::mapStatus('PENDING'));
        $this->assertEquals('Cancelado', AsaasService::mapStatus('CANCELED'));
    }
}
```

#### 3. Criar `PagamentoServiceTest.php`

**Arquivo:** `backend/tests/Unit/PagamentoServiceTest.php`

**Testes a implementar:**
```php
<?php

namespace Tests\Unit;

use App\Models\Pagamento;
use App\Models\Venda;
use App\Services\PagamentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoServiceTest extends TestCase
{
    use RefreshDatabase;

    /** getCicloDeComissao retorna início e fim do mês. */
    public function test_get_ciclo_de_comissao(): void
    {
        $data = \Carbon\Carbon::parse('2026-03-15');
        $ciclo = PagamentoService::getCicloDeComissao($data);
        
        $this->assertEquals('2026-03-01', $ciclo['inicio']->format('Y-m-d'));
        $this->assertEquals('2026-03-31', $ciclo['fim']->format('Y-m-d'));
    }

    /** sync retorna false se pagamento não tem asaas_payment_id. */
    public function test_sync_sem_asaas_payment_id(): void
    {
        $pagamento = Pagamento::factory()->create(['asaas_payment_id' => null]);
        
        $service = new PagamentoService();
        $resultado = $service->sync($pagamento);
        
        $this->assertFalse($resultado);
    }
}
```

### Validação
```bash
php artisan test --filter=CommissionServiceTest
php artisan test --filter=AsaasServiceTest
php artisan test --filter=PagamentoServiceTest
php artisan test
```

---

## Tarefa 4.2 — Testes de Integração para Rotas Críticas

### Problema
- Apenas `DashboardApiTest.php` e `AsaasWebhookTest.php` existem
- Rotas críticas sem testes: Vendas, Clientes, Vendedores, Comissões

### Ação

#### 1. Criar `VendaApiTest.php`

**Arquivo:** `backend/tests/Feature/VendaApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendaApiTest extends TestCase
{
    use RefreshDatabase;

    /** Cria venda com sucesso. */
    public function test_cria_venda_com_sucesso(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendas', [
            'cliente_id' => $cliente->id,
            'plano' => 'Growth',
            'valor_final' => 197,
            'tipo_pagamento' => 'pix',
            'tipo_negociacao' => 'mensal',
        ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'status']]);
    }

    /** Vendedor não pode criar outro vendedor (trava de segurança). */
    public function test_vendedor_nao_pode_criar_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        Vendedor::factory()->create(['usuario_id' => $user->id]);
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendedores', [
            'nome' => 'Test',
            'email' => 'test@test.com',
            'senha' => 'password123',
        ]);
        
        $response->assertStatus(403);
    }

    /** Master pode criar vendedor. */
    public function test_master_pode_criar_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendedores', [
            'nome' => 'Test',
            'email' => 'test@test.com',
            'senha' => 'password123',
            'percentual_comissao' => 10,
        ]);
        
        $response->assertStatus(201);
    }

    /** Lista vendas do vendedor logado. */
    public function test_lista_vendas_do_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        
        // Criar venda para este vendedor
        $venda = \App\Models\Venda::create([
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'valor' => 100,
            'valor_final' => 100,
            'plano' => 'Growth',
            'status' => 'concluida',
            'forma_pagamento' => 'pix',
            'tipo_negociacao' => 'mensal',
            'data_venda' => now(),
        ]);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/vendas');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** Validação de dados inválidos retorna 422. */
    public function test_validacao_dados_invalidos(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        Vendedor::factory()->create(['usuario_id' => $user->id]);
        
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/vendas', [
            'cliente_id' => 99999, // Não existe
            'plano' => 'Growth',
            'valor_final' => -100, // Negativo
        ]);
        
        $response->assertStatus(422);
    }
}
```

#### 2. Criar `ClienteApiTest.php`

**Arquivo:** `backend/tests/Feature/ClienteApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClienteApiTest extends TestCase
{
    use RefreshDatabase;

    /** Cria cliente com sucesso. */
    public function test_cria_cliente_com_sucesso(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/clientes', [
            'nome_igreja' => 'Igreja Teste',
            'documento' => '12345678900',
            'email' => 'teste@test.com',
        ]);
        
        $response->assertStatus(201);
    }

    /** Validação de documento retorna 422. */
    public function test_validacao_documento(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/clientes', [
            'nome_igreja' => 'Igreja Teste',
            'documento' => '123', // Inválido
        ]);
        
        $response->assertStatus(422);
    }

    /** Soft delete funciona. */
    public function test_soft_delete(): void
    {
        $user = User::factory()->create(['perfil' => 'master']);
        $cliente = Cliente::factory()->create();
        
        Sanctum::actingAs($user);
        
        $response = $this->deleteJson("/api/clientes/{$cliente->id}");
        
        $response->assertStatus(200);
        $this->assertSoftDeleted('clientes', ['id' => $cliente->id]);
    }
}
```

#### 3. Criar `ComissaoApiTest.php`

**Arquivo:** `backend/tests/Feature/ComissaoApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comissao;
use App\Models\Pagamento;
use App\Models\User;
use App\Models\Venda;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComissaoApiTest extends TestCase
{
    use RefreshDatabase;

    /** Lista comissões do vendedor logado. */
    public function test_lista_comissoes_do_vendedor(): void
    {
        $user = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor = Vendedor::factory()->create(['usuario_id' => $user->id]);
        $cliente = Cliente::factory()->create();
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create(['venda_id' => $venda->id, 'valor' => 1000]);
        
        Comissao::create([
            'vendedor_id' => $vendedor->id,
            'cliente_id' => $cliente->id,
            'venda_id' => $venda->id,
            'pagamento_id' => $pagamento->id,
            'valor_comissao' => 100,
            'status' => 'confirmada',
            'competencia' => now()->format('Y-m'),
        ]);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/financeiro/comissoes');
        
        $response->assertStatus(200);
    }

    /** Vendedor não vê comissões de outros. */
    public function test_vendedor_nao_ve_comissoes_de_outros(): void
    {
        $user1 = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor1 = Vendedor::factory()->create(['usuario_id' => $user1->id]);
        
        $user2 = User::factory()->create(['perfil' => 'vendedor']);
        $vendedor2 = Vendedor::factory()->create(['usuario_id' => $user2->id]);
        
        $cliente = Cliente::factory()->create();
        $venda = Venda::factory()->create(['vendedor_id' => $vendedor2->id, 'valor_final' => 1000]);
        $pagamento = Pagamento::factory()->create(['venda_id' => $venda->id, 'valor' => 1000]);
        
        Comissao::create([
            'vendedor_id' => $vendedor2->id,
            'cliente_id' => $cliente->id,
            'venda_id' => $venda->id,
            'pagamento_id' => $pagamento->id,
            'valor_comissao' => 100,
            'status' => 'confirmada',
            'competencia' => now()->format('Y-m'),
        ]);
        
        Sanctum::actingAs($user1);
        
        $response = $this->getJson('/api/financeiro/comissoes');
        
        $response->assertStatus(200);
        // Deve retornar vazio ou apenas comissões do vendedor1
    }
}
```

### Validação
```bash
php artisan test --filter=VendaApiTest
php artisan test --filter=ClienteApiTest
php artisan test --filter=ComissaoApiTest
php artisan test
```

---

## Tarefa 4.3 — CI/CD com GitHub Actions

### Problema
- Não há pipeline de CI/CD
- Testes não rodam automaticamente
- Deploy manual propenso a erros

### Ação

#### 1. Criar workflow `.github/workflows/ci.yml`

```yaml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  backend:
    name: Backend Tests
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: basileia_test
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: postgres
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, pdo_pgsql, bcmath, intl, gd
          coverage: xdebug
      
      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT
      
      - name: Cache dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction --no-progress
        working-directory: backend
      
      - name: Copy .env
        run: cp .env.example .env
        working-directory: backend
      
      - name: Generate key
        run: php artisan key:generate
        working-directory: backend
      
      - name: Run migrations
        run: php artisan migrate --force
        working-directory: backend
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: basileia_test
          DB_USERNAME: postgres
          DB_PASSWORD: postgres
      
      - name: Run tests
        run: php artisan test --coverage --min=60
        working-directory: backend
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: basileia_test
          DB_USERNAME: postgres
          DB_PASSWORD: postgres
  
  frontend:
    name: Frontend Build & Lint
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: frontend/package-lock.json
      
      - name: Install dependencies
        run: npm ci
        working-directory: frontend
      
      - name: Run lint
        run: npm run lint
        working-directory: frontend
      
      - name: Type check
        run: npx tsc --noEmit
        working-directory: frontend
      
      - name: Build
        run: npm run build
        working-directory: frontend
        env:
          NEXT_PUBLIC_API_URL: http://localhost:8000
```

#### 2. Criar workflow `.github/workflows/deploy.yml` (opcional)

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: [backend, frontend]
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Deploy to server
        run: |
          # Adicionar comandos de deploy aqui
          # Exemplo: SSH, Docker push, etc.
          echo "Deploying to production..."
```

#### 3. Adicionar badge no README

```markdown
![CI](https://github.com/your-org/basilea-vendor/actions/workflows/ci.yml/badge.svg)
```

### Validação
1. Fazer push para branch `develop` → CI deve rodar automaticamente
2. Testes falhando → CI deve falhar
3. Testes passando → CI deve passar
4. Build do frontend deve funcionar
5. Lint deve passar

---

## Checklist da Fase 4

- [ ] **Tarefa 4.1** — Testes unitários para Services críticos
  - [ ] Criar `CommissionServiceTest.php` (5 testes)
  - [ ] Criar `AsaasServiceTest.php` (3 testes)
  - [ ] Criar `PagamentoServiceTest.php` (2 testes)
  - [ ] Rodar todos os testes → devem passar

- [ ] **Tarefa 4.2** — Testes de integração para rotas críticas
  - [ ] Criar `VendaApiTest.php` (5 testes)
  - [ ] Criar `ClienteApiTest.php` (3 testes)
  - [ ] Criar `ComissaoApiTest.php` (2 testes)
  - [ ] Rodar todos os testes → devem passar

- [ ] **Tarefa 4.3** — CI/CD com GitHub Actions
  - [ ] Criar `.github/workflows/ci.yml`
  - [ ] Configurar PostgreSQL no CI
  - [ ] Adicionar coverage mínimo de 60%
  - [ ] Testar push para branch → CI roda
  - [ ] (Opcional) Criar `.github/workflows/deploy.yml`

---

## Como validar a Fase 4 completa

1. **Testes:**
   ```bash
   php artisan test
   # Deve mostrar: Tests:  30+ passing
   ```

2. **Cobertura:**
   ```bash
   php artisan test --coverage
   # Deve mostrar: Coverage: 60%+ em Services críticos
   ```

3. **CI/CD:**
   - Fazer push para branch `develop`
   - Verificar que CI roda automaticamente
   - Verificar que todos os testes passam
   - Verificar que build do frontend funciona

4. **Métricas:**
   - Total de testes: 30+
   - Cobertura de Services críticos: 60%+
   - CI/CD funcionando em cada PR

---

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Testes falham por dependências externas | Usar Http::fake() para mockar APIs externas |
| CI demora muito | Cache de dependências, parallel jobs |
| Cobertura baixa | Focar em Services críticos primeiro |
| Deploy automático quebra produção | Começar com CI apenas, deploy manual |

---

## Métricas de Sucesso

### Após Fase 4
- [ ] Total de testes: 30+
- [ ] Cobertura de Services críticos: 60%+
- [ ] CI/CD funcionando em cada PR
- [ ] Zero testes falhando no CI
- [ ] Build do frontend funcionando no CI

---

**Próximo passo:** Começar pela **Tarefa 4.1** (Testes unitários para Services críticos).

---

# FASE 5: Arquitetura e Manutenção 🔵

## Objetivo
Refatorar para facilitar manutenção futura e adicionar monitoramento.

## Status da Fase 4
✅ **COMPLETA** — Todas as 3 tarefas implementadas.

### Resumo da Fase 4
- ✅ Tarefa 4.1: 10 testes unitários criados (CommissionService, AsaasService, PagamentoService)
- ✅ Tarefa 4.2: 10 testes de integração criados (Vendas, Clientes, Comissões)
- ✅ Tarefa 4.3: CI/CD com GitHub Actions configurado (PostgreSQL, coverage 60%, frontend build)

---

## Tarefa 5.1 — Consolidar Estrutura de Rotas do Frontend

### Problema
- Três estruturas de rotas paralelas: `(menu)`, `vendedor`, `gestor`
- Duplicação de páginas (ex: `vendas`, `clientes`, `comissoes` aparecem em 3 lugares)
- Dificuldade de manutenção e inconsistência de UX

### Ação

#### 1. Criar estrutura unificada

**Diretório:** `frontend/src/app/`

**Estrutura proposta:**
```
src/app/
├── dashboard/              → Todos (com permissões)
├── vendas/                 → Todos (com permissões)
├── clientes/               → Todos (com permissões)
├── comissoes/              → Todos (com permissões)
├── equipes/                → Master/Gestor
├── configuracoes/          → Todos (com permissões)
└── auth/                   → Login, 2FA, termos
```

#### 2. Implementar controle de acesso por perfil

**Arquivo:** `frontend/src/lib/permissions.ts`

```typescript
export const permissions = {
  dashboard: ['master', 'gestor', 'vendedor'],
  vendas: ['master', 'gestor', 'vendedor'],
  clientes: ['master', 'gestor', 'vendedor'],
  comissoes: ['master', 'gestor', 'vendedor'],
  equipes: ['master', 'gestor'],
  configuracoes: ['master', 'gestor', 'vendedor'],
};

export function hasPermission(perfil: string, rota: string): boolean {
  const allowed = permissions[rota as keyof typeof permissions];
  return allowed ? allowed.includes(perfil) : false;
}
```

#### 3. Criar páginas unificadas com controle de acesso

**Arquivo:** `frontend/src/app/dashboard/page.tsx`

```typescript
import { useAuth } from "@/context/AuthContext";
import { redirect } from "next/navigation";

export default function DashboardPage() {
  const { user } = useAuth();
  
  if (!user) redirect("/auth/login");
  
  // Redirecionar baseado no perfil
  if (user.perfil === "master") {
    return <MasterDashboard />;
  } else if (user.perfil === "gestor") {
    return <GestorDashboard />;
  } else {
    return <VendedorDashboard />;
  }
}
```

#### 4. Migrar páginas existentes

**Migrar de:**
- `src/app/(menu)/dashboard/` → `src/app/dashboard/`
- `src/app/(menu)/gestao-comercial/vendas/` → `src/app/vendas/`
- `src/app/(menu)/gestao-comercial/clientes/` → `src/app/clientes/`
- `src/app/vendedor/dashboard/` → `src/app/dashboard/` (com controle de acesso)
- `src/app/gestor/dashboard/` → `src/app/dashboard/` (com controle de acesso)

#### 5. Atualizar navigation.ts

**Arquivo:** `frontend/src/data/navigation.ts`

```typescript
export const navSections = [
  {
    title: "DASHBOARD",
    items: [
      { label: "Painel", icon: LayoutDashboard, href: "/dashboard" },
    ],
  },
  {
    title: "GESTÃO COMERCIAL",
    items: [
      { label: "Vendas", icon: ShoppingCart, href: "/vendas" },
      { label: "Clientes", icon: Users, href: "/clientes" },
      { label: "Comissões", icon: DollarSign, href: "/comissoes" },
    ],
  },
  {
    title: "CONFIGURAÇÕES",
    items: [
      { label: "Configurações", icon: Settings, href: "/configuracoes" },
    ],
  },
];
```

### Validação
1. Todas as rotas funcionam
2. Permissões respeitadas (vendedor não acessa equipes)
3. URLs mais limpas (`/vendas` em vez de `/gestao-comercial/vendas`)
4. Sidebar atualizada com novas rotas

---

## Tarefa 5.2 — Cache Redis para KPIs

### Problema
- Dashboard faz 4 queries separadas para KPIs
- Sem cache de resultados
- Performance lenta em conexões ruins

### Ação

#### 1. Adicionar Redis no Docker

**Arquivo:** `docker-compose.yml`

```yaml
services:
  postgres:
    # ... (existente)

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - basileia_redis:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  backend:
    # ... (existente)
    environment:
      # ... (existente)
      - CACHE_STORE=redis
      - REDIS_HOST=redis
      - REDIS_PASSWORD=null
      - REDIS_PORT=6379
    depends_on:
      - postgres
      - redis

volumes:
  basileia_pgdata:
  basileia_storage:
  basileia_logs:
  basileia_redis:
```

#### 2. Configurar cache no Laravel

**Arquivo:** `backend/config/cache.php`

```php
return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'basileia_cache_'),
];
```

**Arquivo:** `backend/.env`

```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 3. Implementar cache no DashboardController

**Arquivo:** `backend/app/Http/Controllers/Api/DashboardController.php`

```php
use Illuminate\Support\Facades\Cache;

public function index(Request $request)
{
    $user = $request->user();
    $cacheKey = "dashboard_kpis_{$user->id}_{$user->perfil}";
    
    return Cache::remember($cacheKey, 300, function () use ($user) {
        // Cálculos pesados
        $totalVendas = Venda::count();
        $vendasAtivas = Venda::whereNotIn('status', ['CANCELADO', 'EXPIRADO'])->count();
        $receitaBruta = Venda::whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('valor');
        $comissaoTotal = Venda::whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('comissao_vendedor_valor');
        
        return [
            'kpis' => [
                'total_vendas' => $totalVendas,
                'vendas_ativas' => $vendasAtivas,
                'receita_bruta' => $receitaBruta,
                'comissao_total' => $comissaoTotal,
            ],
            'charts' => [
                'receita_mensal' => $this->getReceitaMensal(),
            ],
        ];
    });
}
```

#### 4. Invalidar cache quando necessário

**Arquivo:** `backend/app/Services/Commission/CommissionService.php`

```php
// Após gerar comissão
Cache::tags(["user_{$vendedor->usuario_id}"])->flush();
```

### Validação
1. Dashboard carrega em < 2s
2. Redis armazena dados em cache
3. Após 5 minutos, cache expira e recalcula
4. Cache invalidado quando comissão é gerada

---

## Tarefa 5.3 — Monitoramento com Sentry

### Problema
- Não há logs centralizados
- Não há métricas de performance
- Não há alertas de erro
- Dificuldade de debug em produção

### Ação

#### 1. Instalar Sentry no Backend

**Comando:**
```bash
cd backend
composer require sentry/sentry-laravel
```

**Arquivo:** `backend/config/sentry.php`

```php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => 0.1, // 10% das requisições
    'profiles_sample_rate' => 0.1,
];
```

**Arquivo:** `backend/.env`

```env
SENTRY_LARAVEL_DSN=https://examplePublicKey@o0.ingest.sentry.io/0
```

#### 2. Instalar Sentry no Frontend

**Comando:**
```bash
cd frontend
npm install @sentry/nextjs
```

**Arquivo:** `frontend/next.config.js`

```javascript
const { withSentryConfig } = require('@sentry/nextjs');

module.exports = withSentryConfig({
  // ... config
}, {
  org: "basileia",
  project: "vendor-os",
});
```

**Arquivo:** `frontend/sentry.client.config.js`

```javascript
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
  tracesSampleRate: 0.1,
});
```

#### 3. Configurar alertas

**No dashboard do Sentry:**
- Alerta de erro crítico (5xx)
- Alerta de performance (latência > 2s)
- Alerta de taxa de erro (> 1%)

### Validação
1. Erros são enviados para Sentry
2. Dashboard do Sentry mostra erros
3. Alertas configurados e funcionando

---

## Tarefa 5.4 — Atualizar Documentação

### Problema
- Documentação desatualizada
- Docs dizem SQLite, projeto usa PostgreSQL
- API Contract toda "pendente"
- Módulos complexos sem README próprio

### Ação

#### 1. Corrigir DOCUMENTACAO_BASILEIA_VENDAS.md

**Arquivo:** `DOCUMENTACAO_BASILEIA_VENDAS.md`

```markdown
<!-- ANTES -->
**Tecnologia:** Laravel 11 + PHP 8.2 + SQLite

<!-- DEPOIS -->
**Tecnologia:** Laravel 11 + PHP 8.4 + PostgreSQL 15
```

#### 2. Atualizar API_CONTRACT.md

**Arquivo:** `frontend/API_CONTRACT.md`

```markdown
| Módulo | Método | Endpoint | Status |
|--------|--------|----------|--------|
| Auth | POST | /login | ✅ implementado |
| Auth | POST | /logout | ✅ implementado |
| Vendas | GET | /vendas | ✅ implementado |
| Vendas | POST | /vendas | ✅ implementado |
| Clientes | GET | /clientes | ✅ implementado |
| Clientes | POST | /clientes | ✅ implementado |
| Comissões | GET | /financeiro/comissoes | ✅ implementado |
| Dashboard | GET | /dashboard | ✅ implementado |
```

#### 3. Adicionar README de módulos críticos

**Arquivo:** `backend/app/Services/Commission/README.md`

```markdown
# Motor de Comissão

## Visão Geral
Motor único de cálculo de comissões para vendas e pagamentos.

## Regras
1. Comissão inicial na primeira venda
2. Comissão de recorrência em pagamentos subsequentes
3. Comissão de gestor quando vendedor tem gestor
4. Trava de fim de mês (não gera comissão após fim do mês do vencimento)
5. Antecipação de parcelado (comissão cheia no primeiro pagamento)

## Uso
```php
$resultado = CommissionService::gerarParaPagamento($pagamento);
```

## Testes
```bash
php artisan test --filter=CommissionServiceTest
php artisan test --filter=CommissionCalculatorTest
```
```

**Arquivo:** `backend/app/Services/Asaas/README.md`

```markdown
# Integração Asaas

## Visão Geral
Services especializados para integração com API do Asaas.

## Services
- `CustomerService`: Gestão de clientes
- `PaymentService`: Gestão de pagamentos
- `SubscriptionService`: Gestão de assinaturas
- `SplitService`: Gestão de splits

## Uso
```php
$asaas = new AsaasService();
$customer = $asaas->createCustomer('Nome', '12345678900');
$payment = $asaas->createPayment($customerId, 100.00, '2026-12-31', 'PIX', 'Descrição');
```

## Testes
```bash
php artisan test --filter=AsaasServiceTest
```
```

### Validação
1. Documentação reflete realidade do código
2. Novos devs conseguem entender o sistema
3. READMEs dos módulos críticos documentados

---

## Checklist da Fase 5

- [ ] **Tarefa 5.1** — Consolidar estrutura de rotas do frontend
  - [ ] Criar estrutura unificada (`/dashboard`, `/vendas`, `/clientes`)
  - [ ] Implementar controle de acesso por perfil
  - [ ] Migrar páginas existentes
  - [ ] Atualizar navigation.ts
  - [ ] Testar todas as rotas

- [ ] **Tarefa 5.2** — Cache Redis para KPIs
  - [ ] Adicionar Redis no Docker
  - [ ] Configurar cache no Laravel
  - [ ] Implementar cache no DashboardController
  - [ ] Invalidar cache quando necessário
  - [ ] Testar performance

- [ ] **Tarefa 5.3** — Monitoramento com Sentry
  - [ ] Instalar Sentry no Backend
  - [ ] Instalar Sentry no Frontend
  - [ ] Configurar alertas
  - [ ] Testar envio de erros

- [ ] **Tarefa 5.4** — Atualizar Documentação
  - [ ] Corrigir DOCUMENTACAO_BASILEIA_VENDAS.md
  - [ ] Atualizar API_CONTRACT.md
  - [ ] Adicionar README de módulos críticos
  - [ ] Verificar consistência da documentação

---

## Como validar a Fase 5 completa

1. **Arquitetura:**
   - URLs limpas e consistentes
   - Permissões funcionando
   - Sidebar atualizada

2. **Performance:**
   - Dashboard carrega em < 2s
   - Redis funcionando
   - Cache invalidado corretamente

3. **Monitoramento:**
   - Erros enviados para Sentry
   - Alertas configurados
   - Dashboard do Sentry funcionando

4. **Documentação:**
   - Docs atualizados
   - READMEs dos módulos críticos
   - API Contract completo

---

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Mudança de URLs quebra links | Implementar redirects para URLs antigas |
| Redis não disponível | Fallback para cache de arquivo |
| Sentry muito verboso | Ajustar sample rate |
| Documentação desatualizada | Revisar periodicamente |

---

## Métricas de Sucesso

### Após Fase 5
- [ ] URLs limpas e consistentes
- [ ] Dashboard carrega em < 2s
- [ ] Erros monitorados no Sentry
- [ ] Documentação atualizada
- [ ] READMEs dos módulos críticos

---

**Próximo passo:** Começar pela **Tarefa 5.1** (Consolidar estrutura de rotas do frontend).

---

# FASE 5: Arquitetura e Manutenção 🔵

## Objetivo
Refatorar para facilitar manutenção futura e adicionar monitoramento.

## Status da Fase 4
✅ **COMPLETA** — Todas as 3 tarefas implementadas.

### Resumo da Fase 4
- ✅ Tarefa 4.1: 10 testes unitários criados (CommissionService, AsaasService, PagamentoService)
- ✅ Tarefa 4.2: 10 testes de integração criados (Vendas, Clientes, Comissões)
- ✅ Tarefa 4.3: CI/CD com GitHub Actions configurado (PostgreSQL, coverage 60%, frontend build)

---

## Tarefa 5.1 — Consolidar Estrutura de Rotas do Frontend

### Problema
- Três estruturas de rotas paralelas: `(menu)`, `vendedor`, `gestor`
- Duplicação de páginas (ex: `vendas`, `clientes`, `comissoes` aparecem em 3 lugares)
- Dificuldade de manutenção e inconsistência de UX

### Ação

#### 1. Criar estrutura unificada

**Diretório:** `frontend/src/app/`

**Estrutura proposta:**
```
src/app/
├── dashboard/              → Todos (com permissões)
├── vendas/                 → Todos (com permissões)
├── clientes/               → Todos (com permissões)
├── comissoes/              → Todos (com permissões)
├── equipes/                → Master/Gestor
├── configuracoes/          → Todos (com permissões)
└── auth/                   → Login, 2FA, termos
```

#### 2. Implementar controle de acesso por perfil

**Arquivo:** `frontend/src/lib/permissions.ts`

```typescript
export const permissions = {
  dashboard: ['master', 'gestor', 'vendedor'],
  vendas: ['master', 'gestor', 'vendedor'],
  clientes: ['master', 'gestor', 'vendedor'],
  comissoes: ['master', 'gestor', 'vendedor'],
  equipes: ['master', 'gestor'],
  configuracoes: ['master', 'gestor', 'vendedor'],
};

export function hasPermission(perfil: string, rota: string): boolean {
  const allowed = permissions[rota as keyof typeof permissions];
  return allowed ? allowed.includes(perfil) : false;
}
```

#### 3. Criar páginas unificadas com controle de acesso

**Arquivo:** `frontend/src/app/dashboard/page.tsx`

```typescript
import { useAuth } from "@/context/AuthContext";
import { redirect } from "next/navigation";

export default function DashboardPage() {
  const { user } = useAuth();
  
  if (!user) redirect("/auth/login");
  
  // Redirecionar baseado no perfil
  if (user.perfil === "master") {
    return <MasterDashboard />;
  } else if (user.perfil === "gestor") {
    return <GestorDashboard />;
  } else {
    return <VendedorDashboard />;
  }
}
```

#### 4. Migrar páginas existentes

**Migrar de:**
- `src/app/(menu)/dashboard/` → `src/app/dashboard/`
- `src/app/(menu)/gestao-comercial/vendas/` → `src/app/vendas/`
- `src/app/(menu)/gestao-comercial/clientes/` → `src/app/clientes/`
- `src/app/vendedor/dashboard/` → `src/app/dashboard/` (com controle de acesso)
- `src/app/gestor/dashboard/` → `src/app/dashboard/` (com controle de acesso)

#### 5. Atualizar navigation.ts

**Arquivo:** `frontend/src/data/navigation.ts`

```typescript
export const navSections = [
  {
    title: "DASHBOARD",
    items: [
      { label: "Painel", icon: LayoutDashboard, href: "/dashboard" },
    ],
  },
  {
    title: "GESTÃO COMERCIAL",
    items: [
      { label: "Vendas", icon: ShoppingCart, href: "/vendas" },
      { label: "Clientes", icon: Users, href: "/clientes" },
      { label: "Comissões", icon: DollarSign, href: "/comissoes" },
    ],
  },
  {
    title: "CONFIGURAÇÕES",
    items: [
      { label: "Configurações", icon: Settings, href: "/configuracoes" },
    ],
  },
];
```

### Validação
1. Todas as rotas funcionam
2. Permissões respeitadas (vendedor não acessa equipes)
3. URLs mais limpas (`/vendas` em vez de `/gestao-comercial/vendas`)
4. Sidebar atualizada com novas rotas

---

## Tarefa 5.2 — Cache Redis para KPIs

### Problema
- Dashboard faz 4 queries separadas para KPIs
- Sem cache de resultados
- Performance lenta em conexões ruins

### Ação

#### 1. Adicionar Redis no Docker

**Arquivo:** `backend/docker-compose.yml`

```yaml
services:
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  redis-data:
```

#### 2. Configurar cache no Laravel

**Arquivo:** `backend/config/cache.php`

```php
return [
    'default' => env('CACHE_DRIVER', 'redis'),
    
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
    
    'prefix' => env('CACHE_PREFIX', 'basileia_cache_'),
];
```

**Arquivo:** `backend/.env`

```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 3. Implementar cache no DashboardController

**Arquivo:** `backend/app/Http/Controllers/Api/DashboardController.php`

```php
use Illuminate\Support\Facades\Cache;

public function index(Request $request)
{
    $user = $request->user();
    $cacheKey = "dashboard_kpis_{$user->id}_{$user->perfil}";
    
    return Cache::remember($cacheKey, 300, function () use ($user) {
        // Cálculos pesados
        $totalVendas = Venda::count();
        $vendasAtivas = Venda::whereNotIn('status', ['CANCELADO', 'EXPIRADO'])->count();
        $receitaBruta = Venda::whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('valor');
        $comissaoTotal = Venda::whereIn('status', ['PAGO', 'RECEIVED', 'CONFIRMED'])->sum('comissao_vendedor_valor');
        
        return [
            'kpis' => [
                'total_vendas' => $totalVendas,
                'vendas_ativas' => $vendasAtivas,
                'receita_bruta' => $receitaBruta,
                'comissao_total' => $comissaoTotal,
            ],
            'charts' => [
                'receita_mensal' => $this->getReceitaMensal(),
            ],
        ];
    });
}
```

#### 4. Invalidar cache quando necessário

**Arquivo:** `backend/app/Services/Commission/CommissionService.php`

```php
// Após gerar comissão
Cache::tags(["user_{$vendedor->usuario_id}"])->flush();
```

### Validação
1. Dashboard carrega em < 2s
2. Redis armazena dados em cache
3. Após 5 minutos, cache expira e recalcula
4. Cache invalidado quando comissão é gerada

---

## Tarefa 5.3 — Monitoramento com Sentry

### Problema
- Não há logs centralizados
- Não há métricas de performance
- Não há alertas de erro
- Dificuldade de debug em produção

### Ação

#### 1. Instalar Sentry no Backend

**Comando:**
```bash
cd backend
composer require sentry/sentry-laravel
```

**Arquivo:** `backend/config/sentry.php`

```php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => 0.1, // 10% das requisições
    'profiles_sample_rate' => 0.1,
];
```

**Arquivo:** `backend/.env`

```env
SENTRY_LARAVEL_DSN=https://examplePublicKey@o0.ingest.sentry.io/0
```

#### 2. Instalar Sentry no Frontend

**Comando:**
```bash
cd frontend
npm install @sentry/nextjs
```

**Arquivo:** `frontend/next.config.js`

```javascript
const { withSentryConfig } = require('@sentry/nextjs');

module.exports = withSentryConfig({
  // ... config
}, {
  org: "basileia",
  project: "vendor-os",
});
```

**Arquivo:** `frontend/sentry.client.config.js`

```javascript
import * as Sentry from "@sentry/nextjs";

Sentry.init({
  dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
  tracesSampleRate: 0.1,
});
```

#### 3. Configurar alertas

**No dashboard do Sentry:**
- Alerta de erro crítico (5xx)
- Alerta de performance (latência > 2s)
- Alerta de taxa de erro (> 1%)

### Validação
1. Erros são enviados para Sentry
2. Dashboard do Sentry mostra erros
3. Alertas configurados e funcionando

---

## Tarefa 5.4 — Atualizar Documentação

### Problema
- Documentação desatualizada
- Docs dizem SQLite, projeto usa PostgreSQL
- API Contract toda "pendente"
- Módulos complexos sem README próprio

### Ação

#### 1. Corrigir DOCUMENTACAO_BASILEIA_VENDAS.md

**Arquivo:** `DOCUMENTACAO_BASILEIA_VENDAS.md`

```markdown
<!-- ANTES -->
**Tecnologia:** Laravel 11 + PHP 8.2 + SQLite

<!-- DEPOIS -->
**Tecnologia:** Laravel 11 + PHP 8.4 + PostgreSQL 15
```

#### 2. Atualizar API_CONTRACT.md

**Arquivo:** `frontend/API_CONTRACT.md`

```markdown
| Módulo | Método | Endpoint | Status |
|--------|--------|----------|--------|
| Auth | POST | /login | ✅ implementado |
| Auth | POST | /logout | ✅ implementado |
| Vendas | GET | /vendas | ✅ implementado |
| Vendas | POST | /vendas | ✅ implementado |
| Clientes | GET | /clientes | ✅ implementado |
| Clientes | POST | /clientes | ✅ implementado |
| Comissões | GET | /financeiro/comissoes | ✅ implementado |
| Dashboard | GET | /dashboard | ✅ implementado |
```

#### 3. Adicionar README de módulos críticos

**Arquivo:** `backend/app/Services/Commission/README.md`

```markdown
# Motor de Comissão

## Visão Geral
Motor único de cálculo de comissões para vendas e pagamentos.

## Regras
1. Comissão inicial na primeira venda
2. Comissão de recorrência em pagamentos subsequentes
3. Comissão de gestor quando vendedor tem gestor
4. Trava de fim de mês (não gera comissão após fim do mês do vencimento)
5. Antecipação de parcelado (comissão cheia no primeiro pagamento)

## Uso
```php
$resultado = CommissionService::gerarParaPagamento($pagamento);
```

## Testes
```bash
php artisan test --filter=CommissionServiceTest
php artisan test --filter=CommissionCalculatorTest
```
```

**Arquivo:** `backend/app/Services/Asaas/README.md`

```markdown
# Integração Asaas

## Visão Geral
Services especializados para integração com API do Asaas.

## Services
- `CustomerService`: Gestão de clientes
- `PaymentService`: Gestão de pagamentos
- `SubscriptionService`: Gestão de assinaturas
- `SplitService`: Gestão de splits

## Uso
```php
$asaas = new AsaasService();
$customer = $asaas->createCustomer('Nome', '12345678900');
$payment = $asaas->createPayment($customerId, 100.00, '2026-12-31', 'PIX', 'Descrição');
```

## Testes
```bash
php artisan test --filter=AsaasServiceTest
```
```

### Validação
1. Documentação reflete realidade do código
2. Novos devs conseguem entender o sistema
3. READMEs dos módulos críticos documentados

---

## Checklist da Fase 5

- [ ] **Tarefa 5.1** — Consolidar estrutura de rotas do frontend
  - [ ] Criar estrutura unificada (`/dashboard`, `/vendas`, `/clientes`)
  - [ ] Implementar controle de acesso por perfil
  - [ ] Migrar páginas existentes
  - [ ] Atualizar navigation.ts
  - [ ] Testar todas as rotas

- [ ] **Tarefa 5.2** — Cache Redis para KPIs
  - [ ] Adicionar Redis no Docker
  - [ ] Configurar cache no Laravel
  - [ ] Implementar cache no DashboardController
  - [ ] Invalidar cache quando necessário
  - [ ] Testar performance

- [ ] **Tarefa 5.3** — Monitoramento com Sentry
  - [ ] Instalar Sentry no Backend
  - [ ] Instalar Sentry no Frontend
  - [ ] Configurar alertas
  - [ ] Testar envio de erros

- [ ] **Tarefa 5.4** — Atualizar Documentação
  - [ ] Corrigir DOCUMENTACAO_BASILEIA_VENDAS.md
  - [ ] Atualizar API_CONTRACT.md
  - [ ] Adicionar README de módulos críticos
  - [ ] Verificar consistência da documentação

---

## Como validar a Fase 5 completa

1. **Arquitetura:**
   - URLs limpas e consistentes
   - Permissões funcionando
   - Sidebar atualizada

2. **Performance:**
   - Dashboard carrega em < 2s
   - Redis funcionando
   - Cache invalidado corretamente

3. **Monitoramento:**
   - Erros enviados para Sentry
   - Alertas configurados
   - Dashboard do Sentry funcionando

4. **Documentação:**
   - Docs atualizados
   - READMEs dos módulos críticos
   - API Contract completo

---

## Riscos e Mitigações

| Risco | Mitigação |
|-------|-----------|
| Mudança de URLs quebra links | Implementar redirects para URLs antigas |
| Redis não disponível | Fallback para cache de arquivo |
| Sentry muito verboso | Ajustar sample rate |
| Documentação desatualizada | Revisar periodicamente |

---

## Métricas de Sucesso

### Após Fase 5
- [ ] URLs limpas e consistentes
- [ ] Dashboard carrega em < 2s
- [ ] Erros monitorados no Sentry
- [ ] Documentação atualizada
- [ ] READMEs dos módulos críticos

---

**Próximo passo:** Começar pela **Tarefa 5.1** (Consolidar estrutura de rotas do frontend).