<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'johnchristianleoncio0@gmail.com',
            'password' => bcrypt('admin123'), // Password: admin123
            'mobile' => '09123456789',
            'role' => 'admin',
            'position' => 'Administrator',
            'assigned_area' => 'Malabon',
            'status' => 'active',
        ]);
    }
}
