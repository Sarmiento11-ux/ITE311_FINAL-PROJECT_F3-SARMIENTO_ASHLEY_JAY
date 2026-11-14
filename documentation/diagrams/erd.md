# CraftMarket — Entity Relationship Diagram (ERD)

**Project Title:** Startup-Oriented Web Application Development with CMS and System Integration (CraftMarket)  
**Courses:** Web Systems and Technologies 2 / System Integration and Architecture 1 (ITE311) — BSIT F3  
**Database:** MySQL 8.x / InnoDB, utf8mb4  
**Source of truth:** `backend/database/migrations/` (this document mirrors the executable schema)

---

## 1. Complete ERD

```mermaid
erDiagram
    USERS ||--o| SELLER_PROFILES : "has storefront"
    USERS ||--o{ PRODUCTS : "sells as artisan"
    CATEGORIES ||--o{ PRODUCTS : "classifies"
    USERS ||--o{ ORDERS : "places as customer"
    ORDERS ||--|{ ORDER_ITEMS : "contains"
    PRODUCTS ||--o{ ORDER_ITEMS : "appears in"
    USERS ||--o{ ORDER_ITEMS : "fulfils as seller"
    ORDERS ||--o{ PAYMENTS : "settles via"
    USERS ||--o{ PAYMENTS : "pays"
    USERS ||--o{ PRODUCT_REVIEWS : "writes"
    PRODUCTS ||--o{ PRODUCT_REVIEWS : "receives"
    ORDER_ITEMS ||--o| PRODUCT_REVIEWS : "verifies purchase"
    USERS ||--o{ CART_ITEMS : "owns cart"
    PRODUCTS ||--o{ CART_ITEMS : "added to"
    USERS ||--o{ WISHLISTS : "saves"
    PRODUCTS ||--o{ WISHLISTS : "saved in"
    USERS ||--o{ ARTISAN_SUBSCRIPTIONS : "subscribes"
    USERS ||--o{ PAYOUT_REQUESTS : "requests as seller"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "authenticates"

    USERS {
        bigint id PK
        string name
        string username UK "nullable public handle"
        string email UK
        timestamp email_verified_at
        string password "bcrypt hash"
        string role "customer | seller | admin"
        boolean is_active
        string subscription_plan "basic | pro"
        string subscription_status "active | cancelled | expired"
        timestamp subscription_expires_at
        decimal commission_rate "8.00 or 3.00"
        int listing_limit "10 or NULL"
        string phone
        text address
        string avatar
        string remember_token
        timestamp created_at
        timestamp updated_at
    }

    SELLER_PROFILES {
        bigint id PK
        bigint user_id FK
        string shop_name
        string shop_slug UK
        text shop_description
        string shop_logo
        string shop_banner
        string location
        text address
        string contact_number
        boolean is_verified
        boolean is_active
        string verification_status "pending | approved | rejected"
        text verification_notes
        timestamp verified_at
        timestamp verification_reviewed_at
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        bigint id PK
        string name
        string slug UK
        text description
        string image
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        bigint id PK
        bigint seller_id FK
        bigint category_id FK
        string name
        string slug UK
        text description
        decimal price
        int stock
        string image
        json images "additional gallery"
        string status "active | inactive | out_of_stock | archived"
        boolean is_featured
        string material
        string dimensions
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        bigint id PK
        bigint user_id FK "buyer"
        string order_number UK "CM-YYYYMMDD-XXXX"
        decimal subtotal
        decimal shipping_fee
        decimal total_amount
        string status "pending..delivered | cancelled | refunded"
        string payment_method
        string payment_status "unpaid | paid | refunded"
        text shipping_address
        string contact_number
        text notes
        timestamp confirmed_at
        timestamp shipped_at
        timestamp delivered_at
        string escrow_status "pending | held | released_to_seller | refunded | cancelled"
        decimal platform_fee
        decimal seller_payout
        timestamp escrow_released_at
        timestamp created_at
        timestamp updated_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        bigint seller_id FK
        string product_name "snapshot"
        decimal unit_price "snapshot"
        int quantity
        decimal price
        decimal subtotal
        string status "pending..delivered | cancelled"
        string tracking_number
        decimal commission_rate "snapshot 8.00 / 3.00"
        decimal platform_fee
        decimal seller_payout
        string escrow_status
        timestamp escrow_released_at
        string dispatch_code UK "QR hand-off code"
        timestamp dispatched_at
        timestamp dispatch_confirmed_at
        timestamp created_at
        timestamp updated_at
    }

    PAYMENTS {
        bigint id PK
        bigint order_id FK
        bigint user_id FK
        string payment_method
        string payment_gateway "paymongo | cod"
        string payment_reference UK
        string checkout_session_id
        string checkout_url
        decimal amount
        string status "pending | paid | failed | cancelled | refunded"
        timestamp paid_at
        timestamp created_at
        timestamp updated_at
    }

    PRODUCT_REVIEWS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        bigint order_item_id FK "verified purchase"
        tinyint rating "1-5"
        text comment
        timestamp created_at
        timestamp updated_at
    }

    CART_ITEMS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        int quantity
        timestamp created_at
        timestamp updated_at
    }

    WISHLISTS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        timestamp created_at
        timestamp updated_at
    }

    ARTISAN_SUBSCRIPTIONS {
        bigint id PK
        bigint user_id FK
        string plan_name "Artisan Pro"
        decimal amount "299.00"
        string billing_cycle "monthly | annual"
        string payment_method
        string checkout_session_id
        string status "active | cancelled | expired"
        timestamp starts_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    PAYOUT_REQUESTS {
        bigint id PK
        bigint seller_id FK
        decimal amount
        string status "pending | approved | rejected | paid"
        string payout_method
        string account_name
        string account_number
        text notes
        text admin_notes
        timestamp requested_at
        timestamp reviewed_at
        timestamp paid_at
        timestamp created_at
        timestamp updated_at
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token UK
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }
```

