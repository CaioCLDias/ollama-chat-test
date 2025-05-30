<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@buzzvel.com'],
            [
                'name' => 'Admin',
                'password' => '123456',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
