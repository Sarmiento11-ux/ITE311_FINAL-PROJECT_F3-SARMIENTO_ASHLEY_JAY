# CraftMarket - Local Handmade Products Marketplace

**Student Name:** Ashley Jay Sarmiento  
**Course and Section:** BSIT - F3  
**Project:** CraftMarket Web Application (ITE311 Final Project)

---

## 📌 Project Overview
**CraftMarket** is a modern, decoupled web marketplace application designed to connect local Filipino handmade artisans, weavers, potters, and crafters directly with conscious consumers. It provides micro-producers with an accessible digital storefront while safeguarding buyer confidence through escrow-based transactions and genuine craft curation.

---

## 🏗️ Decoupled Technology Stack
- **Frontend SPA:** Vue.js 3, Vite, Vue Router 4, Custom Editorial Design System (CSS)
- **Backend API:** PHP 8.2, Laravel 11, Laravel Sanctum (Token Auth)
- **Relational Database:** MySQL 8.x (InnoDB, Foreign Key Constraints)
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
│   │   ├── assets/
│   │   │   ├── css/                   # Custom handcrafted stylesheets
│   │   │   │   ├── style.css          # Design system, variables & global styles
│   │   │   │   ├── products.css       # Catalog grid and filter styles
│   │   │   │   ├── product-details.css# Details showcase & spec table styles
│   │   │   │   ├── auth.css           # Unified login & register card styling
│   │   │   │   └── profile.css        # Profile page & role preview styles
│   │   │   └── images/                # Local artisan images
│   │   │
│   │   ├── components/                # Modular reusable Vue components
│   │   │   ├── Navbar.vue             # Header with search & router links
│   │   │   ├── Footer.vue             # Multi-column marketplace footer
│   │   │   └── ProductCard.vue        # Reusable product card with price in ₱
│   │   │
│   │   ├── views/                     # Page route views
│   │   │   ├── HomeView.vue           # Migrated homepage
│   │   │   ├── ProductsView.vue       # Interactive catalog & multi-filter
│   │   │   ├── ProductDetailsView.vue # Single craft view & quantity selector
│   │   │   ├── RegisterView.vue       # Reactive registration form
│   │   │   ├── LoginView.vue          # Login engine & quick demo buttons
│   │   │   └── ProfileView.vue        # Profile management & role preview
│   │   │
│   │   ├── router/                    # Vue Router 4 setup
│   │   │   └── index.js
│   │   │
│   │   ├── services/                  # Service layer for REST API communication
│   │   │   └── productService.js      # Mock & upcoming Laravel API service
│   │   │
│   │   ├── App.vue                    # Root application component
│   │   └── main.js                    # Vue entry point & stylesheet imports
│   │
│   ├── index.html                     # Vite HTML entry point
│   ├── package.json                   # Dependencies (Vue 3, Vue Router 4, Vite)
│   └── vite.config.js                 # Vite configuration
│
├── backend/                           # [LARAVEL RESTful API PREPARATION]
│   └── README.md                      # Laravel setup & API endpoint blueprints
│
├── cms/                               # [WORDPRESS INTEGRATION]
│   └── wordpress-integration-guide.md # Headless WP REST API documentation
│
├── documentation/                     # [PROJECT SPECIFICATIONS]
│   ├── database/
│   │   └── schema.sql                 # 7 Relational tables & seeds
│   ├── diagrams/
│   │   └── erd.md                     # Mermaid ERD & Architecture diagrams
│   └── SRS/
│       └── system-requirements.md     # Software Requirements Specification
│
└── README.md                          # Main project guide
```

---

## 🚀 How to Run the Vue.js Frontend Locally

1. Open your terminal in the `CraftMarket/frontend` directory:
   ```powershell
   cd c:\CraftMarket\frontend
   ```
2. Install dependencies (if not already installed):
   ```powershell
   npm install
   ```
3. Start the Vite development server:
   ```powershell
   npm run dev
   ```
4. Open the displayed URL in your browser (typically `http://localhost:5173`).
