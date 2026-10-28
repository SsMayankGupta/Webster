<?php

use App\Http\Controllers\Auth\WardenPasswordResetController;
use App\Livewire\WardenDashboard;
use App\Livewire\WardenLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('warden')->name('warden.')->group(function (): void {
    Route::get('/login', WardenLogin::class)->name('login')->middleware('guest:warden');

    Route::middleware('guest:warden')->group(function (): void {
        Route::get('/forgot-password', [WardenPasswordResetController::class, 'create'])
            ->name('password.request');
        Route::post('/forgot-password', [WardenPasswordResetController::class, 'storeLink'])
            ->middleware('throttle:6,1')
            ->name('password.email');
        Route::get('/reset-password/{token}', [WardenPasswordResetController::class, 'createReset'])
            ->middleware('throttle:6,1')
            ->name('password.reset');
        Route::post('/reset-password', [WardenPasswordResetController::class, 'storeReset'])
            ->middleware('throttle:6,1')
            ->name('password.store');
    });

    Route::middleware('auth:warden')->group(function (): void {
        Route::get('/dashboard', WardenDashboard::class)->name('dashboard');
        Route::post('/logout', function () {
            Auth::guard('warden')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('warden.login');
        })->name('logout');
    });
});
