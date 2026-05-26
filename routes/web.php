<?php


use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('employees')->name('employees.')->group(function () {
    Route::get('/',                      [EmployeeController::class, 'index'])->name('index');
    Route::get('/create',                [EmployeeController::class, 'create'])->name('create');
    Route::post('/',                     [EmployeeController::class, 'store'])->name('store');
    Route::get('/archived',              [EmployeeController::class, 'archived'])->name('archived');
    Route::get('/{employee}',            [EmployeeController::class, 'show'])->name('show');
    Route::get('/{employee}/edit',       [EmployeeController::class, 'edit'])->name('edit');
    Route::put('/{employee}',            [EmployeeController::class, 'update'])->name('update');
    Route::patch('/{employee}/archive',  [EmployeeController::class, 'archive'])->name('archive');
    Route::patch('/{employee}/restore',  [EmployeeController::class, 'restore'])->name('restore');
});