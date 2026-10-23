<?php

use App\Http\Controllers\Auth\ContractorAuthController;
use App\Http\Controllers\Contractor\ScanController;
use App\Livewire\ContractorDashboard;
use App\Livewire\ContractorHeadcount;
use App\Livewire\ContractorLeave;
use App\Livewire\ContractorLogin;
use App\Livewire\ContractorMenuBuilder;
use App\Livewire\ContractorStaff;
use App\Livewire\ContractorTicket;
use App\Livewire\ContractorWaste;
use App\Livewire\MealTimingManager;
use App\Livewire\MenuUpdate;
use Illuminate\Support\Facades\Route;

Route::post('/contractor/logout', [ContractorAuthController::class, 'destroy'])->middleware('auth:contractor')->name('contractor.logout');

Route::prefix('contractor')->name('contractor.')->group(function () {
    Route::get('/login', ContractorLogin::class)->name('login')->middleware('guest');
    Route::get('/about', function () {
        return view('layouts.contractor');
    })->name('about')->middleware('guest');

    Route::middleware('auth:contractor')->group(function () {
        Route::get('/dashboard', ContractorDashboard::class)->name('dashboard');
        Route::get('/menu-builder', ContractorMenuBuilder::class)->name('menu-builder');
        Route::get('/menu-update', MenuUpdate::class)->name('menu-update');
        Route::get('/meal-timing', MealTimingManager::class)->name('meal-timing');
        Route::get('/headcount', ContractorHeadcount::class)->name('headcount');
        Route::get('/waste', ContractorWaste::class)->name('waste.index');
        Route::get('/leaves', ContractorLeave::class)->name('leaves.index');
        Route::get('/staff', ContractorStaff::class)->name('staff');
        Route::get('/tickets', ContractorTicket::class)->name('tickets.index');
        Route::get('/scanner', [ScanController::class, 'index'])->name('scanner');
    });
});
