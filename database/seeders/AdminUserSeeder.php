<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public const EMAIL = 'admin@arzen.com';

    public const INITIAL_PASSWORD = 'ArzenAdmin.2026';

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Administrador Arzen',
                'password' => Hash::make(self::INITIAL_PASSWORD),
                'must_change_password' => true,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );
    }
}
