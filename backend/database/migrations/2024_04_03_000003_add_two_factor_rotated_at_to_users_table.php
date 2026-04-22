<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_rotated_at')) {
                $table->timestamp('two_factor_rotated_at')->nullable()->after('recovery_codes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_rotated_at')) {
                $table->dropColumn('two_factor_rotated_at');
            }
        });
    }
};
