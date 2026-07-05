<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's admin user.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'firstName' => 'Vallerian',
                'lastName' => 'Mchau',
                'role' => UserRole::ADMIN->value,
                'status' => UserStatus::ACTIVE->value,
                'phone' => '0796861122',
                'password' => '1234567890',
            ]
        );
    }
}
