# Software Requirements Specification (SRS)

## CraftMarket — Startup-Oriented Web Application Development with CMS and System Integration

| Field | Detail |
|---|---|
| **Project Title** | Startup-Oriented Web Application Development with CMS and System Integration |
| **Application** | CraftMarket — Local Handmade Products Marketplace |
| **Courses** | Web Systems and Technologies 2 / System Integration and Architecture 1 (ITE311) |
| **Student** | Ashley Jay Sarmiento (BSIT F3) |
| **Document version** | 2.1 |
| **Status** | Implemented and verified |
| **Last updated** | November 2026 |

---

## 1. Introduction

### 1.1 Purpose

This SRS defines the functional and non-functional requirements of **CraftMarket**, a decoupled multi-vendor marketplace for Philippine handmade products. It is the reference for development, testing, defense evaluation, and future maintenance.

### 1.2 Objectives

In accordance with the project requirements for **Web Systems and Technologies 2** and **System Integration and Architecture 1**, the objectives of this application are:

1. **Design and Develop a Complete Web Application:** Build a responsive, production-ready full-stack application featuring a public storefront, customer portal, artisan workshop dashboard, and administrative console.
2. **Apply System Integration Concepts:** Implement decoupled multi-system communication across payment gateways (PayMongo hosted checkout and webhooks), physical dispatch verification (dynamic QR code generation), transactional notification channels (SMTP), and editorial CMS data bridges.
3. **Utilize a CMS Platform for Content and System Management:** Integrate headless WordPress to manage marketing pages, promotional announcement bars, and editorial journal stories decoupled from the transactional core.
4. **Implement Secure User Authentication and Authorization:** Provide token-based authentication (Laravel Sanctum), granular Role-Based Access Control (`customer`, `seller`, `admin`), password hashing (Bcrypt), input sanitization, and brute-force throttling.
5. **Design a Revenue-Generating Digital Business Solution:** Formulate a sustainable startup business model incorporating dual monetization streams (tiered transaction commissions with 8% Free Starter, 5% Basic Maker, and 3% Premium Pro rates, alongside tiered recurring subscriptions with monthly and annual billing).
6. **Present a Viable Startup/Business Concept:** Provide a complete business proposal, stakeholder value proposition, and architectural documentation for defense evaluation.

### 1.3 Scope

CraftMarket lets independent artisans open a verified storefront, list handmade crafts, receive and fulfil orders, monitor revenue, and cash out earnings. Shoppers can browse a curated catalog, manage a cart and wishlist, pay through an escrow-protected checkout, track delivery, and publish verified reviews. Administrators approve storefronts, moderate content, manage users and roles, audit escrow, and settle payouts.

**In scope**

- Account registration, authentication, and RBAC (`customer`, `seller`, `admin`)
- Storefront verification workflow
- Craft catalog with search, filtering, sorting, and pagination
- Cart, wishlist, checkout, shipping-fee policy
- PayMongo hosted checkout and signed webhooks; COD support
- Escrow ledger, commission snapshotting, refunds/cancellations
- Seller fulfilment, QR dispatch confirmation, revenue analytics, payout requests
- Admin oversight, moderation, reports, payout settlement
- Headless WordPress CMS bridge with shared-secret authentication
- Documentation set: SRS, use case, ERD, context, architecture, wireframes, schema

**Out of scope**

- Card data storage (delegated to PayMongo)
- Courier dispatch and last-mile logistics operations
- Actual bank/GCash disbursement rails
- Tax computation and statutory filing
- Native mobile applications
- Multi-currency pricing (single currency: PHP)

### 1.4 Definitions

| Term | Meaning |
|---|---|
| **Artisan / Seller** | Craft producer with a storefront profile |
| **Storefront verification** | Admin approval required before a shop's products are publicly visible |
| **Escrow** | Platform-held funds released to the seller only after delivery |
| **Commission** | Platform fee per line item: 8% Free Starter, 5% Basic Maker, 3% Premium Pro |
| **Payout** | Seller cash-out of released escrow earnings |
| **Tiered Subscriptions** | Free (₱0), Basic (₱149/mo or ₱1,490/yr), Premium Pro (₱299/mo or ₱2,990/yr) |
| **GMV** | Gross merchandise value (sum of item subtotals) |
| **QR dispatch** | Scannable hand-off code confirming courier turnover |
| **RBAC** | Role-Based Access Control |

