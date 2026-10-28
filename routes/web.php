<?php

use App\Http\Controllers\Portal\PortalMealController;
use App\Http\Controllers\Portal\PortalQrController;
use App\Http\Controllers\ProfileController;
use App\Livewire\StudentPortal;
use App\Http\Controllers\Auth\FirstLoginPasswordController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'portal-chooser')->name('home');
Route::get('/student', StudentPortal::class)->middleware(['portal.account.active', 'password.change.completed'])->name('student.portal');

Route::middleware(['auth', 'portal.account.active', 'password.change.completed'])->group(function (): void {
    Route::get('/password/first-login', [FirstLoginPasswordController::class, 'create'])
        ->withoutMiddleware('password.change.completed')
        ->name('password.first-login');
    Route::put('/password/first-login', [FirstLoginPasswordController::class, 'update'])
        ->withoutMiddleware('password.change.completed')
        ->name('password.first-login.update');
});

Route::get('/portal/qr/public-key', [PortalQrController::class, 'publicKey'])
    ->name('portal.qr.public-key');

Route::prefix('portal')->name('portal.')->middleware(['auth:student', 'portal.account.active', 'password.change.completed'])->group(function () {
    Route::post('/meals/{menu}/confirm', [PortalMealController::class, 'confirm'])
        ->name('meals.confirm');
    Route::post('/meals/{menu}/cancel', [PortalMealController::class, 'cancel'])
        ->name('meals.cancel');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->role === 'warden') {
        return redirect()->route('warden.dashboard');
    }
    if ($user?->role === 'contractor') {
        return redirect()->route('contractor.dashboard');
    }
    if ($user?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('student.portal');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'portal.account.active', 'password.change.completed'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
