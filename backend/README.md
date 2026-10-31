# CraftMarket — Laravel 12 Backend & REST API

Official backend for **CraftMarket** (Local Handmade Products Marketplace).

- **Student Developer:** Ashley Jay Sarmiento (BSIT-F3)
- **Course:** ITE311 Final Project
- **Stack:** PHP 8.2+ • Laravel 12.70 • MySQL / SQLite • Laravel Sanctum

---

## 1. Architecture Overview

```
backend/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── CategoryController.php   # Category listing and management
│   │   ├── ProductController.php    # Product catalog, filtering, search, CRUD
│   │   ├── UserController.php       # User profiles & management
│   │   ├── OrderController.php      # Order placement & status updates
│   │   └── SellerController.php     # Seller products, orders & stats
│   └── Models/
│       ├── User.php                 # User with Sanctum tokens, roles, profile
│       ├── Category.php             # Product category
│       ├── Product.php              # Artisan item with price, stock, seller_id
│       ├── Order.php                # Customer order header
│       ├── OrderItem.php            # Order line item snapshot
│       └── SellerProfile.php        # Shop details, verification, logo
├── config/
│   └── cors.php                     # CORS configured for Vue.js frontend
├── database/
│   ├── migrations/                  # Schema migrations for all entities
│   └── seeders/                     # Seeders for categories, users, products, orders
└── routes/
    └── api.php                      # REST API routes under /api
```

---

## 2. Database Entities & Relationships

| Entity | Relationships |
|---|---|
| **User** | Has One `SellerProfile`, Has Many `Order` (as customer), Has Many `Product` (as seller) |
| **SellerProfile** | Belongs To `User` |
| **Category** | Has Many `Product` |
| **Product** | Belongs To `Category`, Belongs To `User` (seller), Has Many `OrderItem` |
| **Order** | Belongs To `User` (customer), Has Many `OrderItem` |
| **OrderItem** | Belongs To `Order`, Belongs To `Product`, Belongs To `User` (seller) |

---

## 3. REST API Endpoints

All responses follow a consistent JSON envelope:
```json
{
  "success": true,
  "message": "Human readable message",
  "data": { ... }
}
```

### 🏷️ Categories
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/categories` | List active categories with product counts |
| `GET` | `/api/categories/{id}` | Get single category with its products |
| `POST` | `/api/categories` | Create a category |
| `PUT` | `/api/categories/{id}` | Update category |
| `DELETE` | `/api/categories/{id}` | Delete category |

### 🏺 Products
| Method | Endpoint | Query Parameters / Description |
|---|---|---|
| `GET` | `/api/products` | `?search=mug&category=ceramics-pottery&sort=price_asc&featured=1` |
| `GET` | `/api/products/{id}` | Get product details with seller and category info |
| `POST` | `/api/products` | Create product (seller_id, name, price, stock, image, etc.) |
| `PUT` | `/api/products/{id}` | Update existing product |
| `DELETE` | `/api/products/{id}` | Delete product |

### 👤 Users & Profiles
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/users` | List users (supports `?role=seller` or `?role=customer`) |
| `GET` | `/api/users/{id}` | User details with seller profile and order history |
| `PUT` | `/api/users/{id}` | Update user profile info (name, email, phone, address) |

### 📦 Orders
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/orders` | List orders (supports `?user_id=1` or `?status=pending`) |
| `GET` | `/api/orders/{id}` | Order details with order items snapshot |
| `POST` | `/api/orders` | Place a new order with items array |
| `PATCH` | `/api/orders/{id}/status` | Update order status (`pending`, `confirmed`, `delivered`, etc.) |

### 🏪 Seller Dashboard
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/seller/products?seller_id={id}` | Seller's inventory |
| `GET` | `/api/seller/orders?seller_id={id}` | Items ordered from this seller |
| `GET` | `/api/seller/stats?seller_id={id}` | Summary counts: total sales, orders, products |
| `GET` | `/api/seller/profile/{id}` | Seller shop profile |

---

## 4. Setup & Running Instructions

### Prerequisites
- PHP 8.2+
- Composer
- MySQL (via Laragon, XAMPP, or standalone MySQL server)

### Step-by-Step Setup

1. **Install dependencies (already installed):**
   ```bash
   composer install
   ```

2. **Configure Database in `.env`:**
   For MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=craftmarket_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Create the MySQL database:**
   In phpMyAdmin, HeidiSQL, or MySQL CLI:
   ```sql
   CREATE DATABASE craftmarket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start the Laravel API Server:**
   ```bash
   php artisan serve
   ```
   The backend API will be running at `http://127.0.0.1:8000`.

### Demo Accounts Seeded
- **Customer:** `maria@example.com` / `password` (Maria Clara Santos)
- **Seller 1:** `artisan@example.com` / `password` (Elena Bautista / Cordillera Weaves)
- **Seller 2:** `clayworks@example.com` / `password` (Marco Gomez / Studio Clayworks)
- **Admin:** `admin@example.com` / `admin123`
