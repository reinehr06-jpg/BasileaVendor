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
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'credit_card_token')) {
                $table->string('credit_card_token')->nullable()->after('email');
            }
            if (!Schema::hasColumn('clientes', 'card_brand')) {
                $table->string('card_brand')->nullable()->after('credit_card_token');
            }
            if (!Schema::hasColumn('clientes', 'card_last_digits')) {
                $table->string('card_last_digits')->nullable()->after('card_brand');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['credit_card_token', 'card_brand', 'card_last_digits']);
        });
    }
};
