<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Root redirect
Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel Vue 2 SPA Router Handler
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/{any?}', function () {
        return view('backend.app');
    })->where('any', '.*')->name('admin.dashboard');
});
