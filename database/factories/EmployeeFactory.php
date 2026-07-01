<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->optional()->firstName(),
            'birthdate' => fake()->date(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed', 'Separated']),
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'department' => fake()->randomElement(['Engineering', 'Marketing', 'Finance', 'HR', 'Operations']),
            'position' => fake()->jobTitle(),
            'employment_status' => fake()->randomElement(['Regular', 'Probationary', 'Contractual', 'Part-time']),
            'date_hired' => fake()->date(),
            'salary_type' => 'Monthly',
            'basic_salary' => fake()->numberBetween(15000, 50000),
            'sss_number' => fake()->optional()->numerify('##-#######-#'),
            'philhealth_number' => fake()->optional()->numerify('##-#########-#'),
            'pagibig_number' => fake()->optional()->numerify('###########'),
            'tin_number' => fake()->optional()->numerify('###-###-###-###'),
            'is_archived' => false,
        ];
    }
}
