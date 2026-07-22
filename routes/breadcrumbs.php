<?php // routes/breadcrumbs.php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

//Profile
Breadcrumbs::for('profile.show', function (BreadcrumbTrail $trail) {
  $trail->parent('dashboard');   //copy nalang pag needed in future also ang parent ay ung navigatable lang means di pwede and nasa dropdown parents sa sidebar, since walan silang routes, nor page, instead lagay sila sa trail push as label
    $trail->push('Profile', route('profile.show'));
});

//USERS
Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail) {
 $trail->parent('manage-users');
    $trail->push('Users', route('users.index'));
});

//PARENT BREADCRUMBS
Breadcrumbs::for('manage-users', function (BreadcrumbTrail $trail) {
    $trail->push('Manage Users');
});
Breadcrumbs::for('manage-employees', function (BreadcrumbTrail $trail) {
    $trail->push('Manage Employees');
}); 
Breadcrumbs::for('manage-payroll', function (BreadcrumbTrail $trail) {
    $trail->push('Manage Payroll');
}); 
Breadcrumbs::for('monitoring', function (BreadcrumbTrail $trail) {
    $trail->push('Monitoring');
}); 


//ROLES (each pages dapat seperate pala)
//SHOW
Breadcrumbs::for('roles.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-users');
    $trail->push('Roles', route('roles.index'));
});

//READ
Breadcrumbs::for('roles.show', function (BreadcrumbTrail $trail, $role) {
    $trail->parent('roles.index');
    $trail->push($role->name, route('roles.show', $role));
});

//ganito pag required ipakita kung sino ang ineedit or naka "show" na page, for example Admin
//UPDATE
Breadcrumbs::for('roles.edit', function (BreadcrumbTrail $trail, $role) {
    $trail->parent('roles.index');
    $trail->push('Edit ' . $role->name . ' role', route('roles.edit', $role));
});

//CREATE
Breadcrumbs::for('roles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('roles.index');
    $trail->push('Create Role', route('roles.create'));
});



//PERMISSIONS
Breadcrumbs::for('permissions.index', function (BreadcrumbTrail $trail) {
     $trail->parent('manage-users');
    $trail->push('Roles', route('permissions.index'));
});


Breadcrumbs::for('permissions.show', function (BreadcrumbTrail $trail, $permission) {
    $trail->parent('permissions.index');
    $trail->push($permission->name, route('permissions.show', $permission));
});


Breadcrumbs::for('permissions.edit', function (BreadcrumbTrail $trail, $permission) {
    $trail->parent('permissions.index');
    $trail->push('Edit ' . $permission->name .' permission', route('permissions.edit', $permission));
});

Breadcrumbs::for('permissions.create', function (BreadcrumbTrail $trail) {
    $trail->parent('permissions.index');
    $trail->push('Create Permission', route('permissions.create'));
});

//EMPLOYEES
Breadcrumbs::for('employees.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-employees');
    $trail->push('Employee List', route('employees.index'));
});


Breadcrumbs::for('employees.show', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('employees.index');
    $trail->push($employee->full_name, route('employees.show', $employee));
});


Breadcrumbs::for('employees.edit', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('employees.index');
    $trail->push("Edit {$employee->full_name}'s profile", route('employees.edit', $employee));
});

Breadcrumbs::for('employees.create', function (BreadcrumbTrail $trail) {
    $trail->parent('employees.index');
    $trail->push('Add Employees', route('employees.create'));
});


Breadcrumbs::for('employees.archived', function (BreadcrumbTrail $trail) {
    $trail->parent('employees.index');
    $trail->push('Archived Employees', route('employees.archived'));
});


//ATTENDANCE
Breadcrumbs::for('manual-payroll-attendance.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-employees');
    $trail->push('Attendance', route('manual-payroll-attendance.index'));
});
//why is this on payroll-periods? ask vincent
Breadcrumbs::for('payroll-periods.archived', function (BreadcrumbTrail $trail) {
    $trail->parent('manual-payroll-attendance.index');
    $trail->push('Archived Periods',route('payroll-periods.archived'));
});

