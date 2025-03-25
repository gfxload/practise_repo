<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ApiReportController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\DownloadController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\UrlValidationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\SubscriptionManagementController; 
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// جعل صفحة تسجيل الدخول هي الصفحة الرئيسية
Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    // User Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Downloads Routes
    Route::resource('downloads', DownloadController::class);
    Route::get('/downloads/{download}/download', [DownloadController::class, 'download'])->name('downloads.download');
    
    // Notification Routes
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-read', [NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Subscription Routes
    Route::post('/subscription/renew', [SubscriptionController::class, 'renew'])->name('subscription.renew');
    Route::get('/subscription/status', [SubscriptionController::class, 'status'])->name('subscription.status');
    
    // Services Routes
    Route::get('/services', [App\Http\Controllers\ServicesController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [App\Http\Controllers\ServicesController::class, 'show'])->name('services.show');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('services', ServiceController::class);
    Route::post('/services/update-order', [ServiceController::class, 'updateOrder'])->name('services.update-order');
    Route::post('/users/{user}/add-points', [UserController::class, 'addPoints'])->name('users.add-points');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/users', [ReportController::class, 'users'])->name('reports.users');
    Route::get('/reports/downloads', [ReportController::class, 'downloads'])->name('reports.downloads');
    Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
    Route::get('/reports/api', [ApiReportController::class, 'index'])->name('reports.api');
    Route::get('/reports/api/{id}', [ApiReportController::class, 'detail'])->name('reports.api.detail');
    
    // Notifications
    Route::get('/notifications/send', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [AdminNotificationController::class, 'send'])->name('notifications.send');
    
    // Settings
    Route::get('/settings/download', [SettingsController::class, 'downloadSettings'])->name('settings.download');
    Route::put('/settings/download', [SettingsController::class, 'updateDownloadSettings'])->name('settings.download.update');
    
    // Subscription Management
    Route::get('/subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{user}/edit', [SubscriptionManagementController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{user}', [SubscriptionManagementController::class, 'update'])->name('subscriptions.update');
    Route::post('/subscriptions/{user}/renew', [SubscriptionManagementController::class, 'renew'])->name('subscriptions.renew');
    
    Route::get('reports/downloads', [App\Http\Controllers\Admin\ReportController::class, 'downloads'])->name('reports.downloads');
    Route::get('downloads/{download}/download', [App\Http\Controllers\Admin\DownloadController::class, 'download'])->name('downloads.download');
});

// URL Validation Route
Route::get('/validate-url', [UrlValidationController::class, 'validateUrl'])->name('validate.url');

require __DIR__.'/auth.php';