### 1.5 References

- [`documentation/diagrams/use-case.md`](../diagrams/use-case.md)
- [`documentation/diagrams/erd.md`](../diagrams/erd.md)
- [`documentation/diagrams/context.md`](../diagrams/context.md)
- [`documentation/diagrams/architecture.md`](../diagrams/architecture.md)
- [`documentation/diagrams/wireframes.md`](../diagrams/wireframes.md)
- [`documentation/database/schema.sql`](../database/schema.sql)
- [`documentation/business-model.md`](../business-model.md)
- [`documentation/security/security-controls.md`](../security/security-controls.md)

---

## 2. Overall Description

### 2.1 Product perspective

CraftMarket is a **three-tier decoupled system**:

- **Presentation:** Vue 3 + Vite SPA (Vue Router 4, Axios, DOMPurify)
- **Application:** Laravel 12 REST API (PHP 8.2+), Sanctum tokens, RBAC middleware
- **Data:** MySQL 8 / InnoDB (SQLite for local development), migrations as source of truth
- **Content sidecar:** Headless WordPress using the WP REST API and a secured bridge
- **External:** PayMongo checkout + webhooks, SMTP email

### 2.2 User classes

| User class | Description | Primary needs |
|---|---|---|
| **Guest** | Unauthenticated visitor | Discover crafts, compare prices, read reviews, register |
| **Customer** | Registered shopper | Buy safely, track orders, save favorites, review purchases |
| **Seller** | Verified artisan | List products, fulfil orders, understand sales, get paid |
| **Administrator** | Platform operator | Trust and safety, user management, financial oversight |
| **WordPress CMS** | System actor | Read aggregate data and moderate product status / account activation |

### 2.3 Operating environment

| Component | Requirement |
|---|---|
| Browser | Latest Chrome, Edge, Firefox, Safari; responsive 360 px → 1920 px |
| Server | PHP 8.2+, Composer, Node 20+ for builds |
| Database | MySQL 8.x (production), SQLite (local/tests) |
| Web server | HTTPS reverse proxy + PHP-FPM (production), `artisan serve` (development) |
| Queue | Database queue driver (development), supervised worker (production) |
| External | PayMongo account with API keys and webhook secret |

### 2.4 Design constraints

- Single currency (PHP, `DECIMAL(10,2)`).
- All money-moving operations must be transactional.
- The API is stateless; the SPA authenticates with bearer tokens.
- No card data may be stored or transmitted through CraftMarket servers.
- Public catalog must never expose unverified storefronts.
- CMS bridge must never be publicly readable or writable without the shared secret.

### 2.5 Assumptions and dependencies

- Artisans have a valid payout account (GCash or bank).
- PayMongo remains the payment gateway.
- Couriers accept a dispatch code as proof of hand-off.
- Administrators review storefronts within a reasonable service window.
- Internet connectivity is available to both buyers and sellers.

---

## 3. Functional Requirements

Priority scale: **M** = Must have, **S** = Should have, **C** = Could have.

