<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_outbound_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('service', ['basileia_vendas', 'site', 'eventos', 'other'])->default('site');
            $table->json('events')->default('[]');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('retry_count')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_outbound_configs');
    }
};
