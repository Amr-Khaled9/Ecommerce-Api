<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\DeliveryAuthController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
        Route::get('/access-token', [AdminAuthController::class, 'getAccessToken']);
    });
});
Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'isCustomer'])->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [CustomerAuthController::class, 'profile']);
        Route::get('/access-token', [CustomerAuthController::class, 'getAccessToken']);
    });
});
Route::prefix('delivery')->group(function () {
    Route::post('/register', [DeliveryAuthController::class, 'register']);
    Route::post('/login', [DeliveryAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'isDelivery'])->group(function () {
        Route::post('/logout', [DeliveryAuthController::class, 'logout']);
        Route::get('/profile', [DeliveryAuthController::class, 'profile']);
        Route::get('/access-token', [DeliveryAuthController::class, 'getAccessToken']);
    });
});

Route::apiresource('products', ProductController::class)->only('index', 'show');

Route::middleware(['auth:sanctum', 'permission:create products'])->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy']);
});

Route::prefix('product')->controller(ProductController::class)->group(function () {
    Route::middleware(['auth:sanctum', 'permission:delete products'])->group(function () {
        Route::patch('restore/{id}', 'restore');
        Route::delete('forceDelete/{id}', 'forceDelete');
    });

    Route::post('search', 'search');
    Route::get('deleted', 'getDeleteOnly');
    Route::get('all', 'getAllProduct');
    Route::post('filter', 'filterByPrice');
});


Route::apiresource('categories', CategoryController::class)->only('index', 'show');

Route::middleware(['auth:sanctum', 'permission:create products'])->group(function () {
    Route::apiResource('categories', CategoryController::class)
        ->only(['store', 'update', 'destroy']);
});

Route::get('category/all-product', [CategoryController::class, 'getAllProduct']);

Route::middleware(['auth:sanctum', 'permission:create orders'])->group(function () {
    Route::apiresource('carts', CartController::class)->middleware('auth:sanctum');
    Route::delete('cart/clear', [CartController::class, 'clear']);
});
