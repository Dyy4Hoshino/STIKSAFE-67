<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('scan_id')->constrained('scans')->cascadeOnDelete();

            $table->unsignedSmallInteger('attempt_number');
            $table->string('trigger', 16);
            $table->string('status', 16)->default('running');

            $table->uuid('ml_request_id')->nullable();
            $table->string('worker_host', 64)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 500)->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->foreignId('triggered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['scan_id', 'attempt_number']);
            $table->unique('ml_request_id');
            $table->index(['scan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_attempts');
    }
};