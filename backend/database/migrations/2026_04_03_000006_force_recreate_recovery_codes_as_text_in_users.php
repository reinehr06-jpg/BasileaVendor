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
        Schema::table('users', function (Blueprint $table) {
            // Drop and recreate to guarantee it handles it without change() which might fail silently
            if (Schema::hasColumn('users', 'recovery_codes')) {
                $table->dropColumn('recovery_codes');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('recovery_codes')->nullable()->after('two_factor_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'recovery_codes')) {
                $table->dropColumn('recovery_codes');
            }
            $table->string('recovery_codes', 255)->nullable()->after('two_factor_enabled');
        });
    }
};
