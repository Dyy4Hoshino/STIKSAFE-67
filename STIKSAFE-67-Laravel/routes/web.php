<?php

use App\Http\Controllers\Web\AdminAuditLogController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminFeedbackController;
use App\Http\Controllers\Web\AdminNotificationBroadcastController;
use App\Http\Controllers\Web\AdminScanController;
use App\Http\Controllers\Web\AdminSettingsController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ConfirmController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HistoryController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ScanController;
use App\Http\Controllers\Web\ScanFeedbackController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'landingOrDashboard'])->name('home');
Route::get('/tentang', fn () => view('public.about'))->name('about');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', fn () => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Alamat email berhasil diverifikasi.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Tautan verifikasi baru telah dikirim ke email Anda.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');
    Route::get('/scan', [ScanController::class, 'create'])->name('scan.create');
    Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
    Route::post('/scan-url', [ScanController::class, 'storeUrl'])->name('scan.url');
    Route::get('/scans/{id}', [ScanController::class, 'show'])->name('scans.show');
    Route::get('/scans/{id}/status', [ScanController::class, 'status'])->name('scans.status');
    Route::post('/scans/{id}/feedback', [ScanFeedbackController::class, 'store'])->name('scans.feedback.store');
    Route::get('/scans/{id}/cancel', [ScanController::class, 'cancel'])->name('scans.cancel');
    Route::get('/confirm', [ConfirmController::class, 'show'])->name('confirm');
    Route::delete('/history/{id}', [HistoryController::class, 'destroy'])->name('history.destroy');
    Route::post('/scans/{id}/cancel', [ConfirmController::class, 'cancelScan'])->name('scans.cancel.confirm');
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/scans', [AdminScanController::class, 'index'])->name('scans');
        Route::get('/scans/{id}', [AdminScanController::class, 'show'])->name('scans.show');
        Route::post('/scans/{id}/retry', [AdminScanController::class, 'retry'])->name('scans.retry');
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback');
        Route::patch('/feedback/{feedback}/review', [AdminFeedbackController::class, 'review'])->name('feedback.review');
        Route::get('/notifications/create', [AdminNotificationBroadcastController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [AdminNotificationBroadcastController::class, 'store'])->name('notifications.store');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/force-logout', [AdminUserController::class, 'forceLogout'])->name('users.force-logout');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    });
});
