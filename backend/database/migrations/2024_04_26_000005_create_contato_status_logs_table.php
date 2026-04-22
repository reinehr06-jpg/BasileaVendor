<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contato_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contato_id')->constrained('contatos')->cascadeOnDelete();
            $table->string('status_anterior')->nullable();
            $table->string('status_novo');
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->text('motivo')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['contato_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contato_status_logs');
    }
};