### 3.1 Authentication & Account Management

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-AUTH-01 | A guest can register with name, email, password, and optional phone/address. | M | Valid submission returns `201` with a token; duplicate email returns `422`; password is stored only as a Bcrypt hash. |
| FR-AUTH-02 | A registered user can log in with email and password. | M | Valid credentials return a Sanctum token and role; invalid credentials return `401` without revealing which field failed. |
| FR-AUTH-03 | Deactivated accounts cannot authenticate. | M | `is_active = false` returns `403` with a clear message; existing tokens are already revoked. |
| FR-AUTH-04 | Credential endpoints are rate limited. | M | More than 5 attempts per minute from one IP returns `429`. |
| FR-AUTH-05 | A user can log out, revoking the current token. | M | Token is deleted; subsequent requests with it return `401`. |
| FR-AUTH-06 | A user can view and update their own profile. | M | `GET/PUT /api/auth/profile` only affects the authenticated user. |
| FR-AUTH-07 | A user can change their password by supplying the current password. | M | Wrong current password returns `422`; success re-hashes and can revoke other tokens. |
| FR-AUTH-08 | Tokens expire after a configurable period. | S | Default `SANCTUM_EXPIRATION=10080` minutes (7 days); expired tokens return `401`. |

### 3.2 Catalog, Search & Reviews (Public)

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-CAT-01 | The public catalog lists only `active` products from approved, verified storefronts. | M | Products from `pending`/`rejected` shops never appear in list results. |
| FR-CAT-02 | Product detail is reachable only for publicly visible products. | M | Direct link to an unverified shop's product returns `404`. |
| FR-CAT-03 | Guests can search by keyword and filter by category, seller, price range, availability, and featured flag. | M | Filters combine correctly; invalid parameter values return `422`. |
| FR-CAT-04 | Filter facets reflect only publicly visible products. | M | Category/seller counts and price min/max exclude unverified shops. |
| FR-CAT-05 | Guests can read product reviews and ratings. | M | Reviews return with rating, comment, author, and verified-purchase marker. |
| FR-CAT-06 | The catalog supports pagination and sorting. | M | `per_page` bounded 1–100; page metadata includes total, from, to, has_more. |

### 3.3 Cart, Wishlist & Orders

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-ORD-01 | A customer can add, update, and remove cart items. | M | Cart rows are unique per `(user_id, product_id)`; quantity cannot exceed stock. |
| FR-ORD-02 | The cart persists across sessions and devices. | M | Cart is stored in MySQL, not only browser storage. |
| FR-ORD-03 | A customer can save and remove wishlist products. | M | Unique per `(user_id, product_id)`; list reflects saved items. |
| FR-ORD-04 | A customer can place an order from cart items. | M | Order is created with a unique `CM-YYYYMMDD-XXXX` number. |
| FR-ORD-05 | The server recalculates all prices and totals. | M | Client-supplied totals are ignored; totals derive from live product rows. |
| FR-ORD-06 | Stock is validated and decremented atomically. | M | Insufficient stock returns `422`; no partial order is persisted. |
| FR-ORD-07 | Order lines snapshot product name, unit price, and commission data. | M | Later product edits do not change historical order values. |
| FR-ORD-08 | Shipping is free at ₱1,500+ and ₱150 below the threshold. | M | `shipping_fee` and `total_amount` match the policy; policy is server-side. |
| FR-ORD-09 | Cart is cleared after a successful order. | M | Customer's cart is empty after checkout. |
| FR-ORD-10 | Customers can view their order history and per-item status. | M | Customers see only their own orders. |
| FR-ORD-11 | Delivery releases escrow for the delivered line items. | M | Item status `delivered` sets `escrow_status = released_to_seller` and `escrow_released_at`. |
| FR-ORD-12 | Cancellation/refund reverses escrow and restores stock where applicable. | M | Financial rows update transactionally; escrow becomes `refunded`/`cancelled`. |

