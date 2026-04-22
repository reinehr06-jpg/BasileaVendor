<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_contacts')) {
            Schema::create('chat_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('phone', 20)->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('source')->default('whatsapp');
            $table->string('external_id')->nullable()->index();
            $table->boolean('is_contact_admin')->default(false)->index();
            $table->timestamps();
            
                $table->unique(['tenant_id', 'phone']);
            });
        }

        if (!Schema::hasTable('chat_conversations')) {
            Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('chat_contacts')->onDelete('cascade');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
            $table->string('status', 20)->default('open')->index();
            $table->string('atendimento_status', 20)->default('nao_atendido')->index();
            $table->boolean('is_resolved')->default(false)->index();
            $table->timestamp('last_inbound_at')->nullable()->index();
            $table->timestamp('last_outbound_at')->nullable()->index();
            $table->timestamp('assigned_at')->nullable();
            $table->integer('unread_count')->default(0)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'vendedor_id', 'status']);
                $table->index(['tenant_id', 'atendimento_status']);
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('chat_contacts')->onDelete('cascade');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
            $table->string('direction', 10)->index();
            $table->text('content');
            $table->string('type', 20)->default('text');
            $table->string('external_message_id')->nullable()->index();
            $table->string('source_id')->nullable()->index();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['external_message_id'], 'chat_messages_external_unique');
            $table->unique(['source_id'], 'chat_messages_source_unique');
                $table->index(['conversation_id', 'created_at']);
            });
        }

        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('chat_messages')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['message_id', 'vendedor_id']);
        });

        Schema::create('chat_provider_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('provider', 50);
            $table->string('name');
            $table->text('config_json');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'provider', 'name']);
        });

        Schema::create('chat_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('provider', 50);
            $table->string('event_type', 50);
            $table->string('external_id')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_webhook_logs');
        Schema::dropIfExists('chat_provider_configs');
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('chat_contacts');
    }
};