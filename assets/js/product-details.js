/**
 * CraftMarket - Product Details Client Logic
 * Handles dynamic product loading based on URL parameter (?id=X),
 * quantity selection, add-to-cart cart interaction, and related craft suggestions.
 */

// 1. COMPREHENSIVE PRODUCT CATALOG DATASET
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
        description: "Hand-thrown on a traditional potter's wheel using locally sourced Laguna stoneware clay. This mug features a natural unglazed earthy exterior combined with a food-safe glazed interior. Fired at over 1,200°C for exceptional strength, heat retention, and daily kitchen durability.",
        materials: "100% Native Clay, Non-Toxic Mineral Glaze",
        dimensions: "350ml capacity • 9.0cm height x 8.5cm diameter",
        origin: "Pila, Laguna, Philippines"
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
        description: "Tightly handwoven from sun-dried natural abaca fibers by women artisan weavers. Features durable vegetable-tanned genuine leather shoulder straps reinforced with antique brass rivets. Lightweight, breathable, and designed to carry groceries, books, or beach essentials.",
        materials: "100% Natural Abaca Fiber, Veg-Tanned Leather, Brass Rivets",
        dimensions: "38cm width x 32cm height x 12cm base gusset",
        origin: "Baguio, Benguet, Philippines"
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
        description: "Individually handcrafted with authentic Palawan baroque freshwater pearls known for their organic shapes and luminous luster. Complemented with recycled brass spacer beads and durable waterproof macrame cord with an adjustable sliding knot.",
        materials: "Natural Baroque Pearls, Recycled Brass, Waterproof Cord",
        dimensions: "Adjustable sliding clasp (fits 16cm to 21cm wrist)",
        origin: "Puerto Princesa, Palawan, Philippines"
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
        description: "Hand-poured in small batches using 100% natural, biodegradable soy wax. Infused with pure essential oils of native wild sampaguita flower and fresh mountain lemongrass. Features a lead-free organic cotton wick for a clean, soot-free burn of over 45 hours.",
        materials: "Golden Soy Wax, Pure Botanical Essential Oils, Cotton Wick",
        dimensions: "200g net weight • 8.5cm height x 7cm diameter",
        origin: "Tagaytay, Cavite, Philippines"
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
        description: "Carved by third-generation woodcrafters from a single piece of reclaimed fallen acacia wood. Showcases rich organic grain patterns, beveled comfort edges, and a silky smooth finish protected with food-grade virgin coconut oil and organic beeswax.",
        materials: "Reclaimed Solid Acacia Hardwood, Organic Beeswax Finish",
        dimensions: "40cm length x 22cm width x 3cm depth",
        origin: "Paete, Laguna, Philippines"
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
        description: "Traditional wicker pattern hand-plaited using seasoned natural forest rattan vines. Designed with a flexible internal weave that accommodates standard 7 to 9 inch plastic nursery pots, elevating your indoor botanical garden.",
        materials: "100% Natural Forest Rattan Vines, Cotton Thread Binding",
        dimensions: "25cm diameter x 24cm height",
        origin: "Tabaco, Albay, Philippines"
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
        description: "Intricately knotted using soft 100% natural unbleached Philippine cotton cord. Suspended from a hand-turned and sanded acacia wooden ring. Sturdy design safely supports ceramic pots or glass bowls up to 22cm in diameter.",
        materials: "4mm Natural Cotton Rope, Acacia Wood Hanging Ring",
        dimensions: "105cm total length (from top ring to tassel base)",
        origin: "Iloilo City, Philippines"
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
        description: "A functional piece of ceramic art engineered specifically for pour-over coffee enthusiasts. Internal spiral channels maximize brew aeration and uniform flavor extraction. Compatible with cone-shaped 01 and 02 paper coffee filters.",
        materials: "High-Fire Stoneware, Matte Mineral Glaze",
        dimensions: "11cm top diameter x 9cm height x 10cm base plate",
        origin: "Pila, Laguna, Philippines"
    }
];

// 2. HELPER FUNCTIONS
function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

// 3. LOAD PRODUCT DETAILS
let currentProduct = null;
let currentQuantity = 1;
let cartCount = 2;

function loadProductDetails() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = parseInt(urlParams.get("id"), 10);

    // Look up product by ID or fallback to the first item
    currentProduct = craftProducts.find(p => p.id === productId) || craftProducts[0];

    // Populate DOM Elements
    document.title = `${currentProduct.title} | CraftMarket`;
    document.getElementById("breadcrumbProductName").textContent = currentProduct.title;
    
    // Media & Badge
    const mainImg = document.getElementById("mainProductImg");
    mainImg.src = currentProduct.image;
    mainImg.alt = currentProduct.title;
    document.getElementById("productBadge").textContent = currentProduct.badge;

    // Info Meta
    document.getElementById("productCategory").textContent = currentProduct.category;
    document.getElementById("productTitle").textContent = currentProduct.title;
    document.getElementById("productPrice").textContent = formatCurrency(currentProduct.price);
    document.getElementById("productRatingText").textContent = `${currentProduct.rating.toFixed(1)} (${currentProduct.reviewsCount} customer reviews)`;
    document.getElementById("productStock").textContent = `${currentProduct.stock} in stock - Ready to ship`;

    // Descriptions & Specs
    document.getElementById("productDesc").textContent = currentProduct.description;
    document.getElementById("productMaterials").textContent = currentProduct.materials;
    document.getElementById("productDimensions").textContent = currentProduct.dimensions;
    document.getElementById("productOrigin").textContent = currentProduct.origin;

    // Seller Information
    document.getElementById("artisanName").textContent = currentProduct.seller;
    document.getElementById("artisanLocation").textContent = `📍 ${currentProduct.location}`;

    // Render Related Products (Excluding Current)
    renderRelatedProducts(currentProduct.id, currentProduct.category);
}