### 3.4 Payments, Escrow & Payouts

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-PAY-01 | Cash on Delivery is supported. | M | Order is created with `payment_method`/gateway COD and settles on delivery. |
| FR-PAY-02 | Free Starter sellers are charged 8% commission per line item. | M | `platform_fee = subtotal × 0.08`, `seller_payout = subtotal × 0.92`. |
| FR-PAY-03a | Basic Maker sellers are charged 5% commission per line item. | M | `platform_fee = subtotal × 0.05`, `seller_payout = subtotal × 0.95`. |
| FR-PAY-03b | Premium Artisan Pro sellers are charged 3% commission per line item. | M | `platform_fee = subtotal × 0.03`, `seller_payout = subtotal × 0.97`. |
| FR-PAY-04 | Online payment uses a PayMongo hosted checkout session. | M | API returns a `checkout_url`; no card data touches CraftMarket. |
| FR-PAY-05 | PayMongo webhooks are verified before processing. | M | Missing/invalid HMAC signature returns `401`; valid event updates payment. |
| FR-PAY-06 | Successful payment marks the order confirmed and escrow held. | M | `payment_status = paid`, order `confirmed`, escrow `held`. |
| FR-PAY-07 | Webhook processing is idempotent. | M | Repeated events do not create duplicate payments or double-credit escrow. |
| FR-PAY-08 | Sellers can request a payout from released earnings. | M | Amount must be positive and ≤ released − reserved − paid. |
| FR-PAY-09 | Payout requests reserve the requested amount. | M | Available balance decreases while the request is `pending`. |
| FR-PAY-10 | Sellers can cancel a pending payout request. | M | Status becomes cancelled and the reserved amount is released. |
| FR-PAY-11 | Payout history is visible to the owning seller. | M | Sellers see only their own requests and statuses. |
| FR-PAY-12 | Admins can approve, reject, or mark a payout paid. | M | State machine enforces `pending → approved → paid` or `pending → rejected`. |
| FR-PAY-13 | Admins can manually release escrow for exceptions. | S | `PATCH /api/admin/orders/{id}/escrow` updates the escrow ledger with timestamps. |
| FR-PAY-14 | Reports separate commission revenue from pass-through shipping. | M | Commission/subscription totals exclude shipping fees. |
| FR-PAY-15 | Developers can demo the online payment flow without gateway keys. | C | Local/testing only, gated by `PAYMONGO_ALLOW_SIMULATION`; simulated payments are tagged `paymongo_sandbox` and never settle real money. Production still requires a valid PayMongo key. |

### 3.5 Seller Storefront, Listings & Fulfilment

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-SEL-01 | A seller can create, edit, archive, and delete own listings. | M | Ownership enforced by `seller_id`; admins may act on any listing. |
| FR-SEL-02 | Listing creation validates name, category, price, stock, description, and image. | M | Invalid data returns `422` with field-level messages. |
| FR-SEL-03 | Basic sellers are limited to 10 active listings. | M | 11th listing returns `422` prompting an upgrade; Pro is unlimited. |
| FR-SEL-04 | Product images are validated and stored with safe names. | M | Only JPEG/PNG/WEBP within size limits; UUID filename; no traversal. |
| FR-SEL-05 | New listings from unverified shops stay hidden publicly. | M | After approval they appear automatically in the catalog. |
| FR-SEL-06 | Sellers can update fulfilment status and confirm QR dispatch. | M | Status transitions and dispatch timestamps are recorded per item. |
| FR-SEL-07 | Sellers can view revenue analytics. | M | Response includes total sales, orders, AOV, this/last month revenue, growth %, six-month trend, top 5 crafts. |
| FR-SEL-08 | Analytics are seller-scoped. | M | A seller cannot read another seller's figures. |
| FR-SEL-09 | Sellers can view reviews for their products. | S | Seller review list is limited to their own items. |
| FR-SEL-10 | Sellers can view and update their storefront profile. | M | Shop name, bio, location, contact, and images are editable. |

