<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\PublicPaymentController;

// Root redirect to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Route aliases for AdminLTE compatibility
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Payment Routes
Route::get('/payment/{token}', [PublicPaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/success', [PublicPaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PublicPaymentController::class, 'cancel'])->name('payment.cancel');

// Unsubscribe Routes
Route::get('/unsubscribe/{token}', [\App\Http\Controllers\UnsubscribeController::class, 'unsubscribe'])->name('unsubscribe');
Route::get('/resubscribe/{token}', [\App\Http\Controllers\UnsubscribeController::class, 'resubscribe'])->name('resubscribe');
