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
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'billing_type')) {
                $table->string('billing_type')->default('UNDEFINED')->after('status');
            }
            if (!Schema::hasColumn('eventos', 'charge_type')) {
                $table->string('charge_type')->default('DETACHED')->after('billing_type');
            }
            if (!Schema::hasColumn('eventos', 'due_date_limit_days')) {
                $table->integer('due_date_limit_days')->nullable()->after('charge_type');
            }
            if (!Schema::hasColumn('eventos', 'notification_enabled')) {
                $table->boolean('notification_enabled')->default(true)->after('due_date_limit_days');
            }
            if (!Schema::hasColumn('eventos', 'is_address_required')) {
                $table->boolean('is_address_required')->default(false)->after('notification_enabled');
            }
            if (!Schema::hasColumn('eventos', 'max_allowed_usage')) {
                $table->integer('max_allowed_usage')->nullable()->after('is_address_required');
            }
            if (!Schema::hasColumn('eventos', 'end_date')) {
                $table->date('end_date')->nullable()->after('max_allowed_usage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn([
                'billing_type',
                'charge_type',
                'due_date_limit_days',
                'notification_enabled',
                'is_address_required',
                'max_allowed_usage',
                'end_date',
            ]);
        });
    }
};
