<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_inbound_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
            $table->string('source', 50);
            $table->json('raw_payload');
            $table->string('leadgen_id')->nullable()->index();
            $table->string('form_id')->nullable();
            $table->string('ad_id')->nullable();
            $table->string('adgroup_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'source', 'created_at']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedors')->onDelete('set null');
            $table->foreignId('chat_contact_id')->nullable()->constrained('chat_contacts')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('source', 50);
            $table->string('status', 20)->default('novo');
            $table->json('meta')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('page_url')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'source']);
            $table->index(['vendedor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_inbound_logs');
    }
};