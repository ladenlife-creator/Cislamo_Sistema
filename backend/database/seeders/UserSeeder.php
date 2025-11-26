<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'identifier' => 'admin@erpcislamo.com',
                'type' => 'email',
                'user_type' => 'admin',
                'password' => Hash::make('123456789'),
                'verified_at' => now(),
                'role_id' => null,
                'is_active' => true,
                'phone' => '+1234567890',
                'settings' => json_encode(['theme' => 'dark', 'notifications' => true])
            ],
            [
                'name' => 'João Silva',
                'identifier' => 'joao.silva@erpcislamo.com',
                'type' => 'email',
                'user_type' => 'staff',
                'password' => Hash::make('123456789'),
                'verified_at' => now(),
                'role_id' => null,
                'is_active' => true,
                'phone' => '+1234567891',
                'settings' => json_encode(['theme' => 'light', 'notifications' => true])
            ],
            [
                'name' => 'Maria Santos',
                'identifier' => 'maria.santos@erpcislamo.com',
                'type' => 'email',
                'user_type' => 'staff',
                'password' => Hash::make('123456789'),
                'verified_at' => now(),
                'role_id' => null,
                'is_active' => true,
                'phone' => '+1234567892',
                'settings' => json_encode(['theme' => 'light', 'notifications' => false])
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
