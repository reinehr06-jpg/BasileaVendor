<?php

namespace App\Providers;

use App\Http\View\Composers\AdminComposer;
use App\Services\AI\AIService;
use App\Services\CampanhaMetricsService;
use App\Services\VersionCheckService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar AIService como singleton
        $this->app->singleton(AIService::class);

        // Registrar CampanhaMetricsService
        $this->app->singleton(CampanhaMetricsService::class, function ($app) {
            return new CampanhaMetricsService();
        });

        // Registrar VersionCheckService
        $this->app->singleton(VersionCheckService::class, function ($app) {
            return new VersionCheckService();
        });
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
    }
}