// 4. RENDER RELATED PRODUCTS
function renderRelatedProducts(currentId, category) {
    const container = document.getElementById("relatedProductsGrid");
    container.innerHTML = "";

    // Pick 4 other products
    const related = craftProducts
        .filter(p => p.id !== currentId)
        .slice(0, 4);

    related.forEach(prod => {
        const item = document.createElement("article");
        item.className = "catalog-card";
        item.innerHTML = `
            <div class="catalog-card-media">
                <img src="${prod.image}" alt="${prod.title}" loading="lazy">
                <span class="card-badge badge-handcrafted">${prod.badge}</span>
            </div>
            <div class="catalog-card-body">
                <div class="card-artisan-line">
                    <span class="card-maker-name">${prod.seller}</span>
                    <span class="card-rating">★ ${prod.rating.toFixed(1)}</span>
                </div>
                <h3 class="card-product-title">
                    <a href="product-details.html?id=${prod.id}">${prod.title}</a>
                </h3>
                <p class="card-category-name">${prod.category}</p>
                <div class="card-pricing-row">
                    <span class="card-price">₱${formatCurrency(prod.price)}</span>
                </div>
                <div class="card-actions-row">
                    <a href="product-details.html?id=${prod.id}" class="btn-view-product" style="grid-column: 1 / -1;">
                        <span>View Details</span>
                        <span>↗</span>
                    </a>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
}

// 5. QUANTITY SELECTOR CONTROLS
const qtyInput = document.getElementById("quantityInput");
const qtyMinusBtn = document.getElementById("qtyMinusBtn");
const qtyPlusBtn = document.getElementById("qtyPlusBtn");

qtyMinusBtn.addEventListener("click", () => {
    let val = parseInt(qtyInput.value, 10) || 1;
    if (val > 1) {
        val--;
        qtyInput.value = val;
        currentQuantity = val;
    }
});

qtyPlusBtn.addEventListener("click", () => {
    let val = parseInt(qtyInput.value, 10) || 1;
    const maxStock = currentProduct ? currentProduct.stock : 20;
    if (val < maxStock) {
        val++;
        qtyInput.value = val;
        currentQuantity = val;
    } else {
        alert(`Only ${maxStock} items available in stock for this handmade piece.`);
    }
});

qtyInput.addEventListener("change", (e) => {
    let val = parseInt(e.target.value, 10);
    const maxStock = currentProduct ? currentProduct.stock : 20;
    if (isNaN(val) || val < 1) val = 1;
    if (val > maxStock) val = maxStock;
    e.target.value = val;
    currentQuantity = val;
});

// 6. ADD TO CART INTERACTION
const addToCartBtn = document.getElementById("addToCartBtn");
const detailsCartCount = document.getElementById("detailsCartCount");

addToCartBtn.addEventListener("click", () => {
    if (!currentProduct) return;

    cartCount += currentQuantity;
    if (detailsCartCount) detailsCartCount.textContent = cartCount;

    const totalPrice = formatCurrency(currentProduct.price * currentQuantity);

    // Toast Alert Notification
    const toast = document.createElement("div");
    toast.className = "cart-toast";
    toast.innerHTML = `
        <strong>✓ Added to Cart!</strong><br>
        ${currentQuantity} × "${currentProduct.title}" (₱${totalPrice})
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 50);

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 350);
    }, 3000);
});

// 7. HEADER SEARCH REDIRECTION
const headerSearchInput = document.getElementById("headerSearchInput");
if (headerSearchInput) {
    headerSearchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter" && headerSearchInput.value.trim() !== "") {
            window.location.href = `products.html?search=${encodeURIComponent(headerSearchInput.value.trim())}`;
        }
    });
}

// 8. DYNAMIC TOAST STYLES
const toastStyle = document.createElement("style");
toastStyle.innerHTML = `
.cart-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background-color: var(--color-forest, #2b3e32);
    color: #ffffff;
    padding: 14px 22px;
    border-radius: 8px;
    font-size: 0.9rem;
    line-height: 1.5;
    box-shadow: 0 10px 28px rgba(0,0,0,0.22);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.25s ease;
    z-index: 9999;
    border-left: 5px solid var(--color-terracotta, #c2693e);
}
.cart-toast.show {
    opacity: 1;
    transform: translateY(0);
}
`;
document.head.appendChild(toastStyle);

// 9. INITIALIZE ON DOM READY
window.addEventListener("DOMContentLoaded", loadProductDetails);
