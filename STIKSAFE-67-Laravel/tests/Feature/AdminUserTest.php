<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function createUser(): User
    {
        return User::factory()->create(['role' => 'user', 'status' => 'active']);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->createAdmin();
        User::factory()->count(3)->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_cannot_access_admin_routes(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/users')
            ->assertStatus(403);
    }

    public function test_admin_can_suspend_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$user->id}/status", [
                'status' => 'suspended',
                'reason' => 'Pelanggaran aturan.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'account.suspended',
            'target_id' => (string) $user->id,
        ]);
    }

    public function test_admin_cannot_suspend_self(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$admin->id}/status", [
                'status' => 'suspended',
                'reason' => 'test',
            ])
            ->assertStatus(403);
    }

    public function test_admin_cannot_demote_last_active_admin(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$admin->id}/role", [
                'role' => 'user',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_change_role_with_multiple_admins(): void
    {
        $admin1 = $this->createAdmin();
        $admin2 = $this->createAdmin();

        $this->actingAs($admin1, 'sanctum')
            ->patchJson("/api/v1/admin/users/{$admin2->id}/role", [
                'role' => 'user',
            ])
            ->assertOk()
            ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'account.role.changed',
            'target_id' => (string) $admin2->id,
            'severity' => 'critical',
        ]);
    }

    public function test_user_data_not_accessible_cross_account(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $scan = $user1->scans()->create([
            'original_filename' => 'secret.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'sha256' => hash('sha256', 'test'),
            'storage_disk' => 'private',
            'stored_path' => 'quarantine/test/file',
            'status' => 'completed',
            'result_label' => 'safe',
            'confidence' => 0.99,
            'model_version' => 'mock-1.0',
            'attempts_count' => 1,
            'queued_at' => now(),
            'completed_at' => now(),
        ]);

        $this->actingAs($user2, 'sanctum')
            ->getJson("/api/v1/scans/{$scan->id}")
            ->assertStatus(404);
    }
}
