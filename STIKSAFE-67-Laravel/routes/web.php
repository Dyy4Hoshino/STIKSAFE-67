<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminScanController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ConfirmController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HistoryController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('public.landing'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan', [ScanController::class, 'create'])->name('scan.create');
    Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
    Route::get('/scans/{id}', [ScanController::class, 'show'])->name('scans.show');
    Route::get('/scans/{id}/cancel', [ScanController::class, 'cancel'])->name('scans.cancel');
    Route::get('/confirm', [ConfirmController::class, 'show'])->name('confirm');
    Route::post('/scans/{id}/cancel', [ConfirmController::class, 'cancelScan'])->name('scans.cancel.confirm');
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/scans', [AdminScanController::class, 'index'])->name('scans');
        Route::get('/scans/{id}', [AdminScanController::class, 'show'])->name('scans.show');
    });
});


