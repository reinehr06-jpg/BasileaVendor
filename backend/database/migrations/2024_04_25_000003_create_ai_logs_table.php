<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tarefa', 50);
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->unsignedSmallInteger('duracao_ms')->nullable();
            $table->boolean('sucesso')->default(true);
            $table->text('erro')->nullable();
            $table->timestamp('executado_em');

            $table->index(['tarefa', 'executado_em']);
            $table->index(['user_id', 'executado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};