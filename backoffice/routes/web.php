<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

// Root redirect to admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Route aliases for AdminLTE compatibility
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
