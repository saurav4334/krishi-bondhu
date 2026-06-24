<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EquipmentController as AdminEquipmentController;
use App\Http\Controllers\Admin\MarketplaceController as AdminMarketplaceController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\SmsController as AdminSmsController;
use App\Http\Controllers\Admin\VoiceController as AdminVoiceController;
use App\Http\Controllers\Admin\WeatherAlertController as AdminWeatherAlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\VoiceCallbackController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// Root → redirect to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Public Protiddhoni IVR DTMF webhook (machine-to-machine, CSRF-exempt). No auth.
Route::post('/voice/callback', [VoiceCallbackController::class, 'handle'])->name('voice.callback');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Registration mobile verification (when SMS module is enabled)
    Route::get('/register/verify', [AuthController::class, 'showRegisterVerify'])->name('register.verify');
    Route::post('/register/verify', [AuthController::class, 'registerVerify'])->name('register.verify.submit');
    Route::post('/register/verify/resend', [AuthController::class, 'registerResend'])->name('register.verify.resend');

    // Login with OTP (alternative to password)
    Route::get('/login/otp', [AuthController::class, 'showOtpLogin'])->name('login.otp');
    Route::post('/login/otp', [AuthController::class, 'sendLoginOtp'])->name('login.otp.send');
    Route::get('/login/otp/verify', [AuthController::class, 'showOtpLoginVerify'])->name('login.otp.verify');
    Route::post('/login/otp/verify', [AuthController::class, 'verifyLoginOtp'])->name('login.otp.verify.submit');
    Route::post('/login/otp/resend', [AuthController::class, 'loginOtpResend'])->name('login.otp.resend');

    // Forgot / reset password via OTP
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.otp');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
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

    // Crop Marketplace (ফসল বিক্রয় — crops only)
    Route::get('/market', [PostController::class, 'index'])->name('market.index');
    Route::get('/market/create', [PostController::class, 'create'])->name('market.create');
    Route::post('/market', [PostController::class, 'store'])->name('market.store');
    Route::delete('/market/{post}', [PostController::class, 'destroy'])->name('market.destroy');

    // কৃষি সরঞ্জাম — Equipment & Agri-input Marketplace (independent module)
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::get('/equipment/create', [EquipmentController::class, 'create'])->name('equipment.create');
    Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{product}', [EquipmentController::class, 'show'])->name('equipment.show');
    Route::delete('/equipment/{product}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

    // Experts
    Route::get('/experts', [ExpertController::class, 'index'])->name('experts.index');

    // Agriculture News
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{post:slug}', [NewsController::class, 'show'])->name('news.show');

    // Smart Weather
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

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

        // Marketplace moderation + categories
        Route::get('/marketplace', [AdminMarketplaceController::class, 'index'])->name('marketplace.index');
        Route::patch('/marketplace/{post}/approve', [AdminMarketplaceController::class, 'approve'])->name('marketplace.approve');
        Route::delete('/marketplace/{post}/reject', [AdminMarketplaceController::class, 'reject'])->name('marketplace.reject');
        Route::patch('/marketplace/{post}/feature', [AdminMarketplaceController::class, 'feature'])->name('marketplace.feature');
        Route::post('/marketplace/categories', [AdminMarketplaceController::class, 'storeCategory'])->name('marketplace.categories.store');
        Route::patch('/marketplace/categories/{category}/toggle', [AdminMarketplaceController::class, 'toggleCategory'])->name('marketplace.categories.toggle');
        Route::delete('/marketplace/categories/{category}', [AdminMarketplaceController::class, 'deleteCategory'])->name('marketplace.categories.delete');

        // কৃষি সরঞ্জাম (equipment) moderation + categories
        Route::get('/equipment', [AdminEquipmentController::class, 'index'])->name('equipment.index');
        Route::patch('/equipment/{product}/approve', [AdminEquipmentController::class, 'approve'])->name('equipment.approve');
        Route::delete('/equipment/{product}/reject', [AdminEquipmentController::class, 'reject'])->name('equipment.reject');
        Route::patch('/equipment/{product}/feature', [AdminEquipmentController::class, 'feature'])->name('equipment.feature');
        Route::post('/equipment/categories', [AdminEquipmentController::class, 'storeCategory'])->name('equipment.categories.store');
        Route::patch('/equipment/categories/{category}', [AdminEquipmentController::class, 'updateCategory'])->name('equipment.categories.update');
        Route::patch('/equipment/categories/{category}/toggle', [AdminEquipmentController::class, 'toggleCategory'])->name('equipment.categories.toggle');
        Route::delete('/equipment/categories/{category}', [AdminEquipmentController::class, 'deleteCategory'])->name('equipment.categories.delete');

        // News management
        Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('/news/{news}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('/news/{news}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('/news/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');
        Route::post('/news/categories', [AdminNewsController::class, 'storeCategory'])->name('news.categories.store');
        Route::delete('/news/categories/{category}', [AdminNewsController::class, 'deleteCategory'])->name('news.categories.delete');
        Route::post('/news/{news}/sms', [AdminNewsController::class, 'sendSms'])->name('news.sms');
        Route::post('/news/{news}/voice', [AdminNewsController::class, 'sendVoice'])->name('news.voice');

        // Weather alerts
        Route::get('/weather', [AdminWeatherAlertController::class, 'index'])->name('weather.index');
        Route::post('/weather', [AdminWeatherAlertController::class, 'store'])->name('weather.store');
        Route::delete('/weather/{alert}', [AdminWeatherAlertController::class, 'destroy'])->name('weather.destroy');
        Route::post('/weather/{alert}/sms', [AdminWeatherAlertController::class, 'sendSms'])->name('weather.sms');
        Route::post('/weather/{alert}/voice', [AdminWeatherAlertController::class, 'sendVoice'])->name('weather.voice');

        // SMS module (NotifyBD): settings, balance, test, broadcast, logs
        Route::get('/sms', [AdminSmsController::class, 'index'])->name('sms.index');
        Route::post('/sms/settings', [AdminSmsController::class, 'updateSettings'])->name('sms.settings');
        Route::post('/sms/test', [AdminSmsController::class, 'sendTest'])->name('sms.test');
        Route::post('/sms/broadcast', [AdminSmsController::class, 'broadcast'])->name('sms.broadcast');

        // Protiddhoni Voice module: settings, templates (CRUD), test call, campaign, logs, retry
        Route::get('/voice', [AdminVoiceController::class, 'index'])->name('voice.index');
        Route::post('/voice/settings', [AdminVoiceController::class, 'updateSettings'])->name('voice.settings');
        Route::post('/voice/templates', [AdminVoiceController::class, 'storeTemplate'])->name('voice.templates.store');
        Route::post('/voice/templates/{template}', [AdminVoiceController::class, 'updateTemplate'])->name('voice.templates.update');
        Route::patch('/voice/templates/{template}/toggle', [AdminVoiceController::class, 'toggleTemplate'])->name('voice.templates.toggle');
        Route::delete('/voice/templates/{template}', [AdminVoiceController::class, 'destroyTemplate'])->name('voice.templates.destroy');
        Route::post('/voice/test', [AdminVoiceController::class, 'sendTest'])->name('voice.test');
        Route::post('/voice/campaign', [AdminVoiceController::class, 'campaign'])->name('voice.campaign');
        Route::post('/voice/retry', [AdminVoiceController::class, 'retry'])->name('voice.retry');
        Route::post('/voice/logs/{log}/retry', [AdminVoiceController::class, 'retryCall'])->name('voice.logs.retry');
        Route::patch('/voice/callbacks/{callback}/done', [AdminVoiceController::class, 'markCallbackDone'])->name('voice.callbacks.done');
    });
});
