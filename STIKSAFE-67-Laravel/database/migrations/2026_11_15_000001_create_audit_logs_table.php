<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('occurred_at')->useCurrent();
            $table->string('actor_type', 16)->default('user'); // user, admin, system, service
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->string('target_type', 32)->nullable();
            $table->string('target_id', 64)->nullable();
            $table->string('outcome', 16)->default('success'); // success, failure
            $table->string('severity', 16)->default('info'); // info, warn, critical
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->json('metadata')->nullable();

            $table->index(['actor_user_id', 'occurred_at']);
            $table->index(['action', 'occurred_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
