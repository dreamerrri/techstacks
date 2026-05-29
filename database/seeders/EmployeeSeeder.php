<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin@gmail.com')->value('id');
        $hrId    = DB::table('users')->where('email', 'hr@gmail.com')->value('id');

        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Carlos', 'Liza', 'Mark', 'Claire', 'Ryan', 'Jenna',
                       'Miguel', 'Sofia', 'Paolo', 'Iris', 'Kevin', 'Carla', 'Jason', 'Nina', 'Leo', 'Grace',
                       'Nico', 'Hazel', 'Renz', 'Lovely', 'Erwin', 'Trisha', 'Jayson', 'Pamela', 'Dennis', 'Rachel'];

        $middleNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Lopez', 'Ramos', 'Flores', 'Torres', 'Villanueva', 'Mendoza'];

        $lastNames = ['dela Cruz', 'Reyes', 'Garcia', 'Santos', 'Mendoza', 'Torres', 'Flores', 'Ramos',
                      'Villanueva', 'Lopez', 'Bautista', 'Castillo', 'Aquino', 'Navarro', 'Rivera'];

        $departments = ['IT', 'Finance', 'Human Resources', 'Operations', 'Marketing', 'Accounting', 'Admin'];

        $positions = ['Software Developer', 'Accounting Staff', 'HR Specialist', 'Operations Analyst',
                      'Marketing Officer', 'IT Support', 'Admin Assistant', 'Finance Officer', 'Data Analyst', 'Team Lead'];

        $employmentStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];

        $salaryTypes = ['Monthly', 'Daily', 'Hourly'];

        $civilStatuses = ['Single', 'Married', 'Widowed', 'Separated'];

        $genders = ['Male', 'Female'];

        $cities = ['Manila', 'Quezon City', 'Makati City', 'Pasig City', 'Taguig', 'Mandaluyong', 'Caloocan', 'Parañaque'];

        $streets = ['Rizal Street', 'Mabini Avenue', 'Bonifacio Road', 'Luna Street', 'del Pilar Avenue',
                    'Aguinaldo Highway', 'Quezon Avenue', 'España Boulevard'];

        $employees = [];

        // Admin employee record
        $employees[] = [
            'user_id'           => $adminId,
            'employee_id'       => 'EMP-0001',
            'first_name'        => 'Admin',
            'middle_name'       => null,
            'last_name'         => 'User',
            'birthdate'         => '1990-01-01',
            'gender'            => 'Male',
            'civil_status'      => 'Single',
            'address'           => '123 Admin Street, Manila',
            'contact_number'    => '09000000001',
            'email'             => 'admin@gmail.com',
            'department'        => 'Administration',
            'position'          => 'System Administrator',
            'employment_status' => 'Regular',
            'date_hired'        => '2020-01-01',
            'salary_type'       => 'Monthly',
            'basic_salary'      => 50000.00,
            'sss_number'        => null,
            'philhealth_number' => null,
            'pagibig_number'    => null,
            'tin_number'        => null,
            'is_archived'       => false,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        // HR employee record
        $employees[] = [
            'user_id'           => $hrId,
            'employee_id'       => 'EMP-0002',
            'first_name'        => 'HR',
            'middle_name'       => null,
            'last_name'         => 'Manager',
            'birthdate'         => '1992-06-15',
            'gender'            => 'Female',
            'civil_status'      => 'Single',
            'address'           => '456 HR Avenue, Quezon City',
            'contact_number'    => '09000000002',
            'email'             => 'hr@gmail.com',
            'department'        => 'Human Resources',
            'position'          => 'HR Manager',
            'employment_status' => 'Regular',
            'date_hired'        => '2021-03-15',
            'salary_type'       => 'Monthly',
            'basic_salary'      => 45000.00,
            'sss_number'        => null,
            'philhealth_number' => null,
            'pagibig_number'    => null,
            'tin_number'        => null,
            'is_archived'       => false,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        // 30 random employees
        for ($i = 3; $i <= 32; $i++) {
            $num       = str_pad($i, 4, '0', STR_PAD_LEFT);
            $firstName = $firstNames[$i - 3];
            $lastName  = $lastNames[array_rand($lastNames)];
            $gender    = $genders[array_rand($genders)];
            $city      = $cities[array_rand($cities)];
            $street    = $streets[array_rand($streets)];
            $salary    = rand(18000, 80000);

            $employees[] = [
                'user_id'           => null,
                'employee_id'       => "EMP-{$num}",
                'first_name'        => $firstName,
                'middle_name'       => $middleNames[array_rand($middleNames)],
                'last_name'         => $lastName,
                'birthdate'         => Carbon::create(rand(1985, 2000), rand(1, 12), rand(1, 28))->toDateString(),
                'gender'            => $gender,
                'civil_status'      => $civilStatuses[array_rand($civilStatuses)],
                'address'           => rand(100, 999) . ' ' . $street . ', ' . $city,
                'contact_number'    => '09' . rand(100000000, 999999999),
                'email'             => strtolower($firstName) . '.' . strtolower(str_replace(' ', '', $lastName)) . $i . '@company.com',
                'department'        => $departments[array_rand($departments)],
                'position'          => $positions[array_rand($positions)],
                'employment_status' => $employmentStatuses[array_rand($employmentStatuses)],
                'date_hired'        => Carbon::create(rand(2018, 2024), rand(1, 12), rand(1, 28))->toDateString(),
                'salary_type'       => $salaryTypes[array_rand($salaryTypes)],
                'basic_salary'      => $salary . '.00',
                'sss_number'        => rand(10, 99) . '-' . rand(1000000, 9999999) . '-' . rand(0, 9),
                'philhealth_number' => '12-' . rand(100000000, 999999999) . '-' . rand(0, 9),
                'pagibig_number'    => rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'tin_number'        => rand(100, 999) . '-' . rand(100, 999) . '-' . rand(100, 999) . '-000',
                'is_archived'       => false,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ];
        }

        DB::table('employees')->insert($employees);
    }
}