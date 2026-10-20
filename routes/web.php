<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => view('auth.student-login'))->name('login');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user?->role === 'student') return redirect()->route('student.dashboard');
    if ($user?->role === 'contractor') return redirect()->route('contractor.dashboard');
    if ($user?->role === 'admin') return redirect()->route('admin.dashboard');
    return redirect('/');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
