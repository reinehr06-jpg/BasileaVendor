<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\Chat\ChatDistributionService::class);
    }

    public function boot(): void
    {
        // $this->initializeChatModule();
    }

    protected function initializeChatModule(): void
    {
        try {
            $this->ensureChatTables();
            $this->ensureFeatureFlags();
            $this->ensureGestorConfigs();
            $this->ensureQueueInitialized();

        } catch (\Exception $e) {
            Log::error('ChatServiceProvider: Erro na inicialização', ['error' => $e->getMessage()]);
        }
    }

    protected function ensureChatTables(): void
    {
        if (!Schema::hasTable('chat_contacts')) {
            Schema::create('chat_contacts', function (Blueprint $table) {
                $table->id();
                $table->string('nome')->nullable();
                $table->string('telefone')->nullable();
                $table->string('email')->nullable();
                $table->string('avatar_url')->nullable();
                $table->json('tags')->nullable();
                $table->string('source')->nullable();
                $table->string('source_id')->nullable();
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->timestamps();
                $table->index('telefone');
                $table->index('email');
                $table->index('source');
            });
        }

        if (!Schema::hasTable('chat_conversas')) {
            Schema::create('chat_conversas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contact_id');
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->unsignedBigInteger('vendedor_id')->nullable();
                $table->enum('status', ['aberta', 'pendente', 'resolvida'])->default('aberta');
                $table->boolean('pinned')->default(false);
                $table->boolean('is_atendido')->default(false);
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('last_inbound_at')->nullable();
                $table->timestamp('last_outbound_at')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('unread_at')->nullable();
                $table->integer('unread_count')->default(0);
                $table->integer('transfer_count')->default(0);
                $table->timestamps();
                $table->index(['gestor_id', 'status']);
                $table->index(['vendedor_id', 'status']);
            });
        }

        if (!Schema::hasTable('chat_mensagens')) {
            Schema::create('chat_mensagens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversa_id');
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('sender_type')->nullable();
                $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
                $table->enum('tipo', ['texto', 'imagem', 'audio', 'documento', 'sistema'])->default('texto');
                $table->text('conteudo')->nullable();
                $table->string('external_message_id')->nullable();
                $table->string('source_id')->nullable();
                $table->string('attachment_url')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_type')->nullable();
                $table->string('attachment_size')->nullable();
                $table->enum('delivery_status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('sent');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index('conversa_id');
                $table->unique('external_message_id');
                $table->unique('source_id');
            });
        }

        if (!Schema::hasTable('chat_distribuicao_fila')) {
            Schema::create('chat_distribuicao_fila', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->unsignedBigInteger('vendedor_id');
                $table->integer('ordem')->default(0);
                $table->integer('total_atendidos')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('ultimo_atendimento_at')->nullable();
                $table->timestamps();
                $table->unique(['gestor_id', 'vendedor_id']);
            });
        }

        if (!Schema::hasTable('chat_atividades')) {
            Schema::create('chat_atividades', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversa_id')->nullable();
                $table->unsignedBigInteger('vendedor_id')->nullable();
                $table->string('acao');
                $table->text('detalhes')->nullable();
                $table->timestamps();
                $table->index(['conversa_id', 'acao']);
            });
        }

        if (!Schema::hasTable('chat_whatsapp_configs')) {
            Schema::create('chat_whatsapp_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('gestor_id')->nullable();
                $table->string('numero_telefone')->nullable();
                $table->string('numero_id')->nullable();
                $table->string('api_token')->nullable();
                $table->string('webhook_verify_token')->nullable();
                $table->string('provider')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_gestor_configs')) {
            Schema::create('chat_gestor_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('gestor_id')->unique();
                $table->boolean('chat_enabled')->default(false);
                $table->string('numero_whatsapp')->nullable();
                $table->string('whatsapp_provider')->nullable();
                $table->string('whatsapp_api_token')->nullable();
                $table->integer('max_conversas_simultaneas')->nullable()->default(0);
                $table->integer('sla_primeiro_contato')->default(30);
                $table->integer('sla_inatividade')->default(60);
                $table->integer('retorno_dias')->default(7);
                $table->timestamps();
            });
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