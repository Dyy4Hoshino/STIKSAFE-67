<?php

use App\Enums\ScanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->string('original_filename', 255);
            $table->string('extension', 16);
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);

            $table->string('storage_disk', 32)->default('private');
            $table->string('stored_path', 255)->nullable();

            $table->string('status', 16)->default(ScanStatus::Queued->value);
            $table->string('result_label', 16)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('threat_name', 255)->nullable();
            $table->string('threat_category', 64)->nullable();
            $table->string('model_version', 64)->nullable();

            $table->uuid('ml_request_id')->nullable()->unique();
            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 500)->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(0);

            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('file_purged_at')->nullable();
            $table->timestamp('file_expires_at')->nullable();
            $table->timestamp('user_deleted_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'result_label']);
            $table->index(['status', 'created_at']);
            $table->index('sha256');
            $table->index('file_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};