<?php

use App\Http\Controllers\Api\v1\Admin\CategoryAdminController;
use App\Http\Controllers\Api\v1\Admin\DashboardAdminController;
use App\Http\Controllers\Api\v1\Admin\ModerationController;
use App\Http\Controllers\Api\v1\Admin\ReportAdminController;
use App\Http\Controllers\Api\v1\Admin\UserAdminController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\InquiryController;
use App\Http\Controllers\Api\v1\LocationController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\ProductReportController;
use App\Http\Controllers\Api\v1\ReviewController;
use App\Http\Controllers\Api\v1\UploadController;
use App\Http\Controllers\Api\v1\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root API Fallback / Legacy Endpoints
|--------------------------------------------------------------------------
*/
Route::post('/general', [App\Http\Controllers\GeneralController::class, 'getGeneralData']);
Route::post('/configurations', [App\Http\Controllers\ConfigurationController::class, 'getConfigurations']);
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'getProfile']);
Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'updateProfile']);
Route::apiResource('/modules', App\Http\Controllers\ModuleController::class);
Route::apiResource('/roles', App\Http\Controllers\RoleController::class);
Route::apiResource('/role_permissions', App\Http\Controllers\RolePermissionController::class);

/*
|--------------------------------------------------------------------------
| Admin panel: General Settings (session login + CSRF, admins only)
|--------------------------------------------------------------------------
| The Vue admin runs on the same origin, so the `web` group gives it the
| session and CSRF check; `admin` rejects anyone who is not an admin.
*/
Route::middleware(['web', 'admin'])->prefix('general_setting')->group(function () {
    Route::get('/', [App\Http\Controllers\GeneralSettingController::class, 'index']);
    Route::post('/', [App\Http\Controllers\GeneralSettingController::class, 'update']);
    Route::post('/{key}/image', [App\Http\Controllers\GeneralSettingController::class, 'uploadImage'])
        ->where('key', '[a-z0-9_]+')->middleware('throttle:20,1');
    Route::delete('/{key}/image', [App\Http\Controllers\GeneralSettingController::class, 'removeImage'])
        ->where('key', '[a-z0-9_]+');
});

/*
|--------------------------------------------------------------------------
| Version 1 API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // --- Authentication ---
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

    // --- Public Marketplace Endpoints ---
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{identifier}', [ProductController::class, 'show']);

    Route::get('/locations', [LocationController::class, 'index']);

    // Public site settings (homepage banner, contact info…)
    Route::get('/settings', [\App\Http\Controllers\Api\v1\SettingController::class, 'index']);

    // --- Protected User/Seller Endpoints ---
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Listing photo upload (device / camera). Rate limited: 30 per minute per user.
        Route::post('/uploads/images', [UploadController::class, 'image'])->middleware('throttle:30,1');

        // Seller Listings Management
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/my-listings', [ProductController::class, 'myListings']);
        Route::post('/products/{id}/mark-sold', [ProductController::class, 'markAsSold']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

        // Inquiries & Messages
        Route::get('/inquiries', [InquiryController::class, 'index']);
        Route::post('/inquiries', [InquiryController::class, 'store']);

        // Reviews & Rating
        Route::post('/reviews', [ReviewController::class, 'store']);

        // Report Listing
        Route::post('/products/{id}/report', [ProductReportController::class, 'store']);
    });

    // --- Admin Panel Endpoints ---
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard-stats', [DashboardAdminController::class, 'stats']);

        // Moderation Queue
        Route::get('/moderation', [ModerationController::class, 'index']);
        Route::post('/moderation/{id}/approve', [ModerationController::class, 'approve']);
        Route::post('/moderation/{id}/reject', [ModerationController::class, 'reject']);

        // Category Management
        Route::get('/categories', [CategoryAdminController::class, 'index']);
        Route::post('/categories', [CategoryAdminController::class, 'store']);
        Route::put('/categories/{id}', [CategoryAdminController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy']);
        Route::post('/categories/{id}/attributes', [CategoryAdminController::class, 'storeAttribute']);

        // User Management & Verification
        Route::get('/users', [UserAdminController::class, 'index']);
        Route::post('/users/{id}/toggle-verification', [UserAdminController::class, 'toggleVerification']);
        Route::post('/users/{id}/toggle-status', [UserAdminController::class, 'toggleStatus']);

        // Reports Queue
        Route::get('/reports', [ReportAdminController::class, 'index']);
        Route::post('/reports/{id}/resolve', [ReportAdminController::class, 'resolve']);
        Route::post('/reports/{id}/dismiss', [ReportAdminController::class, 'dismiss']);

        // Banner Management
        Route::get('/banners', [\App\Http\Controllers\Api\v1\Admin\BannerAdminController::class, 'index']);
        Route::post('/banners', [\App\Http\Controllers\Api\v1\Admin\BannerAdminController::class, 'store']);
        Route::put('/banners/{id}', [\App\Http\Controllers\Api\v1\Admin\BannerAdminController::class, 'update']);
        Route::delete('/banners/{id}', [\App\Http\Controllers\Api\v1\Admin\BannerAdminController::class, 'destroy']);
    });

    // --- Legacy Vue 2 RBAC & Dynamic Menu Endpoints ---
    Route::post('/general', [App\Http\Controllers\GeneralController::class, 'getGeneralData']);
    Route::post('/configurations', [App\Http\Controllers\ConfigurationController::class, 'getConfigurations']);

    Route::apiResource('/modules', App\Http\Controllers\ModuleController::class);
    Route::apiResource('/roles', App\Http\Controllers\RoleController::class);
    Route::apiResource('/role_permissions', App\Http\Controllers\RolePermissionController::class);
});
