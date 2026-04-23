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
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            $table->json('multi_asaas_ids')->nullable()->after('asaas_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('legacy_customer_imports', function (Blueprint $table) {
            $table->dropColumn('multi_asaas_ids');
        });
    }
};
