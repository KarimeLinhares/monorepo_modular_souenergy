<?php

use App\Http\Controllers\Api\Admin\PrivacyPolicyConfigController;
use App\Http\Controllers\Api\Admin\UserPrivacyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/privacy-policy/status', [PrivacyPolicyController::class, 'status']);
    Route::post('/privacy-policy/accept', [PrivacyPolicyController::class, 'accept']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/privacy-policy', [PrivacyPolicyConfigController::class, 'show']);
        Route::put('/privacy-policy', [PrivacyPolicyConfigController::class, 'update']);
        Route::get('/users', [UserPrivacyController::class, 'index']);
    });
});
