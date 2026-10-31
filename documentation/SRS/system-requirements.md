# Software Requirements Specification (SRS)
## CraftMarket: Local Handmade Products Marketplace

### 1. System Overview
CraftMarket is an enterprise-grade digital marketplace designed to bridge independent Filipino artisans, weavers, potters, and woodcrafters directly with conscious shoppers. The system is architected as a decoupled solution combining:
- **Frontend Presentation:** Vue.js 3 + Vite Single Page Application (SPA)
- **Core Business Backend:** Laravel 11/PHP RESTful API with MySQL
- **Content Management:** Headless WordPress via WP REST API

---

### 2. User Roles & Access Control
1. **Administrator:**
   - Full content moderation and approval of artisan storefronts
   - Management of user profiles and role assignments
   - Monitoring of order lifecycles and 8% commission ledger
   - Financial audit and payout release from escrow
   - Publishing community announcements and platform banners

2. **Seller (Artisan / Creator):**
   - Self-service seller registration and profile customization
   - Creation, modification, and inventory management of craft listings
   - Order receipt, fulfillment tracking, and QR dispatch confirmation
   - Workshop location and bio storytelling
   - Revenue analytics and payout requests

3. **Customer (Shopper):**
   - Account creation and authentication
   - Product catalog browsing with live multi-filter and keyword search
   - Detailed craft specification inspection
   - Shopping cart management and escrow-protected checkout
   - Order tracking and post-delivery review publishing

---

### 3. Core System Modules
1. **Product Management Module:**
   - Categorization by discipline (Ceramics, Woven Goods, Jewelry, Candles, Woodcrafts)
   - Real-time stock decrement upon confirmed order
   - Multi-photo gallery and craft provenance metadata

2. **Order & Escrow Management Module:**
   - Unique order generation (`CM-YYYYMMDD-XXXX`)
   - Escrow holding status (`held` -> `released_to_seller` upon verified delivery)
   - Commission split calculation (92% artisan, 8% platform fee)

3. **User & Authentication Module:**
   - Token-based authentication using Laravel Sanctum
   - Role-Based Access Control (RBAC) middleware
   - Session persistence and secure password hashing (Bcrypt)

4. **CMS Content Integration Module:**
   - WordPress headless integration for community news and artisan feature articles
   - Decoupled from financial database to protect transactional integrity
