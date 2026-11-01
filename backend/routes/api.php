<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ProductReviewController;

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

// ─── PayMongo Webhook (NO auth middleware — PayMongo calls this directly) ─────
// Must be outside the auth:sanctum group. Signature is verified inside the controller.
Route::post('/webhooks/paymongo', [WebhookController::class, 'handlePayMongo']);

// ─── Public Auth Routes ───────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login'])->name('login');
});

// ─── Public Read-Only Routes (Products / Categories) ─────────────────────────
// Products and categories are publicly browsable (no login required)
Route::get('/categories',      [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/products',        [ProductController::class, 'index']);
Route::get('/products/{id}',   [ProductController::class, 'show']);

// Product reviews: publicly readable, auth required to write
Route::get('/products/{productId}/reviews', [ProductReviewController::class, 'index']);

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

    // ─── Customer: Payments (PayMongo checkout sessions) ─────────────────
    Route::post('/payments/checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payments/status/{orderId}',  [PaymentController::class, 'getPaymentStatus']);

    // ─── Customer: Product Reviews ───────────────────────────────────────────────
    Route::get('/products/{productId}/reviews/eligibility', [ProductReviewController::class, 'checkEligibility']);
    Route::get('/products/{productId}/reviews/my',          [ProductReviewController::class, 'myReview']);
    Route::post('/products/{productId}/reviews',            [ProductReviewController::class, 'store']);
    Route::put('/products/{productId}/reviews/{reviewId}',  [ProductReviewController::class, 'update']);
    Route::delete('/products/{productId}/reviews/{reviewId}', [ProductReviewController::class, 'destroy']);

    // ─── Seller / Admin: Product management ──────────────────────────────
    Route::middleware('role:seller,admin')->group(function () {
        Route::post('/products',         [ProductController::class, 'store']);
        Route::put('/products/{id}',     [ProductController::class, 'update']);
        Route::delete('/products/{id}',        [ProductController::class, 'destroy']);
        Route::post('/products/{id}/image',    [ProductController::class, 'uploadImage']);
        Route::delete('/products/{id}/image',  [ProductController::class, 'deleteImage']);
    });

    // ─── Seller: Seller dashboard endpoints ──────────────────────────────
    Route::middleware('role:seller,admin')->prefix('seller')->group(function () {
        Route::get('/products',                 [SellerController::class, 'products']);
        Route::get('/orders',                   [SellerController::class, 'orders']);
        Route::get('/stats',                    [SellerController::class, 'stats']);
        Route::get('/reviews',                  [ProductReviewController::class, 'sellerReviews']);
        Route::get('/profile/{id}',             [SellerController::class, 'profile']);
        Route::patch('/order-items/{id}/status', [SellerController::class, 'updateItemStatus']);
    });

    // ─── Admin: Full administrative endpoints (strictly role:admin) ──────
    Route::middleware('role:admin')->group(function () {
        // Dedicated admin overview, reports & seller oversight
        Route::prefix('admin')->group(function () {
            Route::get('/stats',                   [AdminController::class, 'stats']);
            Route::get('/recent-activity',         [AdminController::class, 'recentActivity']);
            Route::get('/reports',                 [AdminController::class, 'reports']);
            Route::get('/sellers',                 [AdminController::class, 'sellers']);
            Route::get('/sellers/{id}',            [AdminController::class, 'sellerDetails']);
            Route::get('/reviews',                 [ProductReviewController::class, 'adminIndex']);
            Route::delete('/reviews/{reviewId}',   [ProductReviewController::class, 'destroy'])
                ->defaults('productId', 0);
        });

        // Admin order management & status updates
        Route::patch('/orders/{id}/status',       [OrderController::class, 'updateStatus']);

        // Admin category CRUD
        Route::post('/categories',                [CategoryController::class, 'store']);
        Route::put('/categories/{id}',            [CategoryController::class, 'update']);
        Route::delete('/categories/{id}',         [CategoryController::class, 'destroy']);

        // Admin user management & activation
        Route::get('/users',                      [UserController::class, 'index']);
        Route::get('/users/{id}',                 [UserController::class, 'show']);
        Route::put('/users/{id}',                 [UserController::class, 'update']);
        Route::patch('/users/{id}/toggle-active',  [UserController::class, 'toggleActive']);
    });

});
