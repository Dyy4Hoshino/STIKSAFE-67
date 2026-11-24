<?php

use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\ScanController as AdminScanController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ScanController;
use App\Http\Controllers\Api\V1\ScanFeedbackController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth publik
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/auth/reset-password', [ForgotPasswordController::class, 'resetPassword']);

    // Area terautentikasi (User & Admin)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        // Profil
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        // Dashboard User
        Route::get('/dashboard', [DashboardController::class, 'userDashboard']);

        // Scans
        Route::post('/scans', [ScanController::class, 'store']);
        Route::post('/scans/{scan}/feedback', [ScanFeedbackController::class, 'store']);
        Route::get('/scans', [ScanController::class, 'index']);
        Route::get('/scans/{scan}', [ScanController::class, 'show']);
        Route::get('/scans/{scan}/status', [ScanController::class, 'status']);
        Route::post('/scans/{scan}/cancel', [ScanController::class, 'cancel']);
        Route::delete('/scans/{scan}', [ScanController::class, 'destroy']);

        // Admin
        Route::prefix('admin')->middleware('admin')->group(function (): void {
            Route::get('/dashboard', [AdminDashboardController::class, 'systemDashboard']);
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::get('/users/{user}', [AdminUserController::class, 'show']);
            Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
            Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);

            Route::get('/scans', [AdminScanController::class, 'index']);
            Route::get('/scans/{scan}', [AdminScanController::class, 'show']);
            Route::post('/scans/{scan}/retry', [AdminScanController::class, 'retry']);
        });
    });
});
