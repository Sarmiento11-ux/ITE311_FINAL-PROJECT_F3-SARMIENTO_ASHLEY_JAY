<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CmsBridgeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\SubscriptionController;

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
    // Security: brute-force protection on credential endpoints (per IP).
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
});

// ─── Public Read-Only Routes (Products / Categories) ─────────────────────────
// Products and categories are publicly browsable (guarded by rate limit)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/categories',      [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'getPlans']);
    Route::get('/products',         [ProductController::class, 'index']);
    Route::get('/products/filters', [ProductController::class, 'filters']);
    Route::get('/products/{id}',    [ProductController::class, 'show']);

    // Product reviews: publicly readable, auth required to write
    Route::get('/products/{productId}/reviews', [ProductReviewController::class, 'index']);

    // Shipping fee estimate (zone calculation)
    Route::get('/shipping/estimate', [OrderController::class, 'shippingEstimate']);
});

// Headless WordPress CMS Bridge Routes (secrets required — never public)
Route::prefix('cms-bridge')->middleware('cms.bridge')->group(function () {
    // Read: catalog, taxonomy, storefront applications, users, and reports.
    Route::get('/products',   [CmsBridgeController::class, 'products']);
    Route::get('/categories', [CmsBridgeController::class, 'categories']);
    Route::get('/sellers',    [CmsBridgeController::class, 'sellers']);
    Route::get('/users',      [UserController::class, 'index']);
    Route::get('/reports',    [AdminController::class, 'reports']);

    // Write: moderation only. Orders, payments, escrow, and payouts stay off-limits.
    Route::patch('/products/{id}/status',     [CmsBridgeController::class, 'updateProductStatus']);
    Route::patch('/users/{id}/toggle-active', [CmsBridgeController::class, 'toggleUser']);

    // Write: category taxonomy CRUD (delete is blocked while products exist).
    Route::post('/categories',        [CmsBridgeController::class, 'storeCategory']);
    Route::put('/categories/{id}',    [CmsBridgeController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [CmsBridgeController::class, 'deleteCategory']);

    // Write: artisan storefront verification decisions.
    Route::patch('/sellers/{id}/verification', [CmsBridgeController::class, 'updateSellerVerification']);
});

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

    // ─── Artisan Subscription & Tiers ───────────────────────────────────────
    Route::get('/seller/subscription',              [SubscriptionController::class, 'getCurrentSubscription']);
    Route::post('/seller/subscription/upgrade',     [SubscriptionController::class, 'upgradeToPro']);
    Route::get('/seller/subscription/verify',       [SubscriptionController::class, 'verifyPaymongoSession']);
    Route::post('/seller/subscription/cancel',      [SubscriptionController::class, 'cancelSubscription']);

    // ─── Customer: Shopping Cart (persistent MySQL cart) ────────────────
    Route::get('/cart',                [CartController::class, 'index']);
    Route::post('/cart',               [CartController::class, 'store']);
    Route::put('/cart/{id}',           [CartController::class, 'update']);
    Route::delete('/cart/{id}',        [CartController::class, 'destroy']);
    Route::delete('/cart',             [CartController::class, 'clear']);

    // ─── Customer: Wishlist & Favorites (persistent MySQL wishlist) ─────
    Route::middleware('role:customer')->group(function () {
        Route::get('/wishlist',                     [WishlistController::class, 'index']);
        Route::get('/wishlist/ids',                 [WishlistController::class, 'ids']);
        Route::get('/wishlist/status/{productId}',  [WishlistController::class, 'status']);
        Route::post('/wishlist',                    [WishlistController::class, 'store']);
        Route::delete('/wishlist/{productId}',      [WishlistController::class, 'destroy']);
        Route::delete('/wishlist',                  [WishlistController::class, 'clear']);
    });

    // ─── Customer / Admin: Orders & Status Updates (access rules enforced in controller) ──────
    Route::get('/orders',              [OrderController::class, 'index']);
    Route::get('/orders/{id}',         [OrderController::class, 'show']);
    Route::post('/orders',             [OrderController::class, 'store']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // ─── Customer: Payments (PayMongo checkout sessions) ─────────────────
    Route::post('/payments/checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payments/status/{orderId}',  [PaymentController::class, 'getPaymentStatus']);
    // Local-only sandbox settlement (guarded by environment + env flag).
    Route::post('/payments/simulate',         [PaymentController::class, 'simulatePayment']);

    // ─── Customer: Product Reviews ───────────────────────────────────────────────
    Route::get('/products/{productId}/reviews/eligibility', [ProductReviewController::class, 'checkEligibility']);
    Route::get('/products/{productId}/reviews/my',          [ProductReviewController::class, 'myReview']);
    Route::post('/products/{productId}/reviews',            [ProductReviewController::class, 'store']);
    Route::put('/products/{productId}/reviews/{reviewId}',  [ProductReviewController::class, 'update']);
    Route::delete('/products/{productId}/reviews/{reviewId}', [ProductReviewController::class, 'destroy']);

    // ─── Customer: Custom Craft Inquiry ──────────────────────────────────────────
    Route::post('/products/{id}/inquiry',                    [ProductController::class, 'submitInquiry']);

    // ─── Seller / Admin: Product management ──────────────────────────────
    Route::middleware('role:seller,admin')->group(function () {
        Route::post('/products',         [ProductController::class, 'store']);
        Route::put('/products/{id}',     [ProductController::class, 'update']);
        Route::patch('/products/{id}/featured', [ProductController::class, 'toggleFeatured']);
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
        Route::post('/order-items/{id}/dispatch-code',    [SellerController::class, 'generateDispatchCode']);
        Route::post('/order-items/{id}/dispatch-confirm', [SellerController::class, 'confirmDispatch']);
        Route::get('/payouts',                  [SellerController::class, 'payouts']);
        Route::post('/payouts',                 [SellerController::class, 'requestPayout']);
        Route::delete('/payouts/{id}',          [SellerController::class, 'cancelPayout']);
        Route::post('/verification-request',    [SellerController::class, 'requestVerification']);
    });

    // ─── Admin: Full administrative endpoints (strictly role:admin) ──────
    Route::middleware('role:admin')->group(function () {
        // Dedicated admin overview, reports & seller oversight
        Route::prefix('admin')->group(function () {
            Route::get('/subscriptions/metrics',   [SubscriptionController::class, 'getAdminSubscriptionMetrics']);
            Route::get('/stats',                   [AdminController::class, 'stats']);
            Route::get('/recent-activity',         [AdminController::class, 'recentActivity']);
            Route::get('/reports',                 [AdminController::class, 'reports']);
            Route::get('/products',                [ProductController::class, 'adminIndex']);
            Route::get('/sellers',                 [AdminController::class, 'sellers']);
            Route::get('/sellers/{id}',            [AdminController::class, 'sellerDetails']);
            Route::patch('/sellers/{id}/verification', [AdminController::class, 'updateSellerVerification']);
            Route::get('/payouts',                 [AdminController::class, 'payoutRequests']);
            Route::patch('/payouts/{id}',          [AdminController::class, 'updatePayoutRequest']);
            Route::patch('/orders/{id}/escrow',    [AdminController::class, 'releaseEscrow']);
            Route::get('/reviews',                 [ProductReviewController::class, 'adminIndex']);
            Route::delete('/reviews/{reviewId}',   [ProductReviewController::class, 'destroy'])
                ->defaults('productId', 0);
        });


        // Admin category CRUD
        Route::post('/categories',                [CategoryController::class, 'store']);
        Route::put('/categories/{id}',            [CategoryController::class, 'update']);
        Route::delete('/categories/{id}',         [CategoryController::class, 'destroy']);

        // Admin user management & activation
        Route::get('/users',                      [UserController::class, 'index']);
        Route::post('/users',                     [UserController::class, 'store']);
        Route::get('/users/{id}',                 [UserController::class, 'show']);
        Route::put('/users/{id}',                 [UserController::class, 'update']);
        Route::delete('/users/{id}',              [UserController::class, 'destroy']);
        Route::patch('/users/{id}/toggle-active',  [UserController::class, 'toggleActive']);
    });

});
