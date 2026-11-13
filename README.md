# CraftMarket — Local Handmade Products Marketplace

**Student Name:** Ashley Jay Sarmiento  
**Course and Section:** BSIT - F3  
**Project:** CraftMarket Web Application (ITE311 Final Project)

---

## 📌 Project Overview

**CraftMarket** is a modern, decoupled web marketplace application designed to connect local Filipino handmade artisans, weavers, potters, and crafters directly with conscious consumers. It provides micro-producers with an accessible digital storefront while safeguarding buyer confidence through escrow-based transactions and genuine craft curation.

---

## ✨ Key Features

- **Verified storefronts** — only admin-approved artisan shops appear in the public catalog, filters, and product detail pages.
- **Escrow-protected checkout** — funds are held and released per line item on delivery; cancellations and refunds reverse escrow.
- **Multi-vendor orders** — one order can contain items from several sellers, each with its own commission and payout snapshot.
- **Two revenue streams** — 8% Basic commission (3% on Artisan Pro) plus a ₱299/month Pro subscription.
- **Seller tools** — listing management, order fulfilment, QR dispatch confirmation, revenue analytics, and payout requests.
- **Admin tools** — storefront verification, full product oversight, user/role management, escrow audit, payout settlement, and platform reports.
- **Verified reviews** — only customers with a delivered purchase can review a product.
- **Secure by design** — Sanctum tokens, RBAC middleware, rate-limited auth, signed payment webhooks, and a fail-closed CMS bridge.

---

## 🧰 Decoupled Technology Stack

- **Frontend SPA:** Vue.js 3, Vite, Vue Router 4, Axios, DOMPurify, Custom Editorial Design System (CSS)
- **Backend API:** PHP 8.2+, Laravel 12, Laravel Sanctum (Token Auth)
- **Relational Database:** MySQL 8.x (InnoDB, Foreign Key Constraints) or SQLite for local development
- **Headless CMS:** WordPress (REST API for Community Announcements & Artisan Blogs)
- **Version Control:** Git & GitHub

---

## 📁 Project Directory Structure

```text
CraftMarket/
│
├── frontend/                          # [VUE.JS 3 + VITE SPA]
│   ├── public/                        # Static public assets
│   ├── src/
│   │   ├── assets/css/                # Design system & page stylesheets
│   │   ├── components/                # Reusable UI components (Navbar, cards, tables…)
│   │   ├── composables/               # Shared composition functions (useAuth)
│   │   ├── data/                      # Reactive client state (auth, cart, wishlist)
│   │   ├── router/                    # Vue Router 4 setup & route guards
│   │   ├── services/                  # Axios service layer for the Laravel API
│   │   ├── views/                     # Route views (Home, Products, dashboards…)
│   │   ├── App.vue                    # Root application component
│   │   └── main.js                    # Vue entry point & stylesheet imports
│   ├── index.html                     # Vite HTML entry point
│   ├── package.json                   # Dependencies (Vue 3, Vue Router 4, Vite)
│   └── vite.config.js                 # Vite configuration
│
├── backend/                           # [LARAVEL 12 REST API]
│   ├── app/                           # Controllers, models, middleware
│   ├── database/migrations/           # Schema migrations
│   ├── database/seeders/              # Demo accounts & sample catalog
│   ├── routes/api.php                 # REST API routes under /api
│   └── README.md                      # Backend setup, entities & endpoint reference
│
├── cms/                               # [WORDPRESS INTEGRATION]
│   ├── craftmarket-sync-plugin/       # Headless WP sync plugin
│   └── wordpress-integration-guide.md # Headless WP REST API documentation
│
├── documentation/                     # [PROJECT SPECIFICATIONS]
│   ├── README.md                      # Documentation index & rubric map
│   ├── SRS/system-requirements.md     # Requirements with IDs & acceptance criteria
│   ├── diagrams/
│   │   ├── use-case.md                # Use case diagram + 22 specifications
│   │   ├── erd.md                     # Mermaid ERD & integrity notes
│   │   ├── context.md                 # Level-0 DFD & trust boundaries
│   │   ├── architecture.md            # Layered architecture & deployment
│   │   └── wireframes.md              # Wireframes & responsive spec
│   ├── database/schema.sql            # Relational schema reference
│   ├── business-model.md              # Revenue, unit economics, break-even
│   ├── security/security-controls.md  # OWASP mapping & security controls
│   └── presentation/defense-guide.md  # Demo script & Q&A
│
├── assets/, pages/, index.html, style.css  # Legacy static prototype
└── README.md                          # Main project guide
```

