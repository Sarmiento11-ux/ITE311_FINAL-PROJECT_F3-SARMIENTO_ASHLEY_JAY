/**
 * CraftMarket - Product Catalog Client Logic
 * Handles interactive search, category filtering, price sorting, and dynamic card rendering
 */

// 1. SAMPLE REALISTIC HANDMADE PRODUCT DATASET
const craftProducts = [
    {
        id: 1,
        title: "Earthy Terracotta Coffee Mug",
        category: "Ceramics & Pottery",
        price: 420.00,
        seller: "Studio Clayworks",
        location: "Laguna, Philippines",
        rating: 4.9,
        reviewsCount: 24,
        badge: "Featured",
        stock: 8,
        image: "https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Hand-thrown stoneware mug made with natural local clay and food-safe matte mineral glaze."
    },
    {
        id: 2,
        title: "Handwoven Abaca Market Tote",
        category: "Woven Goods & Bags",
        price: 780.00,
        seller: "Cordillera Weaves",
        location: "Baguio City, Philippines",
        rating: 5.0,
        reviewsCount: 42,
        badge: "Best Seller",
        stock: 12,
        image: "https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Tightly handwoven from natural abaca fibers with sturdy vegetable-tanned leather straps."
    },
    {
        id: 3,
        title: "Palawan Freshwater Pearl Bracelet",
        category: "Artisan Jewelry",
        price: 350.00,
        seller: "Tala Artisan Co.",
        location: "Puerto Princesa, Palawan",
        rating: 4.8,
        reviewsCount: 18,
        badge: "Handcrafted",
        stock: 15,
        image: "https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Natural baroque freshwater pearls strung on waterproof cord with recycled brass beads."
    },
    {
        id: 4,
        title: "Botanical Soy Candle in Amber Jar",
        category: "Home Decor & Candles",
        price: 295.00,
        seller: "Habilin Apothecary",
        location: "Tagaytay, Cavite",
        rating: 4.9,
        reviewsCount: 31,
        badge: "Handcrafted",
        stock: 20,
        image: "https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Hand-poured 100% natural soy wax scented with native wild sampaguita and lemongrass."
    },
    {
        id: 5,
        title: "Carved Acacia Wood Serving Tray",
        category: "Wooden Crafts",
        price: 650.00,
        seller: "Paete Woodcraft Guild",
        location: "Paete, Laguna",
        rating: 4.9,
        reviewsCount: 15,
        badge: "Featured",
        stock: 6,
        image: "https://images.unsplash.com/photo-1546554137-f86b9593a222?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Sculpted from a single slab of sustainable acacia wood and finished with organic food-grade beeswax."
    },
    {
        id: 6,
        title: "Handwoven Rattan Planter Basket",
        category: "Woven Goods & Bags",
        price: 480.00,
        seller: "Bicol Craft Collective",
        location: "Albay, Bicol",
        rating: 4.7,
        reviewsCount: 29,
        badge: "Best Seller",
        stock: 10,
        image: "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Traditional wicker pattern hand-plaited using seasoned natural forest rattan vines."
    },
    {
        id: 7,
        title: "Macrame Plant Hanger with Wood Ring",
        category: "Home Decor & Candles",
        price: 320.00,
        seller: "Knot & Thread Studio",
        location: "Iloilo City, Philippines",
        rating: 4.8,
        reviewsCount: 22,
        badge: "Handcrafted",
        stock: 14,
        image: "https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Intricately knotted with 100% unbleached cotton cord and suspended from an acacia wood ring."
    },
    {
        id: 8,
        title: "Ceramic Minimalist Pour-Over Dripper",
        category: "Ceramics & Pottery",
        price: 520.00,
        seller: "Studio Clayworks",
        location: "Laguna, Philippines",
        rating: 5.0,
        reviewsCount: 37,
        badge: "Featured",
        stock: 9,
        image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80",
        shortDesc: "Custom ribbed interior designed for balanced extraction. High-fired durable stoneware pottery."
    }
];

