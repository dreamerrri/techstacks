<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollInputController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\ManualPayrollAttendanceController;
use App\Http\Controllers\GovernmentContributionsController;
use App\Http\Controllers\EmployeeAttendanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\NotificationController;

Route::get('/test', function () {
    return 'ok';
});
// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login']);

    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // All roles can reach /dashboard; the controller scopes data per role.
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Employee Attendance - All authenticated employees can manage their own attendance
    Route::prefix('employee-attendance')->name('employee-attendance.')->group(function () {
        Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeAttendanceController::class, 'create'])->name('create');
        Route::post('/', [EmployeeAttendanceController::class, 'store'])->name('store');
        Route::delete('/{attendance}', [EmployeeAttendanceController::class, 'destroy'])->name('destroy');
        Route::post('/compute-period', [EmployeeAttendanceController::class, 'getPeriodSummary'])->name('compute-period');
        
        // HR/Admin can view specific employee's attendance records
        Route::middleware('permission:view.employees')->group(function () {
            Route::get('/employee/{employee}', [EmployeeAttendanceController::class, 'showEmployee'])->name('show-employee');
        });
    });

Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal');
    Route::middleware('permission:view.users')->prefix('users')->name('users.')->group(function () {
        Route::get('/',                [UserController::class, 'index'])->name('index');
        Route::patch('/{user}/toggle', [UserController::class, 'toggleActive'])->name('toggle')->middleware('permission:edit.users');
        Route::patch('/{user}/role',   [UserController::class, 'updateRole'])->name('role')->middleware('permission:manage.user.roles');
    });

    Route::get('/profile',              [ProfileController::class, 'show'])->name('profile.show');
Route::put('/profile',              [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/photo',       [ProfileController::class, 'updatePhoto'])->name('profile.photo');
Route::put('/profile/banner-color', [ProfileController::class, 'updateBannerColor'])->name('profile.banner-color');

    // Employee Management — admin and HR only.
    Route::middleware('permission:view.employees')->prefix('employees')->name('employees.')->group(function () {

        // ── Static routes FIRST (must come before the /{employee} wildcard) ──
        Route::get('/',         [EmployeeController::class, 'index'])->name('index');
        Route::get('/archived', [EmployeeController::class, 'archived'])->name('archived');

        // Create & Store — admin and HR
        Route::middleware('permission:create.employees')->group(function () {
            Route::get('/create', [EmployeeController::class, 'create'])->name('create');
            Route::post('/',      [EmployeeController::class, 'store'])->name('store');
        });

        // ── Wildcard routes AFTER static ones ──
        Route::get('/{employee}',      [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit')->middleware('permission:edit.employees');
        Route::put('/{employee}',      [EmployeeController::class, 'update'])->name('update')->middleware('permission:edit.employees');

        // Archive & Restore — admin only
        Route::middleware('permission:archive.employees')->group(function () {
            Route::patch('/{employee}/archive', [EmployeeController::class, 'archive'])->name('archive');
            Route::patch('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');
        });

        // Government Contributions — admin and HR only
        Route::middleware('permission:edit.gov.contributions')->group(function () {
            Route::patch('/{employee}/update-gov-contributions', [EmployeeController::class, 'updateGovContributions'])->name('update-gov-contributions');
        });
    });


    // Allowance Management — admin and HR only
    Route::middleware('permission:manage.allowances')->prefix('employees/{employee}/allowances')->name('allowances.')->group(function () {
        Route::post('/', [AllowanceController::class, 'store'])->name('store');
        Route::put('/{allowance}', [AllowanceController::class, 'update'])->name('update');
        Route::delete('/{allowance}', [AllowanceController::class, 'destroy'])->name('destroy');
    });

    // Benefit Management — admin and HR only
    Route::middleware('permission:manage.benefits')->prefix('employees/{employee}/benefits')->name('benefits.')->group(function () {
        Route::post('/', [BenefitController::class, 'store'])->name('store');
        Route::put('/{benefit}', [BenefitController::class, 'update'])->name('update');
        Route::delete('/{benefit}', [BenefitController::class, 'destroy'])->name('destroy');
    });

    // Payroll Management — all authenticated users can access
    // Admin and HR can view all employees' payroll, employees can only view their own
// ✅ Correct
Route::prefix('payroll')->name('payroll.')->group(function () {
    Route::get('/',                        [PayrollController::class, 'index'])->name('index');
    Route::get('/{employee}/payslip',      [PayrollController::class, 'downloadPayslip'])->name('payslip');
    Route::get('/{employee}',              [PayrollController::class, 'show'])->name('show');
});

    // Manual Payroll Attendance Encoding — admin and HR only
    Route::middleware('permission:edit.attendance')->prefix('manual-payroll-attendance')->name('manual-payroll-attendance.')->group(function () {
        Route::get('/',                              [ManualPayrollAttendanceController::class, 'index'])->name('index');
        Route::get('/period/{payrollPeriod}',        [ManualPayrollAttendanceController::class, 'showPeriod'])->name('period');
        Route::get('/period/{payrollPeriod}/employee/{employee}', [ManualPayrollAttendanceController::class, 'showEmployeeForm'])->name('employee-form');
        Route::get('/period/{payrollPeriod}/summary', [ManualPayrollAttendanceController::class, 'getPeriodSummary'])->name('summary');
        Route::post('/preview',                      [ManualPayrollAttendanceController::class, 'preview'])->name('preview');
        Route::post('/save',                         [ManualPayrollAttendanceController::class, 'save'])->name('save');
        Route::post('/adjustments',                  [ManualPayrollAttendanceController::class, 'saveAdjustment'])->name('adjustments');
        Route::delete('/adjustments/{adjustment}',    [ManualPayrollAttendanceController::class, 'deleteAdjustment'])->name('delete-adjustment');
    });

    // Payroll Period Routes — admin and HR only
   Route::middleware('permission:manage.payroll.periods')->prefix('payroll-periods')->name('payroll-periods.')->group(function () {
    Route::get('/create',                    [PayrollPeriodController::class, 'create'])->name('create');
    Route::post('/',                         [PayrollPeriodController::class, 'store'])->name('store');
    Route::post('/{payrollPeriod}/finalize', [PayrollPeriodController::class, 'finalize'])->name('finalize');
    Route::delete('/{payrollPeriod}',        [PayrollPeriodController::class, 'destroy'])->name('destroy');
});

    // Government Contributions Routes — all authenticated users can view, admin and HR can edit
    Route::prefix('government-contributions')->name('government-contributions.')->group(function () {
        Route::get('/', [GovernmentContributionsController::class, 'index'])->name('index')->middleware('permission:view.gov.contributions');
        Route::get('/{employee}', [GovernmentContributionsController::class, 'show'])->name('show')->middleware('permission:view.gov.contributions');
        Route::get('/api/all-with-contributions', [GovernmentContributionsController::class, 'getAllEmployeesWithContributions'])->name('api.all-with-contributions')->middleware('permission:view.gov.contributions');
        Route::middleware('permission:edit.gov.contributions')->group(function () {
            Route::patch('/{employee}', [GovernmentContributionsController::class, 'update'])->name('update');
        });
    });

    // RBAC Management Routes — admin only
    Route::middleware('permission:manage.roles')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/assign-user', [RoleController::class, 'assignUser'])->name('assign.user');
        Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('remove.user');
    });

    Route::middleware('permission:manage.permissions')->prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:view.audit.logs')->prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/generate-hr-admin', [NotificationController::class, 'generateHrAdminNotifications'])->name('generate-hr-admin');
    });
});

