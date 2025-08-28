<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\PushController;
use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\CategoryController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\UserOrderController;

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
    Route::post('user-aupdate', [UserController::class, 'updateUserInfo']);
    Route::get('userinfo', [UserController::class, 'getUserInfo']);
    Route::get('/user-addresses', [AddressController::class, 'index']);
    Route::post('/user-addresses', [AddressController::class, 'store']);
    Route::delete('/user-addresses/{id}', [AddressController::class, 'destroy']);

    Route::get('/orders', [UserOrderController::class, 'index']);
    Route::get('/order/{id}', [UserOrderController::class, 'show']);
    Route::post('/orders/create', [UserOrderController::class, 'create']);
    Route::post('/orders/cancel/{id}', [UserOrderController::class, 'cancel']);

    Route::post('/push-subscription', [PushController::class,'store']);
});

Route::get('/vapid-key', function () {
    $keys = env('VAPID_PUBLIC_KEY');
    return response()->json(['public_key' => $keys]);
});
