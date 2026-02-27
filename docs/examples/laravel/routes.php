<?php

declare(strict_types=1);

use App\Http\Controllers\TwoFactorController;
use App\Http\Middleware\EnsureTwoFactorVerified;
use Illuminate\Support\Facades\Route;

// --- 2FA Setup Routes (authenticated users only) ---
Route::middleware('auth')->prefix('two-factor')->name('two-factor.')->group(function (): void {
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
    Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes');
});

// --- 2FA Challenge Route ---
Route::middleware('auth')->group(function (): void {
    Route::get('/two-factor/verify', fn () => view('auth.two-factor-verify'))->name('two-factor.verify');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'verify']);
});

// --- Protected Routes (require 2FA verification) ---
Route::middleware(['auth', EnsureTwoFactorVerified::class])->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    // Add your protected routes here
});
