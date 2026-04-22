<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notificacoes')) {
            Schema::create('notificacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('tipo'); // renovacao_anual, venda_aprovacao, etc
                $table->string('titulo');
                $table->text('mensagem');
                $table->json('dados')->nullable(); // Dados extras (venda_id, etc)
                $table->boolean('lida')->default(false);
                $table->timestamp('lida_em')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'lida', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
