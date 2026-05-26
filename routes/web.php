<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
=======
use Illuminate\Http\Request;
>>>>>>> 6e8e425cc398210b56a9a3422f2e38cd2169470d
use App\Http\Controllers\AuthController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
<<<<<<< HEAD
=======
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
>>>>>>> 6e8e425cc398210b56a9a3422f2e38cd2169470d
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
});
<<<<<<< HEAD
=======

// API Routes (JWT Authentication)
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'apiLogin']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/validate-password', [AuthController::class, 'validatePassword']);
    
    Route::middleware('jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'apiLogout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
>>>>>>> 6e8e425cc398210b56a9a3422f2e38cd2169470d