// 2. STATE MANAGEMENT
let activeCategory = "all";
let searchQuery = "";
let currentSort = "featured";

// 3. DOM ELEMENTS
const productGrid = document.getElementById("productGrid");
const emptyState = document.getElementById("emptyState");
const resultsCount = document.getElementById("resultsCount");
const catalogSearchInput = document.getElementById("catalogSearchInput");
const clearSearchBtn = document.getElementById("clearSearchBtn");
const headerSearchInput = document.getElementById("headerSearchInput");
const categoryButtons = document.querySelectorAll(".cat-filter-btn");
const sortSelect = document.getElementById("sortSelect");
const resetFiltersBtn = document.getElementById("resetFiltersBtn");
const navCartCount = document.getElementById("navCartCount");

// 4. FORMAT PRICE HELPER (PHP Currency ₱)
function formatCurrency(amount) {
    return "₱" + parseFloat(amount).toFixed(2);
}

// 5. RENDER PRODUCT CARDS
function renderProducts(items) {
    productGrid.innerHTML = "";

    if (!items || items.length === 0) {
        productGrid.style.display = "none";
        emptyState.style.display = "block";
        resultsCount.textContent = "0";
        return;
    }

    productGrid.style.display = "grid";
    emptyState.style.display = "none";
    resultsCount.textContent = items.length;

    items.forEach(product => {
        const card = document.createElement("article");
        card.className = "catalog-card";

        // Determine badge style
        let badgeClass = "badge-handcrafted";
        if (product.badge === "Featured") badgeClass = "badge-featured";
        if (product.badge === "Best Seller") badgeClass = "badge-bestseller";

        card.innerHTML = `
            <div class="catalog-card-media">
                <img src="${product.image}" alt="${product.title}" loading="lazy">
                <span class="card-badge ${badgeClass}">${product.badge}</span>
                <button class="card-wishlist-btn" title="Add to Wishlist" onclick="toggleWishlist(this, '${product.title}')">♥</button>
            </div>
            <div class="catalog-card-body">
                <div class="card-artisan-line">
                    <span class="card-maker-name">${product.seller}</span>
                    <span class="card-rating">★ ${product.rating.toFixed(1)} (${product.reviewsCount})</span>
                </div>
                <h3 class="card-product-title">
                    <a href="product-details.html?id=${product.id}">${product.title}</a>
                </h3>
                <p class="card-category-name">${product.category}</p>
                <div class="card-pricing-row">
                    <span class="card-price">${formatCurrency(product.price)}</span>
                    <span class="card-stock-label">${product.stock} in stock</span>
                </div>
                <div class="card-actions-row">
                    <a href="product-details.html?id=${product.id}" class="btn-view-product">
                        <span>View Product</span>
                        <span>↗</span>
                    </a>
                    <button class="btn-quick-cart" title="Quick Add to Cart" onclick="quickAddToCart('${product.title}', ${product.price})">+</button>
                </div>
            </div>
        `;

        productGrid.appendChild(card);
    });
}

// 6. FILTER AND SORT ENGINE
function filterAndSortProducts() {
    let filtered = [...craftProducts];

    // Filter by Category
    if (activeCategory !== "all") {
        filtered = filtered.filter(p => p.category.toLowerCase() === activeCategory.toLowerCase());
    }

    // Filter by Search Term (Title, Seller, or Category)
    if (searchQuery.trim() !== "") {
        const query = searchQuery.toLowerCase().trim();
        filtered = filtered.filter(p => 
            p.title.toLowerCase().includes(query) ||
            p.seller.toLowerCase().includes(query) ||
            p.category.toLowerCase().includes(query)
        );
    }

    // Sort Products
    switch (currentSort) {
        case "price-low-high":
            filtered.sort((a, b) => a.price - b.price);
            break;
        case "price-high-low":
            filtered.sort((a, b) => b.price - a.price);
            break;
        case "rating-high":
            filtered.sort((a, b) => b.rating - a.rating);
            break;
        case "name-a-z":
            filtered.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case "featured":
        default:
            filtered.sort((a, b) => {
                if (a.badge === "Featured" && b.badge !== "Featured") return -1;
                if (b.badge === "Featured" && a.badge !== "Featured") return 1;
                return a.id - b.id;
            });
            break;
    }

    renderProducts(filtered);
}

