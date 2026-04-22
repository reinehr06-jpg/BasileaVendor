<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('webhook_outbound_logs')) {
            Schema::create('webhook_outbound_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('webhook_config_id')->constrained('webhook_outbound_configs')->cascadeOnDelete();
                $table->string('event_type');
                $table->json('payload');
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->text('response_body')->nullable();
                $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'next_retry_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_outbound_logs');
    }
};
