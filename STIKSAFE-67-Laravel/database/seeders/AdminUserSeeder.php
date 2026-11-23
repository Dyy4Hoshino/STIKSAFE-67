<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'adminstiksafe@stiksafe.local'],
            [
                'name' => 'adminstiksafe',
                'password' => Hash::make('TuDeYa22'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );
    }
}
