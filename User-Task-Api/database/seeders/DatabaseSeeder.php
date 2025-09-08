<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Test regular user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'), // optional, secure password
        ]);

        // Default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'), // secure password
            'role' => 'admin',
        ]);
    }
}
