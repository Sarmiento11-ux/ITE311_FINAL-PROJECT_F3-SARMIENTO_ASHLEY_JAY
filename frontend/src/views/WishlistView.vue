<template>
  <main class="wishlist-page">
    <div class="container">
      
      <!-- Breadcrumb Navigation -->
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <router-link to="/dashboard/customer">Customer Dashboard</router-link>
        <span class="sep">/</span>
        <span class="current">My Wishlist</span>
      </nav>

      <!-- Page Header -->
      <header class="wishlist-header">
        <div class="header-text">
          <span class="eyebrow">FAVORITES & SAVED CRAFTS</span>
          <h1>My Wishlist</h1>
          <p class="subtitle">
            Keep track of authentic handmade pieces from local artisans that you love.
          </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <router-link to="/dashboard/customer" class="btn btn-primary btn-sm">
            ← Back to Customer Dashboard
          </router-link>
          <button
            v-if="wishlist.items.length > 0"
            type="button"
            class="btn btn-outline-danger btn-sm"
            @click="handleClearWishlist"
          >
            Clear All ({{ wishlist.items.length }})
          </button>
        </div>
      </header>

      <!-- Loading State -->
      <div v-if="wishlist.isLoading && wishlist.items.length === 0" class="wishlist-loading">
        <p>Loading your saved artisan crafts...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishlist.items.length === 0" class="wishlist-empty-card">
        <div class="empty-icon-box">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#c25e3e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
        </div>
        <h2>Your Wishlist is Empty</h2>
        <p>You haven't saved any handcrafted treasures yet. Discover authentic regional crafts and tap the heart icon to save them here!</p>
        <router-link to="/products" class="btn btn-primary btn-explore">
          Explore Product Catalog ↗
        </router-link>
      </div>

      <!-- Wishlist Items Grid / Table -->
      <div v-else class="wishlist-content-wrapper">
        <div class="wishlist-summary-bar">
          <span class="summary-count">
            <strong>{{ wishlist.items.length }}</strong> {{ wishlist.items.length === 1 ? 'saved craft' : 'saved crafts' }}
          </span>
          <router-link to="/products" class="continue-shopping-link">
            + Discover More Crafts
          </router-link>
        </div>

        <div class="wishlist-grid">
          <article
            v-for="item in wishlist.items"
            :key="item.id || item.productId"
            class="wishlist-item-card"
          >
            <!-- Media Frame -->
            <div class="item-media">
              <router-link :to="`/products/${item.id || item.productId}`">
                <img
                  :src="item.image || defaultPlaceholder"
                  :alt="item.title || item.name"
                  class="item-img"
                  @error="handleImgError"
                  loading="lazy"
                />
              </router-link>

              <!-- Remove Quick Pill -->
              <button
                type="button"
                class="btn-remove-pill"
                @click="handleRemove(item.id || item.productId)"
                title="Remove from favorites"
                aria-label="Remove item"
              >
                ✕
              </button>

              <!-- Stock Tag -->
              <span
                class="stock-status-badge"
                :class="{
                  'badge-in-stock': (item.stock > 5),
                  'badge-low-stock': (item.stock > 0 && item.stock <= 5),
                  'badge-sold-out': (item.stock <= 0)
                }"
              >
                {{ item.stock > 0 ? (item.stock <= 5 ? `Only ${item.stock} left` : 'In Stock') : 'Sold Out' }}
              </span>
            </div>

            <!-- Details Block -->
            <div class="item-details">
              <div class="item-meta-top">
                <span class="item-category">{{ item.category || 'Handmade' }}</span>
                <span class="item-seller">By {{ item.seller || 'Artisan Maker' }}</span>
              </div>

              <h3 class="item-title">
                <router-link :to="`/products/${item.id || item.productId}`">
                  {{ item.title || item.name }}
                </router-link>
              </h3>

              <div class="item-price-row">
                <div class="price-val">
                  <span class="curr">₱</span>{{ Number(item.price).toFixed(2) }}
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="item-actions-row">
                <button
                  type="button"
                  class="btn btn-primary btn-add-cart-w"
                  :disabled="item.stock <= 0"
                  @click="handleAddToCart(item)"
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                  </svg>
                  <span>{{ item.stock > 0 ? 'Move to Cart' : 'Out of Stock' }}</span>
                </button>

                <button
                  type="button"
                  class="btn btn-outline btn-remove-w"
                  @click="handleRemove(item.id || item.productId)"
                >
                  Remove
                </button>
              </div>
            </div>
          </article>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { onMounted } from 'vue';
import { wishlist } from '../data/wishlist.js';
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';

onMounted(() => {
  wishlist.loadWishlist(true);
});

const handleImgError = (e) => {
  e.target.src = defaultPlaceholder;
};

const handleRemove = async (productId) => {
  await wishlist.remove(productId);
};

const handleAddToCart = async (item) => {
  await wishlist.moveToCart(item);
};

