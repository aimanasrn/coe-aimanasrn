<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfilePhotoController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Middleware\EnsureTokenIsCurrent;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    Route::middleware(['auth:api', EnsureTokenIsCurrent::class])->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/profile', [AuthController::class, 'me']);
        Route::post('/avatar', [ProfilePhotoController::class, 'store'])->middleware('throttle:10,1');
        Route::delete('/avatar', [ProfilePhotoController::class, 'destroy'])->middleware('throttle:10,1');
        Route::get('/avatar/{filename}', [ProfilePhotoController::class, 'show'])->where('filename', '[A-Za-z0-9.-]+');
        Route::patch('/profile', [AuthController::class, 'updateProfile'])->middleware('throttle:10,1');
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:api', EnsureTokenIsCurrent::class, 'can:manage-catalog'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show'])
        ->whereNumber('category');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->whereNumber('category');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->whereNumber('category');

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->whereNumber('product');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->whereNumber('product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->whereNumber('product');
    Route::get('/products/{product}/images/{image}', [ProductController::class, 'image'])
        ->whereNumber(['product', 'image']);
});

Route::middleware(['auth:api', EnsureTokenIsCurrent::class, 'can:shop'])->group(function (): void {
    Route::get('/shop/categories', [ShopController::class, 'categories']);
    Route::get('/shop/products', [ShopController::class, 'index']);
    Route::get('/shop/products/{product}', [ShopController::class, 'show'])->whereNumber('product');
    Route::get('/shop/products/{product}/images/{image}', [ProductController::class, 'image'])->whereNumber(['product', 'image']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'store'])->middleware('throttle:60,1');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->whereNumber('product')->middleware('throttle:60,1');
    Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->whereNumber('product')->middleware('throttle:60,1');
    Route::delete('/cart', [CartController::class, 'clear'])->middleware('throttle:60,1');
});
