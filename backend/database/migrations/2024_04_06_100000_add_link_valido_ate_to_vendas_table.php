<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->timestamp('link_valido_ate')->nullable()->after('checkout_payment_link');
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn('link_valido_ate');
        });
    }
};
