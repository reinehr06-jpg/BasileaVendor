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
        if (Schema::hasTable('terms_documents')) {
            Schema::table('terms_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('terms_documents', 'conteudo_html')) {
                    $table->longText('conteudo_html')->nullable();
                }
                if (!Schema::hasColumn('terms_documents', 'tipo')) {
                    $table->string('tipo')->nullable();
                }
                if (!Schema::hasColumn('terms_documents', 'titulo')) {
                    $table->string('titulo')->nullable();
                }
                if (!Schema::hasColumn('terms_documents', 'versao')) {
                    $table->string('versao')->nullable();
                }
                if (!Schema::hasColumn('terms_documents', 'ativo')) {
                    $table->boolean('ativo')->default(true);
                }
            });
        } else {
            Schema::create('terms_documents', function (Blueprint $table) {
                $table->id();
                $table->string('tipo');
                $table->string('titulo');
                $table->string('versao');
                $table->longText('conteudo_html');
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não removemos para não perder dados acidentalmente
    }
};
