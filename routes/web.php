<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/{any?}', function () {
        return view('backend.app');
    })->where('any', '.*')->name('admin.dashboard');
});
