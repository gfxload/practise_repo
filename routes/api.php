<?php

use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/user/points-history', [UserController::class, 'pointsHistory']);
    
    // Download routes
    Route::prefix('api')->group(function () {
        Route::apiResource('downloads', DownloadController::class)->except(['update', 'destroy']);
        Route::get('/downloads/{download}/url', [DownloadController::class, 'getDownloadUrl']);
    });
    
    // Service routes
    Route::get('/services', [ServiceController::class, 'index']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/points', [UserController::class, 'addPoints']);
    Route::apiResource('services', ServiceController::class);
});
