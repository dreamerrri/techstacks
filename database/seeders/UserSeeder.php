<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Admin User',
                'email'             => 'admin@company.com',
                'role'              => 'admin',
                'is_active'         => true,
                'last_login_at'     => null,
                'email_verified_at' => Carbon::now(),
                'password'          => Hash::make('password123'),
                'remember_token'    => null,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ],
            [
                'name'              => 'HR Manager',
                'email'             => 'hr@company.com',
                'role'              => 'hr',
                'is_active'         => true,
                'last_login_at'     => null,
                'email_verified_at' => Carbon::now(),
                'password'          => Hash::make('hrtechstacks123'),
                'remember_token'    => null,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']], // unique key to match on
                $user                         // values to insert/update
            );
        }
    }
}