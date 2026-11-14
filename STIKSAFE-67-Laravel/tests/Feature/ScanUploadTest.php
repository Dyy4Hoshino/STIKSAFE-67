<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_valid_file(): void
    {
        Storage::fake('private');
        Queue::fake();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/scans', [
                'file' => $file,
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'filename',
                    'file' => ['size_bytes', 'extension', 'mime_type', 'available'],
                    'status',
                    'timestamps',
                ],
            ]);

        $this->assertDatabaseHas('scans', [
            'user_id' => $user->id,
            'original_filename' => 'document.pdf',
            'status' => 'queued',
        ]);

        $this->assertDatabaseHas('scan_attempts', [
            'attempt_number' => 1,
            'status' => 'running',
        ]);
    }
}