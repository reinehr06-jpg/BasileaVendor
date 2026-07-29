<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens efêmeros de acesso ao painel Sysadmin.
 * Cada sessão do painel gera um token que vale 60 minutos. Depois disso ele
 * expira e qualquer tentativa de uso dispara alerta de segurança.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysadmin_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash', 64)->unique(); // sha256 do token (nunca guardamos o valor puro)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysadmin_tokens');
    }
};
