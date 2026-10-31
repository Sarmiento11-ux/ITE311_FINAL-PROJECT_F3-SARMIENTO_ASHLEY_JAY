# Laravel Backend Architecture Blueprint
## CraftMarket REST API Engine

### 1. Technology Requirements
- **PHP:** 8.2+ (CLI & Web server)
- **Framework:** Laravel 11.x
- **Authentication:** Laravel Sanctum (Bearer Token / Cookie session)
- **Database:** MySQL 8.x
- **Data Access:** Eloquent ORM + Form Request Validation

---

### 2. REST API Route Blueprint (`routes/api.php`)

```php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

Route::prefix('v1')->group(function () {
    
    // Public Endpoints
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Protected Routes (Sanctum Authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user', [AuthController::class, 'profile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        // Customer Routes
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/my-orders', [OrderController::class, 'customerOrders']);

        // Seller Routes (Role: Seller)
        Route::middleware('role:seller')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
            Route::get('/seller/orders', [OrderController::class, 'sellerOrders']);
        });

        // Administrator Routes (Role: Admin)
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/reports/sales', [PaymentController::class, 'salesReport']);
            Route::get('/admin/users', [AuthController::class, 'listAllUsers']);
        });
    });
});
```

---

### 3. How to Scaffold the Laravel Backend
Kapag handa na para sa backend setup, patakbuhin:
```powershell
# Sa loob ng c:\CraftMarket:
composer create-project laravel/laravel backend
cd backend
composer require laravel/sanctum
php artisan migrate
```
