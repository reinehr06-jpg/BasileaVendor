<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                if (Schema::hasColumn('chat_messages', 'external_message_id')) {
                    // Try to add unique index only if column exists
                    try {
                        $table->unique(['external_message_id'], 'chat_messages_external_unique');
                    } catch (\Exception $e) {}
                }
                if (!Schema::hasColumn('chat_messages', 'source_id')) {
                    $table->string('source_id')->nullable()->index();
                    $table->unique(['source_id'], 'chat_messages_source_unique');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'chat_sla_primeiro_contato')) {
                    $table->integer('chat_sla_primeiro_contato')->default(30)->after('chat_enabled');
                }
                if (!Schema::hasColumn('settings', 'chat_sla_inatividade')) {
                    $table->integer('chat_sla_inatividade')->default(60)->after('chat_sla_primeiro_contato');
                }
                if (!Schema::hasColumn('settings', 'chat_retorno_dias')) {
                    $table->integer('chat_retorno_dias')->default(7)->after('chat_sla_inatividade');
                }
                if (!Schema::hasColumn('settings', 'chat_google_ads_webhook_key')) {
                    $table->string('chat_google_ads_webhook_key')->nullable()->after('chat_retorno_dias');
                }
            });
        }

        if (Schema::hasTable('chat_contacts')) {
            Schema::table('chat_contacts', function (Blueprint $table) {
                if (Schema::hasColumn('chat_contacts', 'phone')) {
                    try {
                        $table->unique(['phone'], 'chat_contacts_phone_unique');
                    } catch (\Exception $e) {}
                }
            });
        }

        if (Schema::hasTable('chat_conversations')) {
            Schema::table('chat_conversations', function (Blueprint $table) {
                try {
                    $table->index(['gestor_id', 'status', 'is_atendido']);
                    $table->index(['gestor_id', 'vendedor_id', 'status']);
                } catch (\Exception $e) {}
            });
        }
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropUnique('chat_messages_external_unique');
            $table->dropUnique('chat_messages_source_unique');
            $table->dropColumn('source_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'chat_sla_primeiro_contato',
                'chat_sla_inatividade',
                'chat_retorno_dias',
                'chat_google_ads_webhook_key'
            ]);
        });
    }
};