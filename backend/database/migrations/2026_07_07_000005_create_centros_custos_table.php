<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centros_custos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('responsavel')->nullable();
            $table->decimal('orcamento', 12, 2)->default(0);
            $table->string('status')->default('Ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centros_custos');
    }
};
