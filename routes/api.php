<?php

use App\Http\Controllers\Client\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\CategoryController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\UserOrderController;
use App\Http\Controllers\GeoController;

Route::prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode']);
    Route::post('/verify-code', [AuthController::class, 'verify']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::post('/getgeo-code', [GeoController::class, 'getAddress']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/products-group-by-category', [ProductController::class, 'index']);
Route::get('/product/{slug}', [ProductController::class, 'show']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/recommend/{productId}', [ProductController::class, 'recommend']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-addresses', [AddressController::class, 'index']);
    Route::post('/user-addresses', [AddressController::class, 'store']);
    Route::delete('/user-addresses/{id}', [AddressController::class, 'destroy']);

    Route::post('/orders/create', [UserOrderController::class, 'create']);
});
