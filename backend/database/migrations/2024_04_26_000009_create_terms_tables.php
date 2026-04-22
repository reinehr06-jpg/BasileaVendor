<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_documents', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('titulo');
            $table->string('versao');
            $table->longText('conteudo_html');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('terms_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('terms_document_id')->constrained('terms_documents')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('aceito_em')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'terms_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_acceptances');
        Schema::dropIfExists('terms_documents');
    }
};