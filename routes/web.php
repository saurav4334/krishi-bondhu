<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TransportController;
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

    // Agriculture Labor Service
    Route::get('/labor', [LaborController::class, 'index'])->name('labor.index');
    Route::get('/labor/register', [LaborController::class, 'register'])->name('labor.register');
    Route::post('/labor/register', [LaborController::class, 'storeWorker'])->name('labor.worker.store');
    Route::get('/labor/jobs/create', [LaborController::class, 'createJob'])->name('labor.jobs.create');
    Route::post('/labor/jobs', [LaborController::class, 'storeJob'])->name('labor.jobs.store');

    // Agriculture Transport Service
    Route::get('/transport', [TransportController::class, 'index'])->name('transport.index');
    Route::get('/transport/register', [TransportController::class, 'register'])->name('transport.register');
    Route::post('/transport/register', [TransportController::class, 'storeProvider'])->name('transport.provider.store');
    Route::get('/transport/book/{provider?}', [TransportController::class, 'book'])->name('transport.book');
    Route::post('/transport/book', [TransportController::class, 'storeBooking'])->name('transport.booking.store');

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

        // District management
        Route::post('/districts', [AdminController::class, 'storeDistrict'])->name('districts.store');
        Route::put('/districts/{district}', [AdminController::class, 'updateDistrict'])->name('districts.update');
        Route::patch('/districts/{district}/toggle', [AdminController::class, 'toggleDistrict'])->name('districts.toggle');
        Route::delete('/districts/{district}', [AdminController::class, 'deleteDistrict'])->name('districts.delete');
    });
});
