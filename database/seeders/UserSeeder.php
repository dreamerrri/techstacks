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
        DB::table('users')->insert([
            [
                'name'              => 'Admin User',
                'email'             => 'admin@gmail.com',
                'role'              => 'admin',
                'is_active'         => true,
                'last_login_at'     => null,
                'email_verified_at' => Carbon::now(),
                'password'          => Hash::make('admin1234'),
                'remember_token'    => null,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ],
            [
                'name'              => 'HR Manager',
                'email'             => 'hr@gmail.com',
                'role'              => 'hr',
                'is_active'         => true,
                'last_login_at'     => null,
                'email_verified_at' => Carbon::now(),
                'password'          => Hash::make('hr1234'),
                'remember_token'    => null,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ],
        ]);
    }
}