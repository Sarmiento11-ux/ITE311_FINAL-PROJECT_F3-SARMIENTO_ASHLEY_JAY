<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SellerController;

/*
|--------------------------------------------------------------------------
| API Routes — CraftMarket
|--------------------------------------------------------------------------
*/

// ─── Health check ────────────────────────────────────────────────────────────
Route::get('/', function () {
    return response()->json([
        'name'    => 'CraftMarket API',
        'version' => '1.0.0',
        'status'  => 'healthy',
    ]);
});

// ─── Public Auth Routes ───────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ─── Public Read-Only Routes (Products / Categories) ─────────────────────────
// Products and categories are publicly browsable (no login required)
Route::get('/categories',      [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/products',        [ProductController::class, 'index']);
Route::get('/products/{id}',   [ProductController::class, 'show']);

// ─── Authenticated Routes (any logged-in user) ───────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth: logout, me, profile update, password change
    Route::post('/auth/logout',          [AuthController::class, 'logout']);
    Route::get('/auth/me',               [AuthController::class, 'me']);
    Route::put('/auth/profile',          [AuthController::class, 'updateProfile']);
    Route::put('/auth/password',         [AuthController::class, 'changePassword']);

    // Legacy /user endpoint (kept for backwards compatibility)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data'    => $request->user()->load('sellerProfile'),
        ]);
    });

    // ─── Customer: Shopping Cart (persistent MySQL cart) ────────────────
    Route::get('/cart',                [CartController::class, 'index']);
    Route::post('/cart',               [CartController::class, 'store']);
    Route::put('/cart/{id}',           [CartController::class, 'update']);
    Route::delete('/cart/{id}',        [CartController::class, 'destroy']);
    Route::delete('/cart',             [CartController::class, 'clear']);

    // ─── Customer: Orders (own orders only — enforced in controller) ──────
    Route::get('/orders',              [OrderController::class, 'index']);
    Route::get('/orders/{id}',         [OrderController::class, 'show']);
    Route::post('/orders',             [OrderController::class, 'store']);

    // ─── Seller / Admin: Product management ──────────────────────────────
    Route::middleware('role:seller,admin')->group(function () {
        Route::post('/products',         [ProductController::class, 'store']);
        Route::put('/products/{id}',     [ProductController::class, 'update']);
        Route::delete('/products/{id}',  [ProductController::class, 'destroy']);
    });

    // ─── Seller: Seller dashboard endpoints ──────────────────────────────
    Route::middleware('role:seller,admin')->prefix('seller')->group(function () {
        Route::get('/products',                 [SellerController::class, 'products']);
        Route::get('/orders',                   [SellerController::class, 'orders']);
        Route::get('/stats',                    [SellerController::class, 'stats']);
        Route::get('/profile/{id}',             [SellerController::class, 'profile']);
        Route::patch('/order-items/{id}/status', [SellerController::class, 'updateItemStatus']);
    });

    // ─── Admin: Order status update, category management, user management ─
    Route::middleware('role:admin')->group(function () {
        Route::patch('/orders/{id}/status',  [OrderController::class, 'updateStatus']);
        Route::post('/categories',           [CategoryController::class, 'store']);
        Route::put('/categories/{id}',       [CategoryController::class, 'update']);
        Route::delete('/categories/{id}',    [CategoryController::class, 'destroy']);
        Route::get('/users',                 [UserController::class, 'index']);
        Route::get('/users/{id}',            [UserController::class, 'show']);
        Route::put('/users/{id}',            [UserController::class, 'update']);
    });

});