const handleClearWishlist = async () => {
  if (confirm('Are you sure you want to clear all saved items from your wishlist?')) {
    const ids = wishlist.items.map(i => i.id || i.productId);
    for (const id of ids) {
      await wishlist.remove(id);
    }
  }
};
</script>

<style scoped>
.wishlist-page {
  padding: 40px 0 80px;
  min-height: 80vh;
  background-color: var(--cm-cream, #FAF6F0);
}

.breadcrumb-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #7a6a60;
  margin-bottom: 24px;
}

.breadcrumb-nav a {
  color: #7a6a60;
  text-decoration: none;
}

.breadcrumb-nav a:hover {
  color: #c25e3e;
}

.breadcrumb-nav .sep {
  color: #c4b8ad;
}

.breadcrumb-nav .current {
  color: #2d2420;
  font-weight: 600;
}

.wishlist-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 20px;
  padding-bottom: 24px;
  border-bottom: 1px solid #efe8de;
  margin-bottom: 32px;
}

.eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #c25e3e;
  display: block;
  margin-bottom: 6px;
}

.wishlist-header h1 {
  font-size: 2.2rem;
  color: #2d2420;
  margin: 0 0 6px;
}

.subtitle {
  color: #7a6a60;
  font-size: 0.95rem;
  margin: 0;
}

/* ── Empty State ── */
.wishlist-empty-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 16px;
  padding: 60px 24px;
  text-align: center;
  max-width: 580px;
  margin: 40px auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.empty-icon-box {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #fceee9;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.wishlist-empty-card h2 {
  font-size: 1.5rem;
  color: #2d2420;
  margin-bottom: 12px;
}

.wishlist-empty-card p {
  color: #7a6a60;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 28px;
}

.btn-explore {
  padding: 12px 28px;
  font-size: 0.95rem;
  text-decoration: none;
  display: inline-block;
}

/* ── Content Grid ── */
.wishlist-summary-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  font-size: 0.9rem;
}

.summary-count {
  color: #5a4f48;
}

.continue-shopping-link {
  color: #3e5943;
  font-weight: 600;
  text-decoration: none;
}

.continue-shopping-link:hover {
  text-decoration: underline;
}

.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.wishlist-item-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.wishlist-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.item-media {
  position: relative;
  aspect-ratio: 1 / 1;
  background: #f5ede4;
  overflow: hidden;
}

.item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.wishlist-item-card:hover .item-img {
  transform: scale(1.04);
}

.btn-remove-pill {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.92);
  border: none;
  color: #7a6a60;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.15s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.btn-remove-pill:hover {
  background: #c25e3e;
  color: #ffffff;
}

.stock-status-badge {
  position: absolute;
  bottom: 10px;
  left: 10px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.badge-in-stock {
  background: rgba(237, 244, 238, 0.95);
  color: #2e7d32;
}

.badge-low-stock {
  background: rgba(254, 247, 236, 0.95);
  color: #b07a2a;
}

.badge-sold-out {
  background: rgba(252, 238, 233, 0.95);
  color: #c25e3e;
}

.item-details {
  padding: 18px 20px 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.item-meta-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.78rem;
  margin-bottom: 8px;
}

.item-category {
  color: #9e8f85;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.item-seller {
  color: #7a6a60;
}

.item-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #2d2420;
  margin: 0 0 12px;
  line-height: 1.35;
}

.item-title a {
  color: inherit;
  text-decoration: none;
}

.item-title a:hover {
  color: #c25e3e;
}

.item-price-row {
  margin-bottom: 16px;
}

.price-val {
  font-size: 1.3rem;
  font-weight: 800;
  color: #2d2420;
}

.price-val .curr {
  font-size: 0.9rem;
  font-weight: 600;
  margin-right: 2px;
}

.item-actions-row {
  margin-top: auto;
  display: flex;
  gap: 8px;
}

.btn-add-cart-w {
  flex: 1;
  padding: 8px 12px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-add-cart-w:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-remove-w {
  padding: 8px 12px;
  font-size: 0.82rem;
  border-color: #ddd3c8;
  color: #7a6a60;
}

.btn-remove-w:hover {
  border-color: #c25e3e;
  color: #c25e3e;
  background: #fceee9;
}

.btn-outline-danger {
  border: 1px solid #f3c9bb;
  background: transparent;
  color: #b84f33;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.82rem;
  transition: all 0.2s;
}

.btn-outline-danger:hover {
  background: #fceee9;
  border-color: #c25e3e;
}

/* Prevent buttons from stretching full-width on mobile */
.btn-add-cart-w,
.btn-remove-w,
.btn-outline-danger {
  width: auto !important;
}

/* Keyboard focus rings */
.btn-add-cart-w:focus-visible,
.btn-remove-w:focus-visible,
.btn-remove-pill:focus-visible {
  outline: 2px solid #3e5943;
  outline-offset: 2px;
}

@media (max-width: 480px) {
  .wishlist-grid {
    grid-template-columns: 1fr;
  }

  .item-actions-row {
    flex-wrap: wrap;
    gap: 8px;
  }
}
</style>