### 3.6 Administrator

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-ADM-01 | Admins can view the storefront verification queue. | M | List supports search and status filtering. |
| FR-ADM-02 | Admins can approve or reject a storefront with notes. | M | `verification_status`, `is_verified`, `verified_at`, `verification_reviewed_at` update correctly. |
| FR-ADM-03 | Approval directly controls public visibility. | M | Approved shops' products appear; rejected shops' products do not. |
| FR-ADM-04 | Admins can view all products including unverified shops. | M | `GET /api/admin/products` returns the full oversight list. |
| FR-ADM-05 | Admins can moderate products, reviews, and categories. | M | Edit/archive/delete actions succeed; deleted reviews disappear publicly. |
| FR-ADM-06 | Admins can create, update, and deactivate categories. | M | Slug stays unique; inactive categories can be hidden from public facets. |
| FR-ADM-07 | Admins can search and filter users by role and active status. | M | Results include seller profile and counts. |
| FR-ADM-08 | Admins can assign roles (`customer`, `seller`, `admin`). | M | Role change persists and is auditable. |
| FR-ADM-09 | Admins cannot demote themselves, deactivate themselves, or demote the final admin. | M | Each attempt returns `422` with an explanatory message. |
| FR-ADM-10 | Promoting a customer to seller provisions a pending storefront. | M | A `seller_profiles` row is created with `verification_status = pending`. |
| FR-ADM-11 | Admins can view platform reports. | M | Reports include sales, orders, AOV, status summaries, best sellers, active sellers, commission ledger, payout summary, subscription revenue. |
| FR-ADM-12 | Admin actions that change privilege revoke active tokens. | M | Role change/deactivation forces re-login with new permissions. |

### 3.7 Reviews

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-REV-01 | Only customers with a delivered purchase can review. | M | Ineligible users receive `403`; eligibility endpoint reports the reason. |
| FR-REV-02 | Ratings are integers from 1 to 5. | M | Out-of-range or non-integer values return `422`. |
| FR-REV-03 | One review per customer per product. | M | Duplicate returns `422`; editing is supported. |
| FR-REV-04 | Reviews record the verifying `order_item_id`. | M | Public responses show a verified-purchase marker. |
| FR-REV-05 | Product rating and review count update automatically. | M | Catalog cards reflect the new average and count. |

### 3.8 Tiered Artisan Subscriptions (Free, Basic, Premium Pro)

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-SUB-01 | Guests and sellers can view Free, Basic, and Premium Pro plan pricing with monthly/annual options. | M | Free (₱0, 8%, 5 listings); Basic (₱149/mo or ₱1,490/yr, 5%, 25 listings); Premium Pro (₱299/mo or ₱2,990/yr, 3%, unlimited listings). |
| FR-SUB-02 | Sellers can upgrade to Basic or Premium Pro through PayMongo hosted checkout. | M | Successful verification updates plan, commission rate (5.00% or 3.00%), and listing limit (25 or NULL). |
| FR-SUB-03 | Subscription billing history is retained. | M | `artisan_subscriptions` records tier, amount, billing cycle (monthly/annual), status, and validity dates. |
| FR-SUB-04 | Sellers can cancel paid subscriptions and return to Free Starter. | M | Commission rate returns to `8.00%`, listing limit to `5`, with a clear confirmation dialog. |
| FR-SUB-05 | Admins can view tiered subscription metrics and MRR. | S | MRR combines active Pro and Basic subscribers; all-time subscription revenue and conversion rate reported. |

### 3.9 CMS Integration

| ID | Requirement | Priority | Acceptance criteria |
|---|---|---|---|
| FR-CMS-01 | The CMS bridge exposes catalog, users, and reports only with a valid shared secret. | M | Missing/wrong `X-CMS-Bridge-Key` returns `401`. |
| FR-CMS-02 | The bridge fails closed when no key is configured. | M | Returns `503` instead of serving data. |
| FR-CMS-03 | Editorial content is decoupled from transactional data. | M | WordPress cannot write orders, payments, or payouts. |
| FR-CMS-04 | Administrators can moderate a product's listing status from the CMS. | M | `PATCH /api/cms-bridge/products/{id}/status` accepts `active`, `inactive`, `out_of_stock`, `archived`; any other value returns `422`. |
| FR-CMS-05 | Administrators can activate or deactivate marketplace accounts from the CMS. | M | `PATCH /api/cms-bridge/users/{id}/toggle-active` updates `is_active`; deactivation revokes all Sanctum tokens; admin deactivation returns `422`. |
| FR-CMS-06 | The CMS reports page exposes revenue, escrow, payout, and subscription summaries. | M | `GET /api/cms-bridge/reports` returns `total_sales`, `total_commission`, `commission_ledger`, `payout_summary`, and `subscription_revenue`. |
| FR-CMS-07 | Every CMS write action is protected against CSRF and unauthorized use. | M | WordPress handlers require a valid nonce and `manage_options`; Laravel validates all input. |

