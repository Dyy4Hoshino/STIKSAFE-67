<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'userstiksafe@stiksafe.local'],
            [
                'name' => 'User STIKSAFE',
                'password' => Hash::make('UserStikSafe2026!'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
    }
}
