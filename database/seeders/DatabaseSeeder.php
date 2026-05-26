<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@company.com',
        ]);

        // Create HR Users
        User::factory()->hr()->create([
            'name' => 'HR Manager',
            'email' => 'hr@company.com',
        ]);

        User::factory()->hr()->create([
            'name' => 'HR Specialist',
            'email' => 'hrspecialist@company.com',
        ]);

        // Create Regular Employees
        User::factory()->employee()->create([
            'name' => 'John Doe',
            'email' => 'john@company.com',
        ]);

        User::factory()->employee()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@company.com',
        ]);

        // Create additional random employees
        User::factory(20)->employee()->create();

        // Create an inactive user for testing
        User::factory()->employee()->inactive()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@company.com',
        ]);
    }
}
