<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_gestor_configs')) {
            Schema::create('chat_gestor_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gestor_id')->constrained('users')->onDelete('cascade');
                $table->boolean('chat_enabled')->default(false);
                $table->string('numero_whatsapp')->nullable();
                $table->string('whatsapp_provider')->nullable();
                $table->string('whatsapp_api_token')->nullable();
                $table->integer('max_conversas_simultaneas')->nullable()->default(0);
                $table->integer('sla_primeiro_contato')->default(30);
                $table->integer('sla_inatividade')->default(60);
                $table->integer('retorno_dias')->default(7);
                $table->timestamps();

                $table->unique(['gestor_id'], 'chat_gestor_configs_gestor_unique');
            });
        }

        if (Schema::hasTable('vendedores')) {
            Schema::table('vendedores', function (Blueprint $table) {
                if (!Schema::hasColumn('vendedores', 'max_conversas')) {
                    $table->integer('max_conversas')->default(0)->nullable()->after('chat_enabled');
                }
                if (!Schema::hasColumn('vendedores', 'chat_disabled')) {
                    $table->boolean('chat_disabled')->default(false)->after('max_conversas');
                }
            });
        }

        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_conversations', 'motivo_perda')) {
                    $table->text('motivo_perda')->nullable()->after('status');
                }
                if (!Schema::hasColumn('chat_conversations', 'resolved_by')) {
                    $table->string('resolved_by')->nullable()->after('motivo_perda');
                }
                if (!Schema::hasColumn('chat_conversations', 'resolved_at')) {
                    $table->timestamp('resolved_at')->nullable()->after('resolved_by');
                }
            });
        }

        if (Schema::hasTable('lead_inbound_logs')) {
            Schema::table('lead_inbound_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_inbound_logs', 'contact_id')) {
                    $table->foreignId('contact_id')->nullable()->constrained('chat_contacts')->onDelete('set null');
                }
                if (!Schema::hasColumn('lead_inbound_logs', 'conversa_id')) {
                    $table->foreignId('conversa_id')->nullable()->constrained('chat_conversations')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('lead_inbound_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('conversa_id');
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['motivo_perda', 'resolved_by', 'resolved_at']);
        });

        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn(['max_conversas', 'chat_disabled']);
        });

        Schema::dropIfExists('chat_gestor_configs');
    }
};