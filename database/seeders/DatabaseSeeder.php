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
        // Create Admin User with Employee record
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $admin->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@company.com',
        ]);

        // Create HR Users with Employee records
        $hrManager = User::factory()->hr()->create([
            'name' => 'HR Manager',
            'email' => 'hr@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $hrManager->id,
            'first_name' => 'HR',
            'last_name' => 'Manager',
            'email' => 'hr@company.com',
            'department' => 'HR',
            'position' => 'HR Manager',
        ]);

        $hrSpecialist = User::factory()->hr()->create([
            'name' => 'HR Specialist',
            'email' => 'hrspecialist@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $hrSpecialist->id,
            'first_name' => 'HR',
            'last_name' => 'Specialist',
            'email' => 'hrspecialist@company.com',
            'department' => 'HR',
            'position' => 'HR Specialist',
        ]);

        // Create Regular Employees with Employee records
        $john = User::factory()->employee()->create([
            'name' => 'John Doe',
            'email' => 'john@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $john->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@company.com',
        ]);

        $jane = User::factory()->employee()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $jane->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@company.com',
        ]);

        // Create additional random employees with employee records
        $employees = User::factory(20)->employee()->create();
        foreach ($employees as $employee) {
            \App\Models\Employee::factory()->create([
                'user_id' => $employee->id,
                'email' => $employee->email,
            ]);
        }

        // Create an inactive user for testing
        $inactive = User::factory()->employee()->inactive()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@company.com',
        ]);
        \App\Models\Employee::factory()->create([
            'user_id' => $inactive->id,
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'email' => 'inactive@company.com',
        ]);
    }
}