// 7. EVENT LISTENERS

// Search Bar Input
catalogSearchInput.addEventListener("input", (e) => {
    searchQuery = e.target.value;
    clearSearchBtn.style.display = searchQuery.length > 0 ? "block" : "none";
    filterAndSortProducts();
});

// Clear Search Button
clearSearchBtn.addEventListener("click", () => {
    catalogSearchInput.value = "";
    searchQuery = "";
    clearSearchBtn.style.display = "none";
    catalogSearchInput.focus();
    filterAndSortProducts();
});

// Header Search Sync
if (headerSearchInput) {
    headerSearchInput.addEventListener("input", (e) => {
        searchQuery = e.target.value;
        catalogSearchInput.value = searchQuery;
        clearSearchBtn.style.display = searchQuery.length > 0 ? "block" : "none";
        filterAndSortProducts();
    });
}

// Category Tabs
categoryButtons.forEach(btn => {
    btn.addEventListener("click", () => {
        categoryButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        activeCategory = btn.getAttribute("data-category");
        filterAndSortProducts();
    });
});

// Sort Dropdown
sortSelect.addEventListener("change", (e) => {
    currentSort = e.target.value;
    filterAndSortProducts();
});

// Reset Button (In Empty State)
resetFiltersBtn.addEventListener("click", () => {
    activeCategory = "all";
    searchQuery = "";
    currentSort = "featured";

    catalogSearchInput.value = "";
    if (headerSearchInput) headerSearchInput.value = "";
    clearSearchBtn.style.display = "none";
    sortSelect.value = "featured";

    categoryButtons.forEach(b => {
        if (b.getAttribute("data-category") === "all") {
            b.classList.add("active");
        } else {
            b.classList.remove("active");
        }
    });

    filterAndSortProducts();
});

// 8. INTERACTIVE HELPERS (CART & WISHLIST)
let cartCount = 2;

function quickAddToCart(productName, price) {
    cartCount++;
    if (navCartCount) navCartCount.textContent = cartCount;

    // Toast alert feedback
    const toast = document.createElement("div");
    toast.className = "cart-toast";
    toast.textContent = `✓ Added "${productName}" to cart (${formatCurrency(price)})`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 50);

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

function toggleWishlist(button, productName) {
    button.classList.toggle("active");
    if (button.classList.contains("active")) {
        button.style.color = "#d9383a";
    } else {
        button.style.color = "var(--text-muted)";
    }
}

// 9. URL QUERY PARAMETER INITIALIZATION
// Allows linking from homepage: products.html?category=Ceramics+%26+Pottery
window.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get("category");
    const searchParam = urlParams.get("search");

    if (categoryParam) {
        activeCategory = categoryParam;
        categoryButtons.forEach(b => {
            if (b.getAttribute("data-category").toLowerCase() === categoryParam.toLowerCase()) {
                b.classList.add("active");
            } else {
                b.classList.remove("active");
            }
        });
    }

    if (searchParam) {
        searchQuery = searchParam;
        catalogSearchInput.value = searchParam;
        clearSearchBtn.style.display = "block";
    }

    filterAndSortProducts();
});

// Dynamic Toast Styling
const toastStyle = document.createElement("style");
toastStyle.innerHTML = `
.cart-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background-color: var(--color-forest, #2b3e32);
    color: #ffffff;
    padding: 14px 20px;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0,0,0,0.18);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.25s ease;
    z-index: 9999;
    border-left: 4px solid var(--color-terracotta, #c2693e);
}
.cart-toast.show {
    opacity: 1;
    transform: translateY(0);
}
`;
document.head.appendChild(toastStyle);
