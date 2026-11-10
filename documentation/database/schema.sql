-- ============================================================
-- CraftMarket: Relational MySQL Database Schema
-- Version: 2.0.0 (aligned with backend/database/migrations)
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Engine: InnoDB
--
-- This file is the documentation-grade DDL snapshot of the live
-- schema. The executable source of truth is backend/database/migrations/.
-- Demo data is created by backend/database/seeders/.
-- ============================================================

CREATE DATABASE IF NOT EXISTS craftmarket_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE craftmarket_db;

-- ------------------------------------------------------------
-- 1. USERS (customers, sellers, administrators)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,                 -- bcrypt hash
    role VARCHAR(30) NOT NULL DEFAULT 'customer',   -- customer | seller | admin
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    subscription_plan VARCHAR(50) NOT NULL DEFAULT 'basic',     -- basic | pro
    subscription_status VARCHAR(50) NOT NULL DEFAULT 'active',  -- active | cancelled | expired
    subscription_expires_at TIMESTAMP NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 8.00,         -- 8.00 basic | 3.00 pro
    listing_limit INT NULL DEFAULT 10,                          -- 10 basic | NULL pro
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    avatar VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role),
    KEY idx_users_active (is_active)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. SELLER PROFILES (artisan storefront + verification)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS seller_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    shop_name VARCHAR(255) NOT NULL,
    shop_slug VARCHAR(255) NOT NULL,
    shop_description TEXT NULL,
    shop_logo VARCHAR(255) NULL,
    shop_banner VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    address TEXT NULL,
    contact_number VARCHAR(255) NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    verification_status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending | approved | rejected
    verification_notes TEXT NULL,
    verified_at TIMESTAMP NULL,
    verification_reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_seller_profiles_user (user_id),
    UNIQUE KEY uq_seller_profiles_slug (shop_slug),
    KEY idx_seller_verification (verification_status, is_verified),
    CONSTRAINT fk_seller_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. CATEGORIES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. PRODUCTS (artisan craft listings)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    images JSON NULL,                               -- additional gallery paths
    status VARCHAR(30) NOT NULL DEFAULT 'active',   -- active | inactive | out_of_stock | archived
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    material VARCHAR(255) NULL,
    dimensions VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_seller (seller_id),
    KEY idx_products_category (category_id),
    KEY idx_products_status (status),
    KEY idx_products_price (price),
    CONSTRAINT fk_products_seller
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. ORDERS (customer order header + escrow ledger)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,                -- buyer / customer
    order_number VARCHAR(50) NOT NULL,               -- CM-YYYYMMDD-XXXX
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',   -- pending | confirmed | processing | shipped | delivered | cancelled | refunded
    payment_method VARCHAR(255) NULL,
    payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid', -- unpaid | paid | refunded
    shipping_address TEXT NULL,
    contact_number VARCHAR(255) NULL,
    notes TEXT NULL,
    confirmed_at TIMESTAMP NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    escrow_status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending | held | released_to_seller | refunded | cancelled
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    seller_payout DECIMAL(10,2) NOT NULL DEFAULT 0,
    escrow_released_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_orders_number (order_number),
    KEY idx_orders_user (user_id),
    KEY idx_orders_status (status),
    KEY idx_orders_escrow (escrow_status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. ORDER ITEMS (per-artisan line items + commission snapshot)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    seller_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(255) NOT NULL,             -- snapshot at checkout
    unit_price DECIMAL(10,2) NOT NULL,              -- snapshot at checkout
    quantity INT NOT NULL,
    price DECIMAL(10,2) NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',  -- pending | confirmed | processing | shipped | delivered | cancelled
    tracking_number VARCHAR(255) NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 8.00,
    platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    seller_payout DECIMAL(10,2) NOT NULL DEFAULT 0,
    escrow_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    escrow_released_at TIMESTAMP NULL,
    dispatch_code VARCHAR(255) NULL,                -- QR dispatch hand-off code
    dispatched_at TIMESTAMP NULL,
    dispatch_confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_order_items_dispatch (dispatch_code),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    KEY idx_order_items_seller (seller_id),
    KEY idx_order_items_status (status),
    KEY idx_order_items_escrow (escrow_status),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_seller
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. PAYMENTS (PayMongo / COD audit trail)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    payment_method VARCHAR(255) NOT NULL,
    payment_gateway VARCHAR(255) NOT NULL DEFAULT 'paymongo', -- paymongo | cod
    payment_reference VARCHAR(255) NULL,
    checkout_session_id VARCHAR(255) NULL,
    checkout_url VARCHAR(255) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',  -- pending | paid | failed | cancelled | refunded
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_payments_reference (payment_reference),
    KEY idx_payments_order (order_id),
    KEY idx_payments_status (status),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. PRODUCT REVIEWS (verified purchase reviews)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,     -- 1..5
    comment TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_review_user_product (user_id, product_id),
    KEY idx_reviews_product (product_id),
    KEY idx_reviews_user (user_id),
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_order_item
        FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. CART ITEMS (persistent shopping cart)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_cart_user_product (user_id, product_id),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. WISHLISTS (saved products)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_wishlist_user_product (user_id, product_id),
    KEY idx_wishlist_user (user_id),
    KEY idx_wishlist_product (product_id),
    CONSTRAINT fk_wishlist_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. ARTISAN SUBSCRIPTIONS (Artisan Pro billing history)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS artisan_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    billing_cycle VARCHAR(20) NOT NULL DEFAULT 'monthly',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'GCash / Card',
    checkout_session_id VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY idx_subscriptions_user (user_id),
    KEY idx_subscriptions_status (status),
    CONSTRAINT fk_subscriptions_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. PAYOUT REQUESTS (seller cash-out from released escrow)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payout_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',  -- pending | approved | rejected | paid
    payout_method VARCHAR(255) NULL,
    account_name VARCHAR(255) NULL,
    account_number VARCHAR(255) NULL,
    notes TEXT NULL,
    admin_notes TEXT NULL,
    requested_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY idx_payout_seller_status (seller_id, status),
    CONSTRAINT fk_payout_seller
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. LARAVEL / SANCTUM SUPPORT TABLES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_pat_token (token),
    KEY idx_pat_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    KEY idx_sessions_user (user_id),
    KEY idx_sessions_activity (last_activity)
) ENGINE=InnoDB;

-- ============================================================
-- SEED REFERENCE DATA
-- Demo users, products, and orders are created by the Laravel
-- seeders (backend/database/seeders/DatabaseSeeder.php).
-- ============================================================

INSERT INTO categories (name, slug, description, image, is_active, created_at, updated_at) VALUES
('Ceramics & Pottery', 'ceramics-pottery', 'Hand-thrown clay mugs, planters, vases and dining ware.', NULL, 1, NOW(), NOW()),
('Woven Goods & Bags', 'woven-goods-bags', 'Natural abaca totes, baskets and indigenous textiles.', NULL, 1, NOW(), NOW()),
('Artisan Jewelry', 'artisan-jewelry', 'Handcrafted pearl bracelets, brass accessories and pendants.', NULL, 1, NOW(), NOW()),
('Home Decor & Candles', 'home-decor-candles', 'Botanical soy candles, macrame wall hangings and home accents.', NULL, 1, NOW(), NOW()),
('Wooden Crafts', 'wooden-crafts', 'Carved acacia kitchenware, trays and decorative wooden crafts.', NULL, 1, NOW(), NOW());
