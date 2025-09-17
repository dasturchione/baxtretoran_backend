<?php

use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminModifierController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DeliverController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Client\ProductCommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\PushController;
use App\Http\Controllers\Client\AddressController;
use App\Http\Controllers\Client\BannerController;
use App\Http\Controllers\Client\BranchController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\CategoryController;
use App\Http\Controllers\Client\UserController;
use App\Http\Controllers\Client\UserOrderController;
use App\Http\Controllers\Payments\PaymeController;
use App\Http\Controllers\SiteInfoController;

Route::prefix('auth')->group(function () {
    Route::post('/send-code', [AuthController::class, 'sendCode']);
    Route::post('/verify-code', [AuthController::class, 'verify']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::post('/getgeo-code', [GeoController::class, 'getAddress']);

Route::get('/banners', [BannerController::class, 'index']);
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/products-group-by-category', [ProductController::class, 'index']);
Route::get('/product/{slug}', [ProductController::class, 'show']);
Route::get('/product/comments/{id}', [ProductCommentController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/recommend/{productId}', [ProductController::class, 'recommend']);

Route::get('site-info', [SiteInfoController::class, 'show']);

Route::get('/vapid-key', function () {
    $keys = env('VAPID_PUBLIC_KEY');
    return response()->json(['public_key' => $keys]);
});

Route::middleware('auth:user')->group(function () {
    Route::post('user-update', [UserController::class, 'updateUserInfo']);
    Route::get('userinfo', [UserController::class, 'getUserInfo']);

    Route::get('/user-addresses', [AddressController::class, 'index']);
    Route::post('/user-addresses/create', [AddressController::class, 'store']);
    Route::delete('/user-addresses/delete/{id}', [AddressController::class, 'destroy']);

    Route::post('/product/comments/{id}', [ProductCommentController::class, 'store']);

    Route::get('/orders', [UserOrderController::class, 'index']);
    Route::get('/order/{id}', [UserOrderController::class, 'show']);
    Route::post('/orders/create', [UserOrderController::class, 'store']);
    Route::post('/orders/cancel/{id}', [UserOrderController::class, 'cancel']);

    Route::post('/push-subscription', [PushController::class, 'store']);
});

/// admin

Route::prefix('admin')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'employeeLogin']);
});

Route::prefix('admin')->middleware('auth:employee')->group(function () {
    Route::post('/auth/check-token', [AuthController::class, 'checkToken']);
    Route::get('/auth/permissions', [AuthController::class, 'me']);

    Route::get('/transactions/payme', [PaymeController::class, 'transactions'])->middleware('permission:banner_view');

    Route::get('/banners', [AdminBannerController::class, 'index'])->middleware('permission:banner_view');
    Route::get('/banners/show/{id}', [AdminBannerController::class, 'show'])->middleware('permission:banner_view');
    Route::post('/banners/create', [AdminBannerController::class, 'store'])->middleware('permission:banner_add');
    Route::post('/banners/edit/{id}', [AdminBannerController::class, 'update'])->middleware('permission:banner_edit');
    Route::delete('/banners/delete/{id}', [AdminBannerController::class, 'destroy'])->middleware('permission:banner_delete');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->middleware('permission:categories_view');
    Route::get('/categories/show/{id}', [AdminCategoryController::class, 'show'])->middleware('permission:categories_view');
    Route::post('/categories/create', [AdminCategoryController::class, 'store'])->middleware('permission:categories_add');
    Route::post('/categories/edit/{id}', [AdminCategoryController::class, 'update'])->middleware('permission:categories_edit');
    Route::delete('/categories/delete/{id}', [AdminCategoryController::class, 'destroy'])->middleware('permission:categories_delete');

    Route::get('/modifiers', [AdminModifierController::class, 'index'])->middleware('permission:modifier_view');
    Route::get('/modifiers/show/{id}', [AdminModifierController::class, 'show'])->middleware('permission:modifier_view');
    Route::post('/modifiers/create', [AdminModifierController::class, 'store'])->middleware('permission:modifier_add');
    Route::post('/modifiers/edit/{id}', [AdminModifierController::class, 'update'])->middleware('permission:modifier_edit');
    Route::delete('/modifiers/delete/{id}', [AdminModifierController::class, 'destroy'])->middleware('permission:modifier_delete');

    Route::get('/products', [AdminProductController::class, 'index'])->middleware('permission:product_view');
    Route::get('/products/show/{id}', [AdminProductController::class, 'show'])->middleware('permission:product_view');
    Route::post('/products/create', [AdminProductController::class, 'store'])->middleware('permission:product_add');
    Route::post('/products/edit/{id}', [AdminProductController::class, 'update'])->middleware('permission:product_edit');
    Route::delete('/products/delete/{id}', [AdminProductController::class, 'destroy'])->middleware('permission:product_delete');

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/order/view/{id}', [AdminOrderController::class, 'show']);
    Route::post('/order/status/{id}', [AdminOrderController::class, 'updateStatus']);
    Route::get('/orders/cancel/{id}', [AdminOrderController::class, 'cancel']);
    Route::post('/orders/assign', [AdminOrderController::class, 'assignCourier']);

    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/role/permissions/{role}', [RoleController::class, 'permissions']);
    Route::post('/role/permissions/{role}', [RoleController::class, 'syncPermissions']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::post('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy']);

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:permissions_view');
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::post('/permissions/{permission}', [PermissionController::class, 'update']);
    Route::delete('/permissions/delete/{id}', [PermissionController::class, 'destroy']);


    Route::get('/delivers', [DeliverController::class, 'index'])->middleware('permission:employee_view');
    Route::get('/delivers/show/{id}', [DeliverController::class, 'show'])->middleware('permission:employee_view');
    Route::post('/delivers/create', [DeliverController::class, 'store'])->middleware('permission:employee_add');
    Route::post('/delivers/edit/{id}', [DeliverController::class, 'update'])->middleware('permission:employee_edit');
    Route::delete('/delivers/delete/{id}', [DeliverController::class, 'destroy'])->middleware('permission:employee_edit');

    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employee_view');
    Route::get('/employees/show/{id}', [EmployeeController::class, 'show'])->middleware('permission:employee_view');
    Route::post('/employees/create', [EmployeeController::class, 'store'])->middleware('permission:employee_add');
    Route::post('/employees/edit/{id}', [EmployeeController::class, 'update'])->middleware('permission:employee_edit');
    Route::delete('/employees/delete/{id}', [EmployeeController::class, 'destroy'])->middleware('permission:employee_edit');

    Route::get('/users', [AdminUserController::class, 'index'])->middleware('permission:user_view');
    Route::get('/users/show/{id}', [AdminUserController::class, 'show'])->middleware('permission:user_view');
    Route::post('/users/create', [AdminUserController::class, 'store'])->middleware('permission:user_add');
    Route::post('/users/edit/{id}', [AdminUserController::class, 'update'])->middleware('permission:user_edit');

    Route::get('site-info', [SiteInfoController::class, 'show']);
    Route::post('site-info/edit', [SiteInfoController::class, 'update']);
});