---

## 2. Relationship summary

| Parent | Child | Cardinality | Rule |
|---|---|---|---|
| `users` | `seller_profiles` | 1 : 0..1 | A seller account owns one storefront profile. |
| `users` | `products` | 1 : 0..* | Artisan listings are owned by one seller. |
| `categories` | `products` | 1 : 0..* | Each product belongs to one discipline. |
| `users` | `orders` | 1 : 0..* | A customer places many orders. |
| `orders` | `order_items` | 1 : 1..* | An order always has at least one line item. |
| `products` | `order_items` | 1 : 0..* | A product can be sold in many order items. |
| `users` | `order_items` | 1 : 0..* | Items are fulfilled by their respective artisan. |
| `orders` | `payments` | 1 : 0..* | Payment attempts/records are retained for audit. |
| `users` | `product_reviews` | 1 : 0..* | Customers write reviews. |
| `products` | `product_reviews` | 1 : 0..* | Products receive reviews. |
| `order_items` | `product_reviews` | 1 : 0..1 | A delivered item verifies the purchase. |
| `users` | `cart_items` | 1 : 0..* | Persistent shopping cart per customer. |
| `users` | `wishlists` | 1 : 0..* | Saved products per customer. |
| `users` | `artisan_subscriptions` | 1 : 0..* | Billing history for Artisan Pro. |
| `users` | `payout_requests` | 1 : 0..* | Released escrow cash-out requests. |

---

## 3. Integrity, normalization, and efficiency

### Referential integrity
- Foreign keys use `ON DELETE CASCADE` for dependent rows (profile, cart, wishlist, reviews, subscriptions, payout requests).
- Orders and order items use restrictive deletes so financial history is never lost; deleting an ordered product archives it instead.
- `order_items` stores **snapshots** of `product_name`, `unit_price`, `commission_rate`, `platform_fee`, and `seller_payout`. Historical financial records remain correct even if the product or commission rate later changes.

### Normalization
- The schema is in **Third Normal Form (3NF)**:
  - User identity, storefront details, product data, order headers, and order lines are separated.
  - Commission and payout values are derived once at checkout and stored on the line item (controlled denormalization for financial audit).
  - Cart and wishlist rows are junction tables with unique `(user_id, product_id)` constraints.

### Constraints and indexes
- Unique: `users.email`, `users.username`, `products.slug`, `categories.slug`, `orders.order_number`, `seller_profiles.shop_slug`, `order_items.dispatch_code`, `product_reviews(user_id, product_id)`, `cart_items(user_id, product_id)`, `wishlists(user_id, product_id)`.
- Indexed: payout `(seller_id, status)`, payments `order_id`, `status`, `payment_reference`, reviews `product_id`, `user_id`.
- State transitions are enforced in the controllers (for example delivery releases escrow; only approved payouts can be paid).

### Transactional consistency
- Order placement runs inside a database transaction: stock is validated and decremented, totals are recalculated server-side, and cart rows are cleared atomically.
- Escrow release, refunds, and cancellations update the order header and all line items in one transaction.
- Payout requests are validated against `released_earnings - reserved - paid`, preventing over-withdrawal.

---

## 4. Data dictionary highlights

| Table | Purpose | Key business rule |
|---|---|---|
| `users` | Identity and RBAC | Role is one of `customer`, `seller`, `admin`; inactive accounts cannot log in and tokens are revoked. |
| `seller_profiles` | Artisan storefront | Public listings require `verification_status = approved` and `is_verified = true`. |
| `products` | Craft catalog | Public catalog only shows `status = active` products from approved storefronts. |
| `orders` | Customer order header | Totals are recalculated server-side; escrow status tracks money flow. |
| `order_items` | Multi-seller line items | Each artisan fulfils their own items and receives their own payout snapshot. |
| `payments` | Gateway audit trail | PayMongo references and checkout sessions are retained. |
| `product_reviews` | Verified reviews | Review requires a delivered/completed order item for the same customer and product. |
| `payout_requests` | Seller cash-out | Funded only by released escrow; admin approval then settlement. |
| `artisan_subscriptions` | Pro billing history | ₱299/month recurring subscription records. |