---

## 4. Non-Functional Requirements

### 4.1 Security

| ID | Requirement | Acceptance criteria |
|---|---|---|
| NFR-SEC-01 | Passwords must be hashed with Bcrypt and never serialized. | `password` in `$hidden`; stored value is a hash. |
| NFR-SEC-02 | Credential endpoints must resist brute force. | `throttle:5,1` returns `429` when exceeded. |
| NFR-SEC-03 | RBAC must be enforced server-side on every protected route. | Guest `401`, wrong role `403` tests pass. |
| NFR-SEC-04 | Unverified storefronts must not be publicly enumerable. | List and detail return no unverified products (`404` on detail). |
| NFR-SEC-05 | Payment webhooks must be signature-verified. | Invalid signature is rejected and logged. |
| NFR-SEC-06 | CMS bridge must use a shared secret and constant-time comparison. | `hash_equals` implementation; `401`/`503` tests pass. |
| NFR-SEC-07 | All user input must be validated server-side. | Invalid payloads return `422` with field errors. |
| NFR-SEC-08 | File uploads must be type/size restricted with safe names. | Non-image and oversized files rejected; UUID filenames used. |
| NFR-SEC-09 | Tokens must be revocable on privilege change. | Tokens deleted on role change/deactivation. |
| NFR-SEC-10 | Production secrets must not be committed. | `.env` gitignored; `.env.example` holds placeholders. |

### 4.2 Performance & Scalability

| ID | Requirement | Acceptance criteria |
|---|---|---|
| NFR-PERF-01 | Catalog listing should respond within 500 ms at p95 for 1,000 products locally. | Paginated, indexed queries; eager loading avoids N+1. |
| NFR-PERF-02 | Seller analytics should return in a single request. | `/api/seller/stats` includes trend and top products. |
| NFR-PERF-03 | The SPA should remain usable when the API is offline. | Sample-data fallback renders the catalog/home. |
| NFR-PERF-04 | The frontend production build should succeed and be cacheable. | `npm run build` completes; hashed asset filenames. |
| NFR-SCALE-01 | The API must be stateless to allow horizontal scaling. | No server-side session dependency for API auth. |
| NFR-SCALE-02 | Images must be offloadable to object storage/CDN. | Storage abstraction supports local and S3-compatible disks. |

### 4.3 Reliability & Data Integrity

| ID | Requirement | Acceptance criteria |
|---|---|---|
| NFR-REL-01 | Money-moving operations must be atomic. | Orders, escrow, refunds, and payouts run in DB transactions. |
| NFR-REL-02 | Financial history must be immutable after checkout. | Line-item price/commission snapshots never change with catalog edits. |
| NFR-REL-03 | Stock must not go negative under concurrent checkout. | `lockForUpdate()` plus server validation. |
| NFR-REL-04 | Referential integrity must be enforced. | Foreign keys and unique constraints in migrations. |

### 4.4 Usability & Accessibility

| ID | Requirement | Acceptance criteria |
|---|---|---|
| NFR-USE-01 | The UI must be responsive from 360 px to 1920 px. | No horizontal overflow; grids collapse; tables scroll. |
| NFR-USE-02 | Every async action must expose loading/empty/error states. | Skeletons, empty-state copy, and retry banners exist. |
| NFR-USE-03 | Interactive targets should be at least 44×44 px on touch. | Mobile nav, buttons, and icon actions meet the size. |
| NFR-USE-04 | Colour contrast should meet WCAG AA for body text. | Forest/ink on cream meets contrast targets. |
| NFR-USE-05 | Destructive actions require confirmation. | Admin deactivation, role change, and payout decisions confirm first. |

### 4.5 Maintainability & Compatibility

