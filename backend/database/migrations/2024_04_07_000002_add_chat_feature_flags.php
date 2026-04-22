<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(false)->after('value');
            $table->string('chat_sla_minutes')->default('60')->after('chat_enabled');
            $table->boolean('chat_round_robin_enabled')->default(true)->after('chat_sla_minutes');
        });

        Schema::table('vendedores', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(true)->after('meta_mensal');
        });
    }

    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn('chat_enabled');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['chat_enabled', 'chat_sla_minutes', 'chat_round_robin_enabled']);
        });
    }
};