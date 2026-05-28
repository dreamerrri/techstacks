<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Public API Routes
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/validate-password', [AuthController::class, 'validatePassword']);

// Protected API Routes (JWT Authentication Required)
Route::middleware('jwt')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