// API Routes (JWT Authentication)
Route::prefix('api')->group(function () {
    Route::post('/login',             [AuthController::class, 'apiLogin']);
    Route::post('/register',          [AuthController::class, 'register']);
    Route::post('/validate-password', [AuthController::class, 'validatePassword']);

    Route::middleware('jwt')->group(function () {

        Route::prefix('employees')->group(function () {
            Route::get('/',                     [EmployeeController::class, 'apiIndex']);
            Route::post('/',                    [EmployeeController::class, 'apiStore']);
            Route::get('/{employee}',           [EmployeeController::class, 'apiShow']);
            Route::put('/{employee}',           [EmployeeController::class, 'apiUpdate']);
            Route::patch('/{employee}/archive', [EmployeeController::class, 'apiArchive']);
        });

        // ── Payroll API ────────────────────────────────────────
        Route::prefix('payroll-periods')->name('api.payroll-periods.')->group(function () {
            Route::get('/',                          [PayrollPeriodController::class, 'index'])->name('index');
            Route::post('/',                         [PayrollPeriodController::class, 'store'])->name('store');
            Route::get('/{payrollPeriod}',           [PayrollPeriodController::class, 'show'])->name('show');
        });

        Route::prefix('payroll-inputs')->name('api.payroll-inputs.')->group(function () {
            Route::get('/',                          [PayrollInputController::class, 'index'])->name('index');   // ?payroll_period_id=1
            Route::post('/',                         [PayrollInputController::class, 'store'])->name('store');
            Route::put('/{payrollInput}',            [PayrollInputController::class, 'update'])->name('update');
            Route::delete('/{payrollInput}',         [PayrollInputController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('payroll')->name('api.payroll.')->group(function () {
            Route::post('/compute',                  [PayrollController::class, 'compute'])->name('compute');
            Route::post('/finalize',                 [PayrollController::class, 'finalize'])->name('finalize');
        });

        Route::post('/logout',        [AuthController::class, 'apiLogout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});