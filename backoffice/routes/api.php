<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\BillingController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/packages', [PackageController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
    
        // Protected routes (require authentication)
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/billing', [BillingController::class, 'index']);
            Route::get('/billing/history', [BillingController::class, 'history']);
            Route::get('/billing/export', [BillingController::class, 'export']);
            Route::post('/packages/request', [PackageController::class, 'request']);
        
        // Payment methods
        Route::get('/payment-methods', [\App\Http\Controllers\Api\V1\PaymentMethodController::class, 'index']);
        Route::post('/payment-methods', [\App\Http\Controllers\Api\V1\PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{paymentMethod}', [\App\Http\Controllers\Api\V1\PaymentMethodController::class, 'update']);
        Route::delete('/payment-methods/{paymentMethod}', [\App\Http\Controllers\Api\V1\PaymentMethodController::class, 'destroy']);
        Route::post('/payment-methods/{paymentMethod}/set-primary', [\App\Http\Controllers\Api\V1\PaymentMethodController::class, 'setPrimary']);
        
        // Earnings
        Route::get('/earnings', [\App\Http\Controllers\Api\V1\EarningController::class, 'index']);
        Route::get('/earnings/history', [\App\Http\Controllers\Api\V1\EarningController::class, 'history']);
            Route::get('/earnings/export', [\App\Http\Controllers\Api\V1\EarningController::class, 'export']);
        });
    });

    // Stripe Webhook (no authentication required)
    Route::post('/stripe/webhook', [\App\Http\Controllers\Api\V1\StripeWebhookController::class, 'handle']);

