<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->boolean('lead_enabled')->default(true)->after('chat_enabled');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('lead_round_robin_enabled')->default(true)->after('chat_round_robin_enabled');
            $table->integer('lead_default_equipe_id')->nullable()->after('lead_round_robin_enabled');
            $table->integer('lead_rate_limit')->default(10)->after('lead_default_equipe_id');
            $table->integer('lead_rate_window')->default(3600)->after('lead_rate_limit');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['lead_round_robin_enabled', 'lead_default_equipe_id', 'lead_rate_limit', 'lead_rate_window']);
        });

        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn('lead_enabled');
        });
    }
};