<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_mensagens', function (Blueprint $table) {
            $table->unique(['external_message_id'], 'chat_mensagens_external_unique');
            $table->string('source_id')->nullable()->index();
            $table->unique(['source_id'], 'chat_mensagens_source_unique');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->integer('chat_sla_primeiro_contato')->default(30)->after('chat_enabled');
            $table->integer('chat_sla_inatividade')->default(60)->after('chat_sla_primeiro_contato');
            $table->integer('chat_retorno_dias')->default(7)->after('chat_sla_inatividade');
            $table->string('chat_google_ads_webhook_key')->nullable()->after('chat_retorno_dias');
        });

        Schema::table('chat_contacts', function (Blueprint $table) {
            $table->unique(['telefone'], 'chat_contacts_telefone_unique');
        });

        Schema::table('chat_conversas', function (Blueprint $table) {
            $table->index(['gestor_id', 'status', 'is_atendido']);
            $table->index(['gestor_id', 'vendedor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_mensagens', function (Blueprint $table) {
            $table->dropUnique('chat_mensagens_external_unique');
            $table->dropUnique('chat_mensagens_source_unique');
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