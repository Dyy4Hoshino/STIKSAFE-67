<?php

namespace Database\Factories;

use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScanFactory extends Factory
{
    protected $model = Scan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_filename' => $this->faker->word().'.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 102450,
            'sha256' => hash('sha256', (string) $this->faker->unique()->word()),
            'storage_disk' => 'private',
            'stored_path' => 'quarantine/2026/11/mock.pdf',
            'status' => ScanStatus::Completed,
            'result_label' => 'safe',
            'confidence' => 0.9850,
            'queued_at' => now(),
            'started_at' => now(),
            'completed_at' => now(),
        ];
    }
}
