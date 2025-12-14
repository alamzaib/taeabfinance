<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\AnalyticsController;

// Admin Authentication Routes
Route::prefix('backoffice')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout.post');

    // Protected Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // User Management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Roles & Permissions
        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
        
        // Packages
        Route::resource('packages', PackageController::class);
        Route::post('packages/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
        
        // Payments
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        
        // Stripe Payment Links
        Route::post('payments/{payment}/generate-payment-link', [\App\Http\Controllers\Admin\StripePaymentController::class, 'generatePaymentLink'])->name('payments.generate-link');
        Route::get('payments/{payment}/payment-status', [\App\Http\Controllers\Admin\StripePaymentController::class, 'getPaymentStatus'])->name('payments.status');
        
        // Refund Requests
        Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
        Route::get('refunds/{refundRequest}', [RefundController::class, 'show'])->name('refunds.show');
        Route::post('refunds/{refundRequest}/approve', [RefundController::class, 'approve'])->name('refunds.approve');
        Route::post('refunds/{refundRequest}/reject', [RefundController::class, 'reject'])->name('refunds.reject');
        
        // Support Tickets
        Route::get('support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::post('support-tickets/{supportTicket}/assign', [SupportTicketController::class, 'assign'])->name('support-tickets.assign');
        Route::post('support-tickets/{supportTicket}/resolve', [SupportTicketController::class, 'resolve'])->name('support-tickets.resolve');
        Route::post('support-tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('support-tickets.reply');
        
        // Logs
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');
        
        // Analytics
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

