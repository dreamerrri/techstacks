<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Generic Dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Admin Dashboard - Only accessible by Admin role
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
    });
    
    // HR Dashboard - Only accessible by HR role
    Route::middleware('role:hr')->group(function () {
        Route::get('/hr/dashboard', [AuthController::class, 'hrDashboard'])->name('hr.dashboard');
    });
    
    // HR Specialist Dashboard - Only accessible by HR Specialist role
    Route::middleware('role:hr_specialist')->group(function () {
        Route::get('/hr-specialist/dashboard', [AuthController::class, 'hrSpecialistDashboard'])->name('hr-specialist.dashboard');
    });
});
