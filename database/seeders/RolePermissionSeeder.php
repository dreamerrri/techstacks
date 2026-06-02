<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Admin gets all permissions
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all()->pluck('id')->toArray();
            $adminRole->syncPermissions($allPermissions);
        }

        // HR gets employee, payroll, attendance, and government contributions permissions
        $hrRole = Role::where('slug', 'hr')->first();
        if ($hrRole) {
            $hrPermissions = Permission::whereIn('slug', [
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
            ])->pluck('id')->toArray();
            $hrRole->syncPermissions($hrPermissions);
        }

        // Employee gets limited permissions for own data
        $employeeRole = Role::where('slug', 'employee')->first();
        if ($employeeRole) {
            $employeePermissions = Permission::whereIn('slug', [
                'view.own.profile',
                'edit.own.profile',
                'view.own.payslip',
                'view.own.attendance',
            ])->pluck('id')->toArray();
            $employeeRole->syncPermissions($employeePermissions);
        }
    }
}
