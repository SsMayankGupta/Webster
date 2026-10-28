<?php

use App\Http\Controllers\Contractor\ScanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:contractor', 'portal.account.active', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::post('/scans/redeem', [ScanController::class, 'redeem'])->name('api.scans.redeem');
    Route::post('/scans/batch-verify', [ScanController::class, 'batchVerify'])->name('api.scans.batch-verify');
});
