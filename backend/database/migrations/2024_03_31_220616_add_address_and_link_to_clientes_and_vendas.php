<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'cep')) {
                $table->string('cep')->nullable()->after('contato');
                $table->string('endereco')->nullable()->after('cep');
                $table->string('numero')->nullable()->after('endereco');
                $table->string('complemento')->nullable()->after('numero');
                $table->string('bairro')->nullable()->after('complemento');
                $table->string('cidade')->nullable()->after('bairro');
                $table->string('estado', 2)->nullable()->after('cidade');
            }
        });

        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'checkout_payment_link')) {
                $table->string('checkout_payment_link', 500)->nullable()->after('checkout_transaction_uuid')->comment('Link gerado pelo SEU CHECKOUT externo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado'
            ]);
        });
        
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn('checkout_payment_link');
        });
    }
};
