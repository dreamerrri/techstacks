<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\BenefitController;


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

    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
    Route::get('/',                    [UserController::class, 'index'])->name('index');
    Route::patch('/{user}/toggle',     [UserController::class, 'toggleActive'])->name('toggle');
    Route::patch('/{user}/role',       [UserController::class, 'updateRole'])->name('role');
});

    // Employee Management — admin and HR only.
    Route::middleware('role:admin,hr')->prefix('employees')->name('employees.')->group(function () {

        // ── Static routes FIRST (must come before the /{employee} wildcard) ──
        Route::get('/',          [EmployeeController::class, 'index'])->name('index');
        Route::get('/archived',  [EmployeeController::class, 'archived'])->name('archived');

        // Create & Store — admin and HR
        Route::middleware('role:admin,hr')->group(function () {
            Route::get('/create', [EmployeeController::class, 'create'])->name('create');
            Route::post('/',      [EmployeeController::class, 'store'])->name('store');
        });

        // ── Wildcard routes AFTER static ones ──
        Route::get('/{employee}',      [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}',      [EmployeeController::class, 'update'])->name('update');

        // Archive & Restore — admin only
        Route::middleware('role:admin')->group(function () {
            Route::patch('/{employee}/archive', [EmployeeController::class, 'archive'])->name('archive');
            Route::patch('/{employee}/restore', [EmployeeController::class, 'restore'])->name('restore');
        });
    });

    // Attendance Management — admin and HR only (separate group to avoid prefix inheritance)
    Route::middleware('role:admin,hr')->prefix('employees/{employee}/attendance')->name('attendance.')->group(function () {
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/',      [AttendanceController::class, 'store'])->name('store');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}',      [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}',   [AttendanceController::class, 'destroy'])->name('destroy');
    });

    // Allowance Management — admin and HR only
    Route::middleware('role:admin,hr')->prefix('employees/{employee}/allowances')->name('allowances.')->group(function () {
        Route::post('/', [AllowanceController::class, 'store'])->name('store');
        Route::put('/{allowance}', [AllowanceController::class, 'update'])->name('update');
        Route::delete('/{allowance}', [AllowanceController::class, 'destroy'])->name('destroy');
    });

    // Benefit Management — admin and HR only
    Route::middleware('role:admin,hr')->prefix('employees/{employee}/benefits')->name('benefits.')->group(function () {
        Route::post('/', [BenefitController::class, 'store'])->name('store');
        Route::put('/{benefit}', [BenefitController::class, 'update'])->name('update');
        Route::delete('/{benefit}', [BenefitController::class, 'destroy'])->name('destroy');
    });

    // Payroll Management — all authenticated users can access
    // Admin and HR can view all employees' payroll, employees can only view their own
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',         [PayrollController::class, 'index'])->name('index');
        Route::get('/{employee}', [PayrollController::class, 'show'])->name('show');
    });
});

// API Routes (JWT Authentication)
Route::prefix('api')->group(function () {
    Route::post('/login',             [AuthController::class, 'apiLogin']);
    Route::post('/register',          [AuthController::class, 'register']);
    Route::post('/validate-password', [AuthController::class, 'validatePassword']);

    Route::middleware('jwt')->group(function () {

    Route::prefix('employees')->group(function () {
        Route::get('/',                          [EmployeeController::class, 'apiIndex']);
        Route::post('/',                         [EmployeeController::class, 'apiStore']);
        Route::get('/{employee}',                [EmployeeController::class, 'apiShow']);
        Route::put('/{employee}',                [EmployeeController::class, 'apiUpdate']);
        Route::patch('/{employee}/archive',      [EmployeeController::class, 'apiArchive']);
    });
    
        Route::post('/logout',        [AuthController::class, 'apiLogout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});