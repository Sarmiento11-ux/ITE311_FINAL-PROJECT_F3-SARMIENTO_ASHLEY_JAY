# CraftMarket — Local Handmade Products Marketplace

**Student Name:** Ashley Jay Sarmiento  
**Course and Section:** BSIT - F3  
**Project:** CraftMarket Web Application (ITE311 Final Project)

---

## 📌 Project Overview

**CraftMarket** is a modern, decoupled web marketplace application designed to connect local Filipino handmade artisans, weavers, potters, and crafters directly with conscious consumers. It provides micro-producers with an accessible digital storefront while safeguarding buyer confidence through escrow-based transactions and genuine craft curation.

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
│   ├── database/schema.sql            # Relational schema reference
│   ├── diagrams/erd.md                # Mermaid ERD & architecture diagrams
│   └── SRS/system-requirements.md     # Software Requirements Specification
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
php artisan serve
```

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

## 📚 Documentation

- **Backend API & setup:** [`backend/README.md`](backend/README.md)
- **Frontend SPA:** [`frontend/README.md`](frontend/README.md)
- **WordPress CMS bridge:** [`cms/wordpress-integration-guide.md`](cms/wordpress-integration-guide.md)
- **Database schema:** [`documentation/database/schema.sql`](documentation/database/schema.sql)
- **ERD & diagrams:** [`documentation/diagrams/erd.md`](documentation/diagrams/erd.md)
- **System requirements:** [`documentation/SRS/system-requirements.md`](documentation/SRS/system-requirements.md)
