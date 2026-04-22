<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vendas', 'checkout_transaction_uuid')) {
            Schema::table('vendas', function (Blueprint $table) {
                $table->string('checkout_transaction_uuid', 36)->nullable()->after('checkout_hash')->comment('UUID da transação no Checkout');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn('checkout_transaction_uuid');
        });
    }
};
