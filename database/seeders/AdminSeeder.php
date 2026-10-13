<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Admin accounts are created here only; they cannot self-register.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@kalaluang.test'],
            [
                'name' => 'Admin KalaLuang',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );
    }
}
