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
        if (Schema::hasTable('terms_acceptances')) {
            Schema::table('terms_acceptances', function (Blueprint $table) {
                if (!Schema::hasColumn('terms_acceptances', 'user_agent')) {
                    $table->string('user_agent')->nullable();
                }
                if (!Schema::hasColumn('terms_acceptances', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms_acceptances', function (Blueprint $table) {
            $table->dropColumn(['user_agent', 'ip_address']);
        });
    }
};
