<?php

use App\Http\Controllers\Client\AddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\ProductController;

Route::prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode']);
    Route::post('/verify-code', [AuthController::class, 'verify']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::post('/getget-code', );

Route::get('/products-group-by-category', [ProductController::class, 'index']);
Route::get('/product/{slug}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-addresses', [AddressController::class, 'index']);
    Route::post('/user-addresses', [AddressController::class, 'store']);
    Route::delete('/user-addresses/{id}', [AddressController::class, 'destroy']);
});