Breadcrumbs::for('payroll-periods.create', function (BreadcrumbTrail $trail) {
    $trail->parent('manual-payroll-attendance.index');
    $trail->push('Create Payroll period',route('payroll-periods.create'));
});

Breadcrumbs::for('manual-payroll-attendance.period', function (BreadcrumbTrail $trail, $payrollPeriod) {
    $trail->parent('manual-payroll-attendance.index');
    $trail->push('Encode Attendance',route('manual-payroll-attendance.period', [$payrollPeriod]));
});

// yeah i dont know
Breadcrumbs::for('manual-payroll-attendance.employee-form', function (BreadcrumbTrail $trail, $payrollPeriod, $employee) {
    $trail->parent('manual-payroll-attendance.period', $payrollPeriod);
    $trail->push($employee->full_name, route('manual-payroll-attendance.employee-form', [$payrollPeriod, $employee]));
});



//WORK REQUESTS
Breadcrumbs::for('work-requests.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-employees');
    $trail->push('Work Requests', route('work-requests.index'));
});

Breadcrumbs::for('work-requests.show', function (BreadcrumbTrail $trail, $workRequest) {
    $trail->parent('work-requests.index');
    $trail->push('Work Request #'.$workRequest->id,route('work-requests.show', [$workRequest]));
});

Breadcrumbs::for('work-requests.pending', function (BreadcrumbTrail $trail) {
    $trail->parent('work-requests.index');
    $trail->push('Pending work requests', route('work-requests.pending'));
});


Breadcrumbs::for('work-requests.create', function (BreadcrumbTrail $trail) {
    $trail->parent('work-requests.index');
    $trail->push('New request', route('work-requests.create'));
});

Breadcrumbs::for('work-requests.edit', function (BreadcrumbTrail $trail, $workRequest) {
    $trail->parent('work-requests.index');
    $trail->push('Edit Request #' . $workRequest->id, route('work-requests.edit', $workRequest));
});

//PAYROLL
Breadcrumbs::for('payroll.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-payroll');
    $trail->push('Payroll', route('payroll.index'));
});

Breadcrumbs::for('payroll.show', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('payroll.index');
    $trail->push($employee->full_name, route('payroll.show', [$employee]));
});

Breadcrumbs::for('payroll.payslip', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('payroll.show', $employee);
    $trail->push('Payslip', route('payroll.payslip', [$employee]));
});


//GOV CONTRIBUTIONS
Breadcrumbs::for('government-contributions.index', function (BreadcrumbTrail $trail) {
    $trail->parent('manage-payroll');
    $trail->push('Government Contributions', route('government-contributions.index'));
});

Breadcrumbs::for('government-contributions.show', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('government-contributions.index');
    $trail->push($employee->full_name, route('government-contributions.show', [$employee]));
});

//AUDIT-LOGS
Breadcrumbs::for('audit-logs.index', function (BreadcrumbTrail $trail) {
    $trail->parent('monitoring');
    $trail->push('Audit Logs', route('audit-logs.index'));
});

Breadcrumbs::for('audit-logs.show', function (BreadcrumbTrail $trail, $auditLog) {
    $trail->parent('audit-logs.index');
    $trail->push('Event ID #'. $auditLog->id, route('audit-logs.show', [$auditLog]));
});


//EMPLOYEE ATTENDANCE(USER SIDE)
Breadcrumbs::for('employee-attendance.index', function (BreadcrumbTrail $trail) {
    $trail->push('Attendance');
    $trail->push('Attendance', route('employee-attendance.index'));
});

Breadcrumbs::for('employee-attendance.show-employee', function (BreadcrumbTrail $trail, $employee) {
    $trail->parent('employee-attendance.index');
$trail->push($employee->full_name . "'s Attendance", route('employee-attendance.show-employee', $employee));
});

Breadcrumbs::for('employee-attendance.create', function (BreadcrumbTrail $trail) {
    $trail->parent('employee-attendance.index');
    $trail->push('Add attendance', route('employee-attendance.create'));
});









