<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero'); // COMP-2024-001
            $table->string('solicitante')->nullable();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->string('status')->default('Rascunho'); // Rascunho, Em cotação, Aguardando aprovação, Aprovada, Pedido enviado, Recebida
            $table->date('data_solicitacao')->nullable();
            $table->timestamps();

            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras');
    }
};
