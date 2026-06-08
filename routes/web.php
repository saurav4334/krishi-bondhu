<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

// Root → redirect to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI Disease Scan
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');

    // Market Prices
    Route::get('/prices', [PriceController::class, 'index'])->name('prices.index');

    // Crop Marketplace
    Route::get('/market', [PostController::class, 'index'])->name('market.index');
    Route::get('/market/create', [PostController::class, 'create'])->name('market.create');
    Route::post('/market', [PostController::class, 'store'])->name('market.store');
    Route::delete('/market/{post}', [PostController::class, 'destroy'])->name('market.destroy');

    // Experts
    Route::get('/experts', [ExpertController::class, 'index'])->name('experts.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin (admin role only)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/notifications', [AdminController::class, 'storeNotification'])->name('notifications.store');
        Route::post('/prices', [AdminController::class, 'storePrice'])->name('prices.store');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    });
});
