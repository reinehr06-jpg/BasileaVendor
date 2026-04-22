<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->enum('ciclo', ['mensal', 'anual'])->default('mensal')->after('is_active');
        });

        // ========================================
        // CHAT MODULE - Bloco A (MVP)
        // Tabelas do Chat interno
        // ========================================

        // Contatos (leads) - todos os leads que entram no chat
        Schema::create('chat_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('tags')->nullable();
            $table->string('source')->nullable();
            $table->string('source_id')->nullable();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('phone');
            $table->index('email');
            $table->index('source');
        });

        // Conversas
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('chat_contacts')->onDelete('cascade');
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
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
            
            $table->index('gestor_id');
            $table->index('vendedor_id');
            $table->index('status');
            $table->index('is_atendido');
            $table->index('last_message_at');
        });

        // Mensagens
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable();
            $table->string('sender_type')->nullable();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('tipo', ['texto', 'imagem', 'audio', 'documento', 'sistema']);
            $table->text('conteudo');
            $table->string('external_message_id')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type')->nullable();
            $table->string('attachment_size')->nullable();
            $table->enum('delivery_status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('conversa_id');
            $table->index('external_message_id');
            $table->index('created_at');
        });

        // Configurações de WhatsApp por gestor
        Schema::create('chat_whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('numero_phone')->nullable();
            $table->string('numero_id')->nullable();
            $table->string('api_token')->nullable();
            $table->string('webhook_verify_token')->nullable();
            $table->enum('provider', ['meta', 'Take', 'WppConnect', 'Evolution'])->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Fila de distribuição (Round Robin)
        Schema::create('chat_distribuicao_fila', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->integer('ordem')->default(0);
            $table->integer('total_atendidos')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('ultimo_atendimento_at')->nullable();
            $table->timestamps();
            
            $table->unique(['gestor_id', 'vendedor_id']);
        });

        // Log de atividades do chat
        Schema::create('chat_atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->nullable()->constrained('chat_conversations')->onDelete('set null');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
            $table->string('acao');
            $table->text('detalhes')->nullable();
            $table->timestamps();
            
            $table->index('conversa_id');
            $table->index('acao');
        });

        // Settings para feature flag
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(false)->after('key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('chat_enabled');
        });
        
        Schema::dropIfExists('chat_atividades');
        Schema::dropIfExists('chat_distribuicao_fila');
        Schema::dropIfExists('chat_whatsapp_configs');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('chat_contacts');
    }
};
