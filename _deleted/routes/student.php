<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Student\BookingController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\HealthController;
use App\Http\Controllers\Student\LeaveController;
use App\Http\Controllers\Student\MenuController;
use App\Http\Controllers\Student\QrController;
use App\Http\Controllers\Student\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/student/login', [StudentAuthController::class, 'create'])->name('student.login');
    Route::post('/student/login', [StudentAuthController::class, 'store'])->name('student.login.store');
});

Route::post('/student/logout', [StudentAuthController::class, 'destroy'])->middleware('auth:student')->name('student.logout');

Route::prefix('student')->middleware('auth:student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/menus', [MenuController::class, 'index'])->name('menus');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/qr', [QrController::class, 'show'])->name('qr');
    Route::get('/qr/token', [QrController::class, 'token'])->name('qr.token');
    Route::resource('/leave', LeaveController::class)->names('leave');
    Route::resource('/tickets', TicketController::class)->names('tickets');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/health', [HealthController::class, 'index'])->name('health');
});
