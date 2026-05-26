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

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // All roles can reach /dashboard; the controller scopes data per role.
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

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