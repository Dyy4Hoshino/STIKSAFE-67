<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 16)->default('user')->after('password');
            $table->string('status', 24)->default('active')->after('role');
            $table->string('status_reason', 255)->nullable()->after('status');
            $table->text('two_factor_secret')->nullable()->after('status_reason');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_confirmed_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->timestamp('deletion_requested_at')->nullable()->after('last_login_ip');
            $table->timestamp('deletion_scheduled_at')->nullable()->after('deletion_requested_at');
            $table->softDeletes();
            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_status_index');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'role',
                'status',
                'status_reason',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'last_login_at',
                'last_login_ip',
                'deletion_requested_at',
                'deletion_scheduled_at',
            ]);
        });
    }
};
