<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MealConfigController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:web', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/users', UserController::class)->names('users');
    Route::resource('/meals', MealConfigController::class)->names('meals');
});