| ID | Requirement | Acceptance criteria |
|---|---|---|
| NFR-MAIN-01 | The database schema must be reproducible from migrations. | `php artisan migrate:fresh --seed` works from scratch. |
| NFR-MAIN-02 | The API must return a consistent envelope. | `{ success, message, data }` across endpoints. |
| NFR-MAIN-03 | Critical flows must be covered by automated tests. | Feature tests pass for escrow, payouts, verification, and RBAC. |
| NFR-COMP-01 | The SPA must support current evergreen browsers. | Build targets modern ES modules; no legacy polyfills required. |
| NFR-COMP-02 | The API must run on PHP 8.2+ with Laravel 12. | Composer platform requirements satisfied. |

---

## 5. External Interface Requirements

### 5.1 User interfaces

- Responsive Vue SPA with public, customer, seller, and admin areas.
- Role-aware navigation; unauthorized routes redirect to login or a 403 page.
- Wireframes: [`documentation/diagrams/wireframes.md`](../diagrams/wireframes.md).

### 5.2 API interfaces (representative)

| Method | Endpoint | Auth | Purpose |
|---|---|---|---|
| `GET` | `/api/products` | Public | Catalog list (approved storefronts only) |
| `GET` | `/api/products/filters` | Public | Facet counts and price range |
| `GET` | `/api/products/{id}` | Public | Product detail (404 if not publicly visible) |
| `POST` | `/api/auth/register` | Public + throttle | Register |
| `POST` | `/api/auth/login` | Public + throttle | Login |
| `GET/POST/PUT/DELETE` | `/api/cart` | Customer | Cart management |
| `GET/POST/DELETE` | `/api/wishlist` | Customer | Wishlist management |
| `GET/POST` | `/api/orders` | Customer | Order history / place order |
| `PATCH` | `/api/orders/{id}/status` | Buyer/Seller/Admin | Status transition |
| `POST` | `/api/payments/checkout-session` | Customer | PayMongo checkout |
| `POST` | `/api/webhooks/paymongo` | Signature | Gateway callback |
| `GET` | `/api/seller/stats` | Seller/Admin | Revenue analytics |
| `GET/POST/DELETE` | `/api/seller/payouts` | Seller/Admin | Payout requests |
| `GET` | `/api/admin/products` | Admin | Full product oversight |
| `PATCH` | `/api/admin/sellers/{id}/verification` | Admin | Approve/reject storefront |
| `GET` | `/api/admin/reports` | Admin | Platform reports |
| `PATCH` | `/api/admin/payouts/{id}` | Admin | Settle payout |
| `GET/PUT` | `/api/users/{id}` | Admin | User management / role assignment |
| `GET` | `/api/cms-bridge/products` | Shared secret | CMS catalog |
| `PATCH` | `/api/cms-bridge/products/{id}/status` | Shared secret | CMS product moderation |
| `GET` | `/api/cms-bridge/users` | Shared secret | CMS user directory |
| `PATCH` | `/api/cms-bridge/users/{id}/toggle-active` | Shared secret | CMS account activation |
| `GET` | `/api/cms-bridge/reports` | Shared secret | CMS reporting |

### 5.3 Software interfaces

| Interface | Protocol | Data |
|---|---|---|
| PayMongo | HTTPS REST + webhooks | Checkout sessions, payment events |
| WordPress | HTTPS REST | Catalog/users/reports, product-status and account moderation, editorial content |
| SMTP | SMTP/TLS | Transactional email |
| Database | MySQL protocol | Persistent relational data |

### 5.4 Hardware interfaces

None beyond standard client devices and commodity servers.

---

## 6. Data Requirements

