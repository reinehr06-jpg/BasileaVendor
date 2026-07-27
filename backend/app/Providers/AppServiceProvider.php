<?php

namespace App\Providers;

use App\Http\View\Composers\AdminComposer;
use App\Services\AI\AIService;
use App\Services\AI\StrictPromptValidator;
use App\Services\CampanhaMetricsService;
use App\Services\Integration\IntegrationTestService;
use App\Services\VersionCheckService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIService::class);
        $this->app->singleton(CampanhaMetricsService::class);
        $this->app->singleton(VersionCheckService::class);
        $this->app->singleton(IntegrationTestService::class);
        $this->app->singleton(StrictPromptValidator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Admin view composer for update alerts
        View::composer('admin.*', AdminComposer::class);

        // Definir Rate Limiters
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
    }
}
