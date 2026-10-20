<?php

use App\Http\Controllers\Auth\ContractorAuthController;
use App\Http\Controllers\Contractor\DashboardController;
use App\Http\Controllers\Contractor\HeadcountController;
use App\Http\Controllers\Contractor\LeaveApprovalController;
use App\Http\Controllers\Contractor\MenuBuilderController;
use App\Http\Controllers\Contractor\ScanController;
use App\Http\Controllers\Contractor\StaffController;
use App\Http\Controllers\Contractor\TicketController;
use App\Http\Controllers\Contractor\WasteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/contractor/login', [ContractorAuthController::class, 'create'])->name('contractor.login');
    Route::post('/contractor/login', [ContractorAuthController::class, 'store'])->name('contractor.login.store');
});

Route::post('/contractor/logout', [ContractorAuthController::class, 'destroy'])->middleware('auth:contractor')->name('contractor.logout');

Route::prefix('contractor')->middleware('auth:contractor')->name('contractor.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/menu-builder', [MenuBuilderController::class, 'index'])->name('menu-builder');
    Route::post('/menu-builder', [MenuBuilderController::class, 'store'])->name('menu-builder.store');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    Route::get('/headcount', [HeadcountController::class, 'index'])->name('headcount');
    Route::get('/scanner', [ScanController::class, 'index'])->name('scanner');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::get('/waste', [WasteController::class, 'index'])->name('waste.index');
    Route::post('/waste', [WasteController::class, 'store'])->name('waste.store');
    Route::get('/leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
});
