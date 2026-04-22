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
                if (!Schema::hasColumn('chat_messages', 'source_id')) {
                    $table->string('source_id')->nullable()->index();
                }
            });
            
            // Raw SQL for idempotency on PostgreSQL
            if (Schema::hasColumn('chat_messages', 'external_message_id')) {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS chat_messages_external_unique ON chat_messages (external_message_id)');
            }
            if (Schema::hasColumn('chat_messages', 'source_id')) {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS chat_messages_source_unique ON chat_messages (source_id)');
            }
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
            if (Schema::hasColumn('chat_contacts', 'phone')) {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS chat_contacts_phone_unique ON chat_contacts (phone)');
            }
        }

        if (Schema::hasTable('chat_conversations')) {
            if (Schema::hasColumn('chat_conversations', 'gestor_id') && Schema::hasColumn('chat_conversations', 'status') && Schema::hasColumn('chat_conversations', 'is_atendido')) {
                DB::statement('CREATE INDEX IF NOT EXISTS chat_conversations_gestor_status_atendido_idx ON chat_conversations (gestor_id, status, is_atendido)');
            }
            if (Schema::hasColumn('chat_conversations', 'gestor_id') && Schema::hasColumn('chat_conversations', 'vendedor_id') && Schema::hasColumn('chat_conversations', 'status')) {
                DB::statement('CREATE INDEX IF NOT EXISTS chat_conversations_gestor_vendedor_status_idx ON chat_conversations (gestor_id, vendedor_id, status)');
            }
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