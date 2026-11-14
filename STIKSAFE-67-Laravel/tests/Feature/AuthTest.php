<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Dedy Bhaskara',
            'email' => 'dedy@stiksafe.test',
            'password' => 'StikSafe#Secure2026!XyZ',
            'password_confirmation' => 'StikSafe#Secure2026!XyZ',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'status'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'dedy@stiksafe.test',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'dedy@stiksafe.test',
            'password' => bcrypt('StikSafe#Secure2026!XyZ'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'dedy@stiksafe.test',
            'password' => 'StikSafe#Secure2026!XyZ',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                ],
            ]);
    }
}