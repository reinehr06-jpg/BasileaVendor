<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\Chat\ChatDistributionService::class);
    }

    public function boot(): void
    {
        $this->initializeChatModule();
    }

    protected function initializeChatModule(): void
    {
        try {
            if (!Schema::hasTable('chat_conversas')) {
                return;
            }

            $this->ensureFeatureFlags();
            $this->ensureGestorConfigs();
            $this->ensureQueueInitialized();

        } catch (\Exception $e) {
            Log::error('ChatServiceProvider: Erro na inicialização', ['error' => $e->getMessage()]);
        }
    }

    protected function ensureFeatureFlags(): void
    {
        $settings = [
            'chat_enabled' => true,
            'chat_sla_primeiro_contato' => 30,
            'chat_sla_inatividade' => 60,
            'chat_retorno_dias' => 7,
        ];

        foreach ($settings as $key => $default) {
            if (!Setting::get($key)) {
                Setting::set($key, $default);
            }
        }
    }

    protected function ensureGestorConfigs(): void
    {
        $gestores = User::where('perfil', 'gestor')->get();
        
        foreach ($gestores as $gestor) {
            DB::table('chat_gestor_configs')->updateOrInsert(
                ['gestor_id' => $gestor->id],
                [
                    'chat_enabled' => false,
                    'sla_primeiro_contato' => 30,
                    'sla_inatividade' => 60,
                    'retorno_dias' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function ensureQueueInitialized(): void
    {
        $gestores = User::where('perfil', 'gestor')->get();

        foreach ($gestores as $gestor) {
            $vendedores = Vendedor::where('gestor_id', $gestor->id)
                ->where('status', 'ativo')
                ->where(function ($q) {
                    $q->whereNull('chat_enabled')->orWhere('chat_enabled', true);
                })
                ->where(function ($q) {
                    $q->whereNull('chat_disabled')->orWhere('chat_disabled', false);
                })
                ->get();

            foreach ($vendedores as $index => $vendedor) {
                DB::table('chat_distribuicao_fila')->updateOrInsert(
                    ['gestor_id' => $gestor->id, 'vendedor_id' => $vendedor->id],
                    [
                        'ordem' => $index,
                        'is_active' => true,
                        'total_atendidos' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}