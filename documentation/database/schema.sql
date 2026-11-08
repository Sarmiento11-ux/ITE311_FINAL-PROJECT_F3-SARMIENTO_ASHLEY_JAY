-- ========================================================
-- CraftMarket: Relational MySQL Database Schema Blueprint
-- Version: 1.1.0
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
--
-- NOTE: This file is a conceptual blueprint for documentation and
-- coursework diagrams. The canonical, executable schema lives in
-- backend/database/migrations/. When they disagree, trust the migrations.
--
-- Escrow ledger (implemented): orders and order_items carry escrow_status,
-- platform_fee, seller_payout, and escrow_released_at. Each order item also
-- stores a commission_rate snapshot (8% Basic / 3% Artisan Pro).
-- ========================================================

CREATE DATABASE IF NOT EXISTS craftmarket_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE craftmarket_db;

-- 1. ROLES TABLE (Administrator, Seller, Customer)
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    bio TEXT NULL,
    avatar_url VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    short_description VARCHAR(255) NULL,
    description TEXT NOT NULL,
    materials VARCHAR(255) NULL,
    dimensions VARCHAR(150) NULL,
    origin VARCHAR(150) NULL,
    image_url VARCHAR(255) NOT NULL,
    badge ENUM('None', 'Featured', 'Best Seller', 'Handcrafted', 'New Arrival') DEFAULT 'Handcrafted',
    status ENUM('draft', 'published', 'sold_out', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    commission_rate DECIMAL(5, 2) DEFAULT 8.00, -- 8% Marketplace Commission
    shipping_address TEXT NOT NULL,
    contact_phone VARCHAR(30) NOT NULL,
    order_notes TEXT NULL,
    status ENUM('pending', 'paid_in_escrow', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. ORDER_ITEMS TABLE
CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    seller_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_items_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. PAYMENTS TABLE
CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    payment_method ENUM('gcash', 'maya', 'credit_card', 'cod') NOT NULL,
    transaction_reference VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    escrow_status ENUM('held', 'released_to_seller', 'refunded_to_customer') DEFAULT 'held',
    payment_status ENUM('pending', 'successful', 'failed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ========================================================
-- SEED INITIAL SYSTEM DATA
-- ========================================================

INSERT INTO roles (name, slug, description) VALUES
('Administrator', 'admin', 'Full access to content moderation, user management, and financial escrow reports.'),
('Seller', 'seller', 'Artisan merchant capable of managing craft inventory and processing customer orders.'),
('Customer', 'customer', 'Conscious shoppers capable of browsing products, purchasing, and reviewing items.');

INSERT INTO categories (name, slug, description, image_url, display_order) VALUES
('Ceramics & Pottery', 'ceramics-pottery', 'Hand-thrown clay mugs, planters, vases and dining ware.', 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61', 1),
('Woven Goods & Bags', 'woven-goods-bags', 'Natural abaca totes, baskets and indigenous textiles.', 'https://images.unsplash.com/photo-1591561954557-26941169b49e', 2),
('Artisan Jewelry', 'artisan-jewelry', 'Handcrafted pearl bracelets, brass accessories and pendants.', 'https://images.unsplash.com/photo-1611652022419-a9419f74343d', 3),
('Home Decor & Candles', 'home-decor-candles', 'Botanical soy candles, macrame wall hangings and home accents.', 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8', 4),
('Wooden Crafts', 'wooden-crafts', 'Carved acacia kitchenware, trays and decorative wooden crafts.', 'https://images.unsplash.com/photo-1546554137-f86b9593a222', 5);
