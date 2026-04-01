<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('church_user_id')->nullable()->after('asaas_customer_id');
            $table->timestamp('church_account_created_at')->nullable()->after('church_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['church_user_id', 'church_account_created_at']);
        });
    }
};
