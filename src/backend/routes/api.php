<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

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
