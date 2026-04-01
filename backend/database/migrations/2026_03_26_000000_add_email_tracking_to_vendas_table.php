<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->boolean('email_vendedor_enviado')->default(false)->after('asaas_subscription_id');
            $table->boolean('email_cliente_enviado')->default(false)->after('email_vendedor_enviado');
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['email_vendedor_enviado', 'email_cliente_enviado']);
        });
    }
};