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
            // Security fields
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->string('recovery_codes')->nullable()->after('two_factor_enabled');
            $table->ipAddress('login_ip')->nullable()->after('recovery_codes');
            $table->timestamp('last_login_at')->nullable()->after('login_ip');
            $table->timestamp('failed_login_at')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('failed_login_at');
            $table->timestamp('account_locked_until')->nullable()->after('failed_login_attempts');
            $table->string('password_reset_token', 64)->unique()->nullable()->after('account_locked_until');
            $table->timestamp('password_reset_expires')->nullable()->after('password_reset_token');
            $table->boolean('require_password_change')->default(false)->after('password_reset_expires');
            
            // Admin specific security
            $table->json('allowed_ips')->nullable()->after('require_password_change'); // For ADM only
            $table->boolean('security_notifications')->default(true)->after('allowed_ips');
            $table->timestamp('security_scan_at')->nullable()->after('security_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_enabled',
                'recovery_codes',
                'login_ip',
                'last_login_at',
                'failed_login_at',
                'failed_login_attempts',
                'account_locked_until',
                'password_reset_token',
                'password_reset_expires',
                'require_password_change',
                'allowed_ips',
                'security_notifications',
                'security_scan_at'
            ]);
        });
    }
};
