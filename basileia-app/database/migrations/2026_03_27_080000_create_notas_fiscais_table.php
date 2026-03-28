<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->string('descricao');
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('arquivo_path');
            $table->string('tipo', 20)->default('pagamento'); // pagamento, comissao
            $table->integer('mes_referencia')->nullable(); // Ym
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