> The root `index.html`, `style.css`, `assets/`, and `pages/` folders are the original static prototype. The active application lives in `frontend/` (Vue SPA) and `backend/` (Laravel API).

---

## 🚀 How to Run the Project Locally

### 1. Backend (Laravel API)

```powershell
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Configure the database in `backend/.env`. For MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=craftmarket_db
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then run the migrations and seeders:

```powershell
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

`php artisan storage:link` is required once per machine so seller-uploaded product
images in `backend/storage/app/public/products/` are served at
`http://127.0.0.1:8000/storage/products/<file>`.

The API runs at `http://127.0.0.1:8000` (health check: `http://127.0.0.1:8000/api`).

### 2. Frontend (Vue SPA)

```powershell
cd frontend
npm install
npm run dev
```

Open the displayed URL (typically `http://localhost:5173`) in your browser. To point the SPA at a different API host, create `frontend/.env.local`:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

If the API is offline, the catalog and home page automatically fall back to sample data so the UI remains usable.

### 3. Production build

```powershell
cd frontend
npm run build
npm run preview
```

---

## 🔑 Demo Accounts (after seeding)

| Role | Email | Password |
|---|---|---|
| Customer | `maria@example.com` | `password` |
| Seller (Cordillera Weaves) | `artisan@example.com` | `password` |
| Seller (Studio Clayworks) | `clayworks@example.com` | `password` |
| Admin | `admin@example.com` | `admin123` |

---

## 🧪 Testing

```powershell
cd backend
php artisan test
```

| Suite | Coverage |
|---|---|
| `OrderEscrowTest` | Commission split, Pro payout, escrow on payment, release on delivery, refund on cancellation |
| `SellerPayoutAndVerificationTest` | Storefront approval/rejection, payout balance rules |
| `SecurityAndAuthorizationTest` | RBAC (401/403), role guards, catalog visibility, admin oversight, seller analytics, CMS bridge secret, report summaries |

Frontend production build:

```powershell
cd frontend
npm run build
```

---

## 🔒 Security Notes

- Sanctum bearer tokens expire after **7 days** (`SANCTUM_EXPIRATION`).
- Role changes and deactivations **revoke all tokens**.
- Login and registration are rate limited to **5 requests/minute/IP**.
- Public catalog and product detail only expose **approved storefronts**.
- PayMongo webhooks are verified with **HMAC-SHA256**.
- The CMS bridge requires `X-CMS-Bridge-Key` and returns `503` if unset.
- Local sandbox payment simulation is **disabled by default**; it only runs with `APP_ENV=local`/`testing` **and** `PAYMONGO_ALLOW_SIMULATION=true`, and simulated records are tagged `paymongo_sandbox`.
- Set `CMS_BRIDGE_KEY`, `SANCTUM_EXPIRATION`, `CORS_ALLOWED_ORIGINS`, and PayMongo keys in `backend/.env`.

---

## 📚 Documentation

Start at the **[documentation index](documentation/README.md)** for the full rubric-mapped set.

| Document | Link |
|---|---|
| Documentation index & rubric map | [`documentation/README.md`](documentation/README.md) |
| Software Requirements Specification | [`documentation/SRS/system-requirements.md`](documentation/SRS/system-requirements.md) |
| Use case diagram & 22 specifications | [`documentation/diagrams/use-case.md`](documentation/diagrams/use-case.md) |
| Entity Relationship Diagram | [`documentation/diagrams/erd.md`](documentation/diagrams/erd.md) |
| Context diagram (Level-0 DFD) | [`documentation/diagrams/context.md`](documentation/diagrams/context.md) |
| System architecture | [`documentation/diagrams/architecture.md`](documentation/diagrams/architecture.md) |
| Wireframes & responsive spec | [`documentation/diagrams/wireframes.md`](documentation/diagrams/wireframes.md) |
| Database schema | [`documentation/database/schema.sql`](documentation/database/schema.sql) |
| Business model & revenue | [`documentation/business-model.md`](documentation/business-model.md) |
| Security & validation controls | [`documentation/security/security-controls.md`](documentation/security/security-controls.md) |
| Final defense guide | [`documentation/presentation/defense-guide.md`](documentation/presentation/defense-guide.md) |
| Backend API & setup | [`backend/README.md`](backend/README.md) |
| Frontend SPA | [`frontend/README.md`](frontend/README.md) |
| WordPress CMS bridge | [`cms/wordpress-integration-guide.md`](cms/wordpress-integration-guide.md) |
