<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table): void {
            $table->json('reason_codes')->nullable()->after('model_version');
            $table->json('analysis')->nullable()->after('reason_codes');
            $table->string('preprocessing_version', 64)->nullable()->after('analysis');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table): void {
            $table->dropColumn(['reason_codes', 'analysis', 'preprocessing_version']);
        });
    }
};
