# CraftMarket - Local Handmade Products Marketplace

**Student Name:** Ashley Jay Sarmiento  
**Course and Section:** BSIT - F3  
**Project:** CraftMarket Web Application (ITE311 Final Project)

---

## 📌 Project Overview
**CraftMarket** is a modern, responsive web-based marketplace application designed to connect local Filipino handmade artisans, weavers, potters, and crafters directly with conscious consumers. It provides small-scale producers with an accessible platform to showcase authentic handcrafted goods while supporting the local micro-artisan economy.

---

## 🛠️ Technologies Used
- **Frontend:** HTML5, CSS3 (Custom Design System), JavaScript (ES6+ Vanilla)
- **Backend (Upcoming):** PHP 8.x
- **Database (Upcoming):** MySQL
- **CMS Integration:** WordPress (Headless / Admin Panel)
- **Version Control:** Git & GitHub

---

## 🌟 Implemented Modules

### Step 1: Homepage (`index.html`, `style.css`)
- Community Announcement Bar
- Sticky Navigation & Marketplace Header with Search Bar
- Hero Showcase with Artisan Badge and Verified Community Statistics
- Artisan Business Overview & Revenue Sharing Model
- Product Categories & Curated Collections
- Featured Handmade Products with Philippine Peso (₱) pricing
- Cultural Heritage & Fair Trade About Section
- Multi-column Marketplace Footer

### Step 2: Product Catalog & Product Details (`products.*`, `product-details.*`)
- **Interactive Product Catalog (`products.html`, `products.css`, `products.js`):**
  - Real-time product search by title, artisan name, or discipline
  - Dynamic category filter buttons (Ceramics & Pottery, Woven Goods, Artisan Jewelry, Home Decor, Wooden Crafts)
  - Sorting engine (Price: Low to High, Price: High to Low, Highest Rated, Name A-Z, Featured)
  - Result count indicator and graceful Empty State with filter reset button
  - Quick Add to Cart with floating toast notification
  - View Product buttons linking to distinct product details
- **Comprehensive Product Details Page (`product-details.html`, `product-details.css`, `product-details.js`):**
  - Dynamic product loading via URL parameter (`?id=X`)
  - Large craft image showcase with artisan badges
  - Price, stock indicator, materials, dimensions, and craft origin
  - Interactive quantity selector with stock boundary validation
  - Large Add to Cart button with animated cart counter update
  - Verified local artisan maker profile box with location
  - Contextual related crafts recommendations section

---

## 🚀 How to Run Locally
1. Clone or download this repository.
2. Open the folder in **Visual Studio Code**.
3. Open `index.html` in your web browser (or right-click `index.html` and choose **Open with Live Server**).
