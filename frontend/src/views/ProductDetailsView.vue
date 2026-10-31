<template>
  <main v-if="product">
    <!-- 1. BREADCRUMBS & NAVIGATION RETURN -->
    <section class="details-top-bar">
      <div class="container">
        <div class="top-nav-row">
          <router-link to="/products" class="back-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Product Catalog</span>
          </router-link>

          <div class="breadcrumb-trail">
            <router-link to="/">Home</router-link>
            <span class="breadcrumb-separator">/</span>
            <router-link to="/products">Catalog</router-link>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ product.title }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. PRODUCT DETAILS SHOWCASE -->
    <section class="product-details-section">
      <div class="container">
        
        <div class="product-details-grid">

          <!-- Visual Column: Large Image & Trust Banner -->
          <div class="details-gallery-col">
            <div class="main-image-frame">
              <img :src="product.image" :alt="product.title" class="main-product-img">
              <span v-if="product.badge" class="product-tag-float">{{ product.badge }}</span>
              <div class="eco-badge-corner">
                <span>🌿 100% Genuine Craft</span>
              </div>
            </div>

            <!-- Artisan Craftsmanship Trust Banner -->
            <div class="artisan-trust-card">
              <div class="trust-icon">🤝</div>
              <div class="trust-text">
                <h4>Fair Trade & Artisan Direct</h4>
                <p>Over 90% of your payment is directly deposited into the artisan's local workshop account upon confirmed delivery.</p>
              </div>
            </div>
          </div>

          <!-- Information Column -->
          <div class="details-info-col">
            
            <!-- Category & Rating -->
            <div class="info-top-meta">
              <span class="category-pill">{{ product.category }}</span>
              <div class="rating-badge">
                <span class="stars">★★★★★</span>
                <span class="rating-text">{{ product.rating }} ({{ product.reviewsCount || 24 }} customer reviews)</span>
              </div>
            </div>

            <!-- Product Title -->
            <h1 class="details-product-title">{{ product.title }}</h1>

            <!-- Pricing & Stock Status -->
            <div class="details-price-card">
              <div class="price-display">
                <span class="currency-symbol">₱</span>
                <span class="price-value">{{ Number(product.price).toFixed(2) }}</span>
              </div>
              <div class="stock-indicator">
                <span class="stock-dot" :class="{ 'dot-low': product.stock <= 5 }"></span>
                <span>
                  {{ product.stock > 0 ? (product.stock <= 5 ? `Low Stock (Only ${product.stock} available)` : `In Stock (${product.stock} available)`) : 'Out of Stock' }}
                </span>
              </div>
            </div>

            <!-- Product Description -->
            <div class="details-description-block">
              <h3 class="block-heading">About this Craft</h3>
              <p class="description-text">{{ product.description }}</p>
            </div>

            <!-- Craft Specifications Table -->
            <div class="specs-grid">
              <div class="spec-item">
                <span class="spec-label">Materials:</span>
                <span class="spec-value">{{ product.materials || 'Natural Local Materials' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Dimensions:</span>
                <span class="spec-value">{{ product.dimensions || 'Standard Artisan Craft Sizing' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Lead Time:</span>
                <span class="spec-value">Ships within 24-48 hours</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Origin:</span>
                <span class="spec-value">{{ product.origin || product.location }}</span>
              </div>
            </div>

            <!-- Purchase Actions & Subtotal Calculation -->
            <div class="purchase-box">
              <div class="purchase-top-row">
                <div class="quantity-control-group">
                  <label for="quantityInput" class="quantity-label">Quantity:</label>
                  <div class="quantity-selector">
                    <button type="button" @click="decreaseQty" class="qty-btn" aria-label="Decrease quantity" :disabled="quantity <= 1">−</button>
                    <input type="number" id="quantityInput" v-model.number="quantity" min="1" :max="product.stock" @change="validateQty">
                    <button type="button" @click="increaseQty" class="qty-btn" aria-label="Increase quantity" :disabled="quantity >= product.stock">+</button>
                  </div>
                </div>

                <div class="subtotal-indicator">
                  <span class="subtotal-label">Subtotal:</span>
                  <span class="subtotal-amount">₱{{ calculatedSubtotal }}</span>
                </div>
              </div>

              <!-- Feedback message -->
              <div v-if="cartFeedback" class="cart-feedback-note" :class="{ 'note-error': feedbackIsError }">
                {{ cartFeedback }}
              </div>

              <button 
                type="button" 
                @click="addToCart" 
                class="btn btn-primary btn-add-cart-large"
                :disabled="product.stock <= 0"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                  <line x1="3" y1="6" x2="21" y2="6"></line>
                  <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span>Add to Cart ({{ quantity }})</span>
              </button>
            </div>

            <!-- Artisan Maker Profile Card -->
            <div class="seller-profile-card">
              <div class="seller-avatar-wrapper">
                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&q=80" alt="Artisan Maker" class="seller-avatar">
                <span class="verified-icon" title="Verified Artisan Maker">✓</span>
              </div>
              <div class="seller-details">
                <span class="seller-badge">Verified Local Artisan</span>
                <h4 class="seller-name">{{ product.seller }}</h4>
                <p class="seller-location">📍 {{ product.location }}</p>
                <p class="seller-bio">Dedicated to reviving regional handmade traditions using sustainable and responsibly sourced natural materials.</p>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section>

    <!-- 3. RELATED PRODUCTS SECTION -->
    <section class="related-products-section">
      <div class="container">
        <div class="section-header-split">
          <div>
            <span class="section-eyebrow">MORE FROM THIS CRAFT</span>
            <h2 class="section-title">You Might Also Like</h2>
          </div>
          <router-link to="/products" class="view-all-link">
            Explore Full Catalog ↗
          </router-link>
        </div>

        <div class="related-grid">
          <ProductCard 
            v-for="rel in relatedProducts" 
            :key="rel.id" 
            :product="rel" 
          />
        </div>
      </div>
    </section>

  </main>
  
  <!-- Fallback if ID is invalid -->
  <div v-else class="container" style="padding: 100px 0; text-align: center;">
    <h2>Product Not Found</h2>
    <p>The craft piece you are looking for might have been moved or is currently sold out.</p>
    <router-link to="/products" class="btn btn-primary" style="margin-top: 20px;">Return to Catalog</router-link>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import { sampleProducts } from '../data/products.js';
import { cart } from '../data/cart.js';

const route = useRoute();

const product = ref(null);
const relatedProducts = ref([]);
const quantity = ref(1);
const cartFeedback = ref('');
const feedbackIsError = ref(false);

const loadProductData = (id) => {
  const numId = parseInt(id, 10);
  const found = sampleProducts.find(p => p.id === numId);
  product.value = found || null;

  if (found) {
    relatedProducts.value = sampleProducts.filter(
      p => p.id !== found.id && p.category === found.category
    ).slice(0, 4);
  }
};

onMounted(() => {
  loadProductData(route.params.id);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    quantity.value = 1;
    cartFeedback.value = '';
    loadProductData(newId);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

const calculatedSubtotal = computed(() => {
  if (!product.value) return '0.00';
  return (product.value.price * quantity.value).toFixed(2);
});

const validateQty = () => {
  if (quantity.value < 1) quantity.value = 1;
  if (product.value && quantity.value > product.value.stock) {
    quantity.value = product.value.stock;
    cartFeedback.value = `Maximum available stock is ${product.value.stock}.`;
    feedbackIsError.value = true;
  } else {
    cartFeedback.value = '';
    feedbackIsError.value = false;
  }
};

const decreaseQty = () => {
  if (quantity.value > 1) {
    quantity.value--;
    cartFeedback.value = '';
  }
};

const increaseQty = () => {
  if (product.value && quantity.value < product.value.stock) {
    quantity.value++;
    cartFeedback.value = '';
  }
};

const addToCart = () => {
  if (!product.value) return;
  const result = cart.addToCart(product.value, quantity.value);
  if (result.success) {
    cartFeedback.value = `✓ Successfully added ${quantity.value} item(s) to cart!`;
    feedbackIsError.value = false;
    setTimeout(() => { cartFeedback.value = ''; }, 3500);
  } else {
    cartFeedback.value = result.message;
    feedbackIsError.value = true;
  }
};
</script>

<style scoped>
.purchase-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.subtotal-indicator {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.subtotal-label {
  font-size: 0.8rem;
  color: #6b635b;
}

.subtotal-amount {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.cart-feedback-note {
  font-size: 0.85rem;
  font-weight: 500;
  color: #2e7d32;
  margin-bottom: 12px;
}

.cart-feedback-note.note-error {
  color: #c25e3e;
}

.dot-low {
  background-color: #c25e3e !important;
}
</style>
