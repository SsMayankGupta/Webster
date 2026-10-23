<?php

use App\Http\Controllers\Portal\PortalMealController;
use App\Http\Controllers\Portal\PortalQrController;
use App\Http\Controllers\ProfileController;
use App\Livewire\StudentPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', StudentPortal::class)->name('home');

Route::get('/portal/qr/public-key', [PortalQrController::class, 'publicKey'])
    ->name('portal.qr.public-key');

Route::prefix('portal')->name('portal.')->middleware('auth:student')->group(function () {
    Route::post('/meals/{menu}/confirm', [PortalMealController::class, 'confirm'])
        ->name('meals.confirm');
    Route::post('/meals/{menu}/cancel', [PortalMealController::class, 'cancel'])
        ->name('meals.cancel');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user?->role === 'contractor') {
        return redirect()->route('contractor.dashboard');
    }
    if ($user?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect('/');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
