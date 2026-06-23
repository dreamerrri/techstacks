<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

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

        // --- Force-link admin/hr to their RBAC roles and permissions ---
        // Direct, model-based safety net so this works regardless of
        // DB triggers, observers, or other seeders.
        $admin = User::where('email', 'admin@company.com')->first();
        $hr    = User::where('email', 'hr@company.com')->first();

        $adminRole = Role::where('slug', 'admin')->first();
        $hrRole    = Role::where('slug', 'hr')->first();

        if ($admin && $adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
            $adminRole->permissions()->sync(Permission::pluck('id')->toArray());
        }

        if ($hr && $hrRole) {
            $hr->roles()->syncWithoutDetaching([$hrRole->id]);

            $hrPermissionSlugs = [
                'view.employees',
                'create.employees',
                'edit.employees',
                'view.payroll',
                'compute.payroll',
                'manage.payroll.periods',
                'view.attendance',
                'edit.attendance',
                'view.gov.contributions',
                'edit.gov.contributions',
                'manage.allowances',
                'manage.benefits',
                'view.reports',
            ];

            $hrPermissionIds = Permission::whereIn('slug', $hrPermissionSlugs)->pluck('id')->toArray();
            $hrRole->permissions()->sync($hrPermissionIds);
        }
    }
}