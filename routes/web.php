<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;


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

// Protected Routes — 'auth' middleware guarantees a logged-in session for everything below
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Single dashboard for all roles.
    // 'auth' middleware is the security gate — no unauthenticated user can reach it.
    // Role-based data scoping is enforced inside AuthController::dashboard().
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Employee Management — accessible to authenticated users.
    // Add ->middleware('role:admin,hr') here if employees should NOT access these routes.
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/',                     [EmployeeController::class, 'index'])->name('index');
        Route::get('/create',               [EmployeeController::class, 'create'])->name('create');
        Route::post('/',                    [EmployeeController::class, 'store'])->name('store');
        Route::get('/archived',             [EmployeeController::class, 'archived'])->name('archived');
        Route::get('/{employee}',           [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit',      [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}',           [EmployeeController::class, 'update'])->name('update');
        Route::patch('/{employee}/archive', [EmployeeController::class, 'archive'])->name('archive');
        Route::patch('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');
    });

    // Example: routes that are genuinely role-restricted (separate URL, separate controller action)
    // Use this pattern only when different roles need a truly different page or data set,
    // not just a different-colored dashboard.
    //
    // Route::middleware('role:admin')->group(function () {
    //     Route::get('/admin/users',    [UserController::class, 'index'])->name('admin.users');
    //     Route::get('/admin/audit',    [AuditController::class, 'index'])->name('admin.audit');
    // });
    //
    // Route::middleware('role:hr')->group(function () {
    //     Route::get('/hr/leaves',      [LeaveController::class, 'index'])->name('hr.leaves');
    //     Route::get('/hr/payroll',     [PayrollController::class, 'index'])->name('hr.payroll');
    // });
});

// API Routes (JWT Authentication)
Route::prefix('api')->group(function () {
    Route::post('/login',             [AuthController::class, 'apiLogin']);
    Route::post('/register',          [AuthController::class, 'register']);
    Route::post('/validate-password', [AuthController::class, 'validatePassword']);

    Route::middleware('jwt')->group(function () {
        Route::post('/logout',        [AuthController::class, 'apiLogout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});