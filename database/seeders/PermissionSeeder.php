<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'view.users', 'module' => 'users', 'description' => 'View all users'],
            ['name' => 'Create Users', 'slug' => 'create.users', 'module' => 'users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit.users', 'module' => 'users', 'description' => 'Edit user information'],
            ['name' => 'Delete Users', 'slug' => 'delete.users', 'module' => 'users', 'description' => 'Delete users'],
            ['name' => 'Manage User Roles', 'slug' => 'manage.user.roles', 'module' => 'users', 'description' => 'Assign and manage user roles'],

            // Employee Management
            ['name' => 'View Employees', 'slug' => 'view.employees', 'module' => 'employees', 'description' => 'View all employees'],
            ['name' => 'Create Employees', 'slug' => 'create.employees', 'module' => 'employees', 'description' => 'Create new employee records'],
            ['name' => 'Edit Employees', 'slug' => 'edit.employees', 'module' => 'employees', 'description' => 'Edit employee information'],
            ['name' => 'Delete Employees', 'slug' => 'delete.employees', 'module' => 'employees', 'description' => 'Delete employee records'],
            ['name' => 'Archive Employees', 'slug' => 'archive.employees', 'module' => 'employees', 'description' => 'Archive employee records'],
            ['name' => 'View Own Profile', 'slug' => 'view.own.profile', 'module' => 'employees', 'description' => 'View own employee profile'],
            ['name' => 'Edit Own Profile', 'slug' => 'edit.own.profile', 'module' => 'employees', 'description' => 'Edit own employee profile'],

            // Payroll Management
            ['name' => 'View Payroll', 'slug' => 'view.payroll', 'module' => 'payroll', 'description' => 'View payroll information'],
            ['name' => 'Compute Payroll', 'slug' => 'compute.payroll', 'module' => 'payroll', 'description' => 'Compute payroll'],
            ['name' => 'Finalize Payroll', 'slug' => 'finalize.payroll', 'module' => 'payroll', 'description' => 'Finalize payroll periods'],
            ['name' => 'View Own Payslip', 'slug' => 'view.own.payslip', 'module' => 'payroll', 'description' => 'View own payslip'],

            // Payroll Period Management
            ['name' => 'Manage Payroll Periods', 'slug' => 'manage.payroll.periods', 'module' => 'payroll.periods', 'description' => 'Create and manage payroll periods'],

            // Attendance Management
            ['name' => 'View Attendance', 'slug' => 'view.attendance', 'module' => 'attendance', 'description' => 'View attendance records'],
            ['name' => 'Edit Attendance', 'slug' => 'edit.attendance', 'module' => 'attendance', 'description' => 'Edit attendance records'],
            ['name' => 'View Own Attendance', 'slug' => 'view.own.attendance', 'module' => 'attendance', 'description' => 'View own attendance'],

            // Government Contributions
            ['name' => 'View Government Contributions', 'slug' => 'view.gov.contributions', 'module' => 'government.contributions', 'description' => 'View government contribution records'],
            ['name' => 'Edit Government Contributions', 'slug' => 'edit.gov.contributions', 'module' => 'government.contributions', 'description' => 'Edit government contribution records'],

            // Allowance Management
            ['name' => 'Manage Allowances', 'slug' => 'manage.allowances', 'module' => 'allowances', 'description' => 'Create and manage employee allowances'],

            // Benefit Management
            ['name' => 'Manage Benefits', 'slug' => 'manage.benefits', 'module' => 'benefits', 'description' => 'Create and manage employee benefits'],

            // Role Management
            ['name' => 'Manage Roles', 'slug' => 'manage.roles', 'module' => 'roles', 'description' => 'Create, edit, and delete roles'],
            ['name' => 'Assign Roles', 'slug' => 'assign.roles', 'module' => 'roles', 'description' => 'Assign roles to users'],

            // Permission Management
            ['name' => 'Manage Permissions', 'slug' => 'manage.permissions', 'module' => 'permissions', 'description' => 'Create, edit, and delete permissions'],
            ['name' => 'Assign Permissions', 'slug' => 'assign.permissions', 'module' => 'permissions', 'description' => 'Assign permissions to roles'],

            // Audit Logs
            ['name' => 'View Audit Logs', 'slug' => 'view.audit.logs', 'module' => 'audit.logs', 'description' => 'View system audit logs'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'view.reports', 'module' => 'reports', 'description' => 'View system reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
