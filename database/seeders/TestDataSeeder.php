<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $hrRole = Role::where('slug', 'hr')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // Create 1 HR user
        $hrUsers = [
            [
                'first_name' => 'HR',
                'last_name' => 'User',
                'email' => 'hr@techstacks.com',
                'password' => 'hrpassword123',
            ],
        ];

        foreach ($hrUsers as $hrData) {
            $fullName = $hrData['first_name'] . ' ' . $hrData['last_name'];
            $user = User::firstOrCreate(
                ['email' => $hrData['email']],
                [
                    'name' => $fullName,
                    'password' => Hash::make($hrData['password']),
                    'role' => 'hr',
                    'is_active' => true,
                ]
            );

            // Assign HR role in RBAC
            if ($hrRole && !$user->roles()->where('role_id', $hrRole->id)->exists()) {
                $user->roles()->attach($hrRole->id);
            }

            // Create corresponding employee record
            if (!$user->employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => 'EMP-' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT),
                    'first_name' => $hrData['first_name'],
                    'last_name' => $hrData['last_name'],
                    'birthdate' => '1990-05-15',
                    'gender' => 'Female',
                    'civil_status' => 'Single',
                    'address' => '123 Main St, City',
                    'contact_number' => '0917' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'email' => $hrData['email'],
                    'department' => 'Human Resources',
                    'position' => 'HR Manager',
                    'employment_status' => 'Regular',
                    'date_hired' => '2023-01-15',
                    'salary_type' => 'Monthly',
                    'basic_salary' => 75000.00,
                    'sss_number' => 'SS-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'philhealth_number' => 'PH-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'pagibig_number' => 'PG-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'tin_number' => 'TIN-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        // Create 30 employee users
        $departments = ['Engineering', 'Marketing', 'Sales', 'Finance', 'Operations'];
        $positions = ['Software Engineer', 'Marketing Specialist', 'Sales Representative', 'Financial Analyst', 'Operations Manager'];
        $firstNames = ['James', 'Emily', 'Robert', 'Jennifer', 'Michael', 'Sarah', 'David', 'Lisa', 'William', 'Maria', 'Richard', 'Patricia', 'Joseph', 'Linda', 'Thomas', 'Barbara', 'Charles', 'Susan', 'Christopher', 'Jessica', 'Daniel', 'Karen', 'Matthew', 'Nancy', 'Anthony', 'Betty', 'Mark', 'Helen', 'Steven', 'Dorothy'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Wilson', 'Anderson', 'Taylor', 'Thomas', 'Moore', 'Jackson', 'Martin', 'Lee', 'Thompson', 'White'];
        $genders = ['Male', 'Female'];
        $civilStatuses = ['Single', 'Married'];

        for ($i = 0; $i < 30; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $email = strtolower($firstName . '.' . $lastName . $i . '@techstacks.com');
            $department = $departments[array_rand($departments)];
            $position = $positions[array_rand($positions)];
            $gender = $genders[array_rand($genders)];
            $civilStatus = $civilStatuses[array_rand($civilStatuses)];

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $fullName,
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                    'is_active' => true,
                ]
            );

            // Assign employee role in RBAC
            if ($employeeRole && !$user->roles()->where('role_id', $employeeRole->id)->exists()) {
                $user->roles()->attach($employeeRole->id);
            }

            // Create corresponding employee record
            if (!$user->employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => 'EMP-' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'birthdate' => '1990-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                    'gender' => $gender,
                    'civil_status' => $civilStatus,
                    'address' => rand(100, 999) . ' Street Ave, City',
                    'contact_number' => '0917' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'email' => $email,
                    'department' => $department,
                    'position' => $position,
                    'employment_status' => 'Regular',
                    'date_hired' => '2023-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                    'salary_type' => 'Monthly',
                    'basic_salary' => rand(40000, 80000),
                    'sss_number' => 'SS-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'philhealth_number' => 'PH-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'pagibig_number' => 'PG-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'tin_number' => 'TIN-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