- **Engine:** InnoDB, `utf8mb4_unicode_ci`.
- **Core entities:** `users`, `seller_profiles`, `categories`, `products`, `orders`, `order_items`, `payments`, `product_reviews`, `cart_items`, `wishlists`, `artisan_subscriptions`, `payout_requests`, `personal_access_tokens`.
- **Integrity:** foreign keys with cascading deletes for dependent rows; restrictive handling for financial history.
- **Snapshots:** `order_items.product_name`, `unit_price`, `commission_rate`, `platform_fee`, `seller_payout`.
- **Uniques:** `users.email`, `products.slug`, `categories.slug`, `orders.order_number`, `seller_profiles.shop_slug`, `order_items.dispatch_code`, review/cart/wishlist `(user_id, product_id)`.
- **Retention:** orders, payments, escrow, and payouts are retained for audit; deletion is not exposed through the API.
- Full DDL: [`documentation/database/schema.sql`](../database/schema.sql).

---

## 7. Traceability Matrix

| Rubric area | Requirements | Artifacts | Evidence |
|---|---|---|---|
| **System Functionality (30%)** | FR-AUTH, FR-CAT, FR-ORD, FR-PAY, FR-SEL, FR-ADM, FR-REV, FR-SUB | Use case, architecture, wireframes | Feature tests + working SPA/API |
| **UI/UX & Responsiveness (15%)** | NFR-USE-01…05 | Wireframes, design tokens, responsive matrix | Responsive Vue views |
| **Business Model & Revenue (15%)** | FR-PAY-02/03/08/12, FR-SUB | `business-model.md` | Commission ledger, Pro MRR, payout summary in Reports |
| **Database Design (10%)** | FR-ORD-07, NFR-REL-01…04 | ERD + schema.sql | Migrations, FKs, snapshot columns |
| **Security & Validation (10%)** | FR-AUTH-04, FR-ADM-09/12, FR-CMS-01…07, NFR-SEC-01…10 | `security-controls.md` | `SecurityAndAuthorizationTest` (9 tests), `CmsBridgeTest` (6 tests) |
| **Documentation & System Design (10%)** | All | SRS, use case, ERD, context, architecture, schema, wireframes | This documentation set |
| **Presentation & Defense (10%)** | All | `presentation/defense-guide.md` | Demo script and Q&A prep |

---

## 8. Acceptance and Verification

### 8.1 Automated verification

```powershell
cd backend
php artisan test

cd ../frontend
npm run build
```

Current status:

| Suite | Result |
|---|---|
| `OrderEscrowTest` | 5 passed — commission split, Pro payout, escrow on payment, release on delivery, refund on cancel |
| `SellerPayoutAndVerificationTest` | 4 passed — storefront approval, payout balance rules, manual escrow release, QR dispatch |
| `SecurityAndAuthorizationTest` | 9 passed / 43 assertions — RBAC, role guards, catalog visibility, admin oversight, analytics, CMS bridge, reports |
| `CmsBridgeTest` | 6 passed — secret/fail-closed guard, catalog, product moderation, token revocation, admin protection |
| Frontend production build | Passed (chunk-size warning only) |

### 8.2 Manual acceptance scenarios

1. Approved seller's craft appears in the public catalog; a pending seller's craft does not.
2. A direct product URL for a pending shop returns `404`.
3. Checkout computes 8% commission for Basic and 3% for Pro.
4. Shipping is free at ₱1,500 and ₱150 below it.
5. Delivering an item releases escrow; cancelling a paid order refunds it.
6. A seller cannot request more than the released, unreserved balance.
7. An admin can approve a storefront and see it instantly in the public catalog.
8. An admin cannot demote themselves or the final administrator.
9. Promoting a user to seller creates a pending storefront and revokes old tokens.
10. The CMS bridge returns `401` without the secret, `503` when unconfigured, and `200` with it.
11. A CMS admin can archive a product and deactivate/reactivate a customer; admin deactivation returns `422`.
12. The SPA remains usable at 360 px, 768 px, and 1440 px widths.
13. Rate limiting returns `429` after repeated login attempts.

---

## 9. Future Enhancements

- Multi-factor authentication for sellers and admins
- Centralized audit log and anomaly alerts
- Automated courier API integration
- Featured-placement and annual Pro billing
- Referral credits and loyalty tiers
- Multi-currency and international shipping
- Native mobile app consuming the same API
- Two-person approval for large payouts
