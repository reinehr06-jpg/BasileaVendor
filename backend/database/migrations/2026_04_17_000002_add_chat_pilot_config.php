<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('vendedores', function (Blueprint $table) {
            $table->integer('max_conversas')->default(0)->nullable()->after('chat_enabled');
            $table->boolean('chat_disabled')->default(false)->after('max_conversas');
        });

        Schema::table('chat_conversas', function (Blueprint $table) {
            $table->text('motivo_perda')->nullable()->after('status');
            $table->string('resolved_by')->nullable()->after('motivo_perda');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });

        Schema::table('lead_inbound_logs', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->constrained('chat_contacts')->onDelete('set null');
            $table->foreignId('conversa_id')->nullable()->constrained('chat_conversas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('lead_inbound_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('conversa_id');
        });

        Schema::table('chat_conversas', function (Blueprint $table) {
            $table->dropColumn(['motivo_perda', 'resolved_by', 'resolved_at']);
        });

        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn(['max_conversas', 'chat_disabled']);
        });

        Schema::dropIfExists('chat_gestor_configs');
    }
};