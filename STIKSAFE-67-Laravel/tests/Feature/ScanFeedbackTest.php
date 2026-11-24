<?php

namespace Tests\Feature;

use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScanFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('scan_feedback')) {
            Schema::create('scan_feedback', function (Blueprint $table): void {
                $table->id();
                $table->ulid('scan_id');
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('type', 20);
                $table->string('comment', 1000)->nullable();
                $table->string('status', 16)->default('open');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_user_can_submit_feedback_for_completed_scan(): void
    {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'user_id' => $user->id,
            'status' => ScanStatus::Completed,
        ]);

        $response = $this->actingAs($user)
            ->post("/scans/{$scan->id}/feedback", [
                'type' => 'false_positive',
                'comment' => 'File ini sebenarnya laporan PDF aman.',
            ]);

        $response->assertRedirect("/scans/{$scan->id}");
        $this->assertDatabaseHas('scan_feedback', [
            'scan_id' => $scan->id,
            'user_id' => $user->id,
            'type' => 'false_positive',
        ]);
    }

    public function test_api_user_can_submit_feedback_via_json(): void
    {
        $user = User::factory()->create();
        $scan = Scan::factory()->create([
            'user_id' => $user->id,
            'status' => ScanStatus::Completed,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/scans/{$scan->id}/feedback", [
                'type' => 'false_negative',
                'comment' => 'Ada malware tersembunyi.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'scan_id' => $scan->id,
                    'type' => 'false_negative',
                ],
            ]);
    }
}
