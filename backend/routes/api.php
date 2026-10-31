<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SellerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| CraftMarket REST API routes.
|
*/

// Health check / API info
Route::get('/', function () {
    return response()->json([
        'name'    => 'CraftMarket API',
        'version' => '1.0.0',
        'status'  => 'healthy',
    ]);
});

// Categories
Route::apiResource('categories', CategoryController::class);

// Products
Route::apiResource('products', ProductController::class);

// Users
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Orders
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

// Seller endpoints
Route::prefix('seller')->group(function () {
    Route::get('/products', [SellerController::class, 'products']);
    Route::get('/orders', [SellerController::class, 'orders']);
    Route::get('/stats', [SellerController::class, 'stats']);
    Route::get('/profile/{id}', [SellerController::class, 'profile']);
});

// Authenticated user (Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data'    => $request->user()->load('sellerProfile'),
    ]);
});
