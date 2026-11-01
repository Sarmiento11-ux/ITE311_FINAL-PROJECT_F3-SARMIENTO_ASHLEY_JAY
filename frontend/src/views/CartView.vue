<template>
  <main class="cart-page-wrapper">
    <div class="container">

      <!-- Breadcrumbs -->
      <div class="breadcrumb-trail">
        <router-link to="/">Home</router-link>
        <span class="breadcrumb-separator">/</span>
        <router-link to="/products">Catalog</router-link>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Shopping Cart</span>
      </div>

      <div class="cart-header-row">
        <div>
          <span class="section-eyebrow">YOUR CRAFT BASKET</span>
          <h1 class="cart-page-title">Shopping Cart</h1>
        </div>
        <button
          v-if="cart.items.length > 0"
          type="button"
          @click="clearAll"
          class="btn-text-danger"
          title="Remove all items from your basket"
        >
          🗑️ Clear Cart
        </button>
      </div>

      <!-- Notification Toast / Error Banner -->
      <div v-if="alertMsg" class="cart-alert-msg" :class="alertClass">
        {{ alertMsg }}
      </div>

      <!-- Empty Cart State -->
      <div v-if="cart.items.length === 0" class="empty-cart-card">
        <div class="empty-icon">🛍️</div>
        <h2>Your Shopping Cart is Empty</h2>
        <p>Discover unique handcrafted pottery, weaves, and accessories made by authentic Filipino artisans.</p>
        <router-link to="/products" class="btn btn-primary" style="margin-top: 20px;">
          Explore Handmade Crafts →
        </router-link>
      </div>

      <!-- Active Cart Grid Layout -->
      <div v-else class="cart-layout-grid">

        <!-- Left Column: Cart Items List -->
        <div class="cart-items-card">
          <div class="cart-items-header">
            <span>Product</span>
            <span>Quantity</span>
            <span>Subtotal</span>
          </div>

          <div class="cart-items-body">
            <div
              v-for="item in cart.items"
              :key="item.id || item.productId"
              class="cart-item-row"
            >
              <!-- Media & Details -->
              <div class="item-product-col">
                <router-link :to="`/products/${item.id || item.productId}`" class="item-thumbnail-link">
                  <img
                    :src="item.image || fallbackImg"
                    :alt="item.title"
                    class="item-thumbnail"
                    @error="handleImgError($event)"
                  >
                </router-link>

                <div class="item-meta">
                  <span class="item-seller">Maker: {{ item.seller || 'Artisan Maker' }}</span>
                  <h3 class="item-title">
                    <router-link :to="`/products/${item.id || item.productId}`">
                      {{ item.title }}
                    </router-link>
                  </h3>
                  <div class="item-price">₱{{ Number(item.price).toFixed(2) }}</div>
                  
                  <div class="item-stock-tag" :class="{ 'tag-low': item.stock <= 5 }">
                    {{ item.stock > 0 ? `${item.stock} available in stock` : 'Out of Stock' }}
                  </div>

                  <button
                    type="button"
                    class="btn-remove-mobile"
                    @click="removeItem(item.id || item.productId)"
                  >
                    Remove
                  </button>
                </div>
              </div>

              <!-- Quantity Selector -->
              <div class="item-qty-col">
                <div class="qty-control">
                  <button
                    type="button"
                    class="qty-btn"
                    :disabled="item.quantity <= 1"
                    @click="changeQuantity(item, item.quantity - 1)"
                    aria-label="Decrease quantity"
                  >−</button>
                  <span class="qty-val">{{ item.quantity }}</span>
                  <button
                    type="button"
                    class="qty-btn"
                    :disabled="item.quantity >= item.stock"
                    @click="changeQuantity(item, item.quantity + 1)"
                    aria-label="Increase quantity"
                  >+</button>
                </div>
              </div>

              <!-- Subtotal & Remove Action -->
              <div class="item-subtotal-col">
                <span class="item-subtotal-amount">
                  ₱{{ (Number(item.price) * Number(item.quantity)).toFixed(2) }}
                </span>
                <button
                  type="button"
                  class="btn-remove-desktop"
                  @click="removeItem(item.id || item.productId)"
                  title="Remove this item"
                >
                  ✕ Remove
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Right Column: Order Summary & Checkout Action -->
        <div class="cart-summary-card">
          <h3 class="summary-title">Order Summary</h3>

          <div class="summary-line">
            <span>Items Subtotal ({{ cart.totalCount }} items)</span>
            <span class="summary-val">₱{{ Number(cart.subtotal).toFixed(2) }}</span>
          </div>

          <div class="summary-line">
            <span>Estimated Delivery Fee</span>
            <span class="summary-val">
              <span v-if="cart.subtotal >= 1500" class="free-shipping-tag">FREE</span>
              <span v-else>₱150.00</span>
            </span>
          </div>

          <div v-if="cart.subtotal < 1500" class="shipping-progress-box">
            <p>Add <strong>₱{{ (1500 - cart.subtotal).toFixed(2) }}</strong> more to unlock <strong>Free Nationwide Shipping</strong>!</p>
            <div class="shipping-progress-bar">
              <div
                class="shipping-progress-fill"
                :style="{ width: Math.min(100, (cart.subtotal / 1500) * 100) + '%' }"
              ></div>
            </div>
          </div>

          <div class="summary-total-row">
            <span>Total Amount</span>
            <span class="summary-total-val">
              ₱{{ (cart.subtotal + (cart.subtotal >= 1500 ? 0 : 150)).toFixed(2) }}
            </span>
          </div>

          <div class="payment-method-pill">
            <span class="pill-icon">🚚</span>
            <div>
              <strong>Payment Option: Cash on Delivery</strong>
              <p>Pay upon safe arrival at your doorstep</p>
            </div>
          </div>

          <router-link to="/checkout" class="btn btn-primary btn-checkout-main">
            <span>Proceed to Checkout</span>
            <span class="btn-arrow">→</span>
          </router-link>

          <router-link to="/products" class="btn-continue-shopping">
            ← Continue Browsing Catalog
          </router-link>

          <div class="escrow-guarantee-note">
            <span>🛡️</span>
            <p><strong>Artisan Escrow Guarantee:</strong> Payment is securely held and only disbursed to the artisan maker after confirmed package delivery.</p>
          </div>
        </div>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { cart } from '../data/cart.js';

const alertMsg = ref('');
const alertClass = ref('');
const fallbackImg = 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80';

onMounted(async () => {
  await cart.loadCart();
});

const handleImgError = (event) => {
  event.target.src = fallbackImg;
};

const changeQuantity = async (item, newQty) => {
  alertMsg.value = '';
  const res = await cart.updateQuantity(item.id || item.productId, newQty);
  if (res && !res.success) {
    alertMsg.value = res.message;
    alertClass.value = 'alert-danger';
    setTimeout(() => { alertMsg.value = ''; }, 3500);
  }
};

const removeItem = async (productId) => {
  await cart.removeItem(productId);
  alertMsg.value = 'Item removed from basket.';
  alertClass.value = 'alert-info';
  setTimeout(() => { alertMsg.value = ''; }, 2500);
};

const clearAll = async () => {
  if (confirm('Are you sure you want to clear all items in your cart?')) {
    await cart.clearCart();
  }
};
</script>

<style scoped>
.cart-page-wrapper {
  padding: 3rem 0 5rem;
  background-color: var(--cm-cream, #FAF6F0);
  min-height: 80vh;
}

.breadcrumb-trail {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #7A6F68;
  margin-bottom: 24px;
}

.breadcrumb-trail a {
  color: #7A6F68;
  text-decoration: none;
}

.breadcrumb-trail a:hover {
  color: var(--cm-terracotta, #C25E3E);
}

.breadcrumb-current {
  color: var(--cm-charcoal, #2C221E);
  font-weight: 600;
}

.cart-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 28px;
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 16px;
}

.section-eyebrow {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--cm-terracotta, #C25E3E);
  text-transform: uppercase;
}

.cart-page-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  margin-top: 4px;
}

.btn-text-danger {
  background: none;
  border: none;
  color: #a33;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-text-danger:hover {
  opacity: 0.75;
}

.cart-alert-msg {
  padding: 12px 18px;
  border-radius: 8px;
  font-size: 0.9rem;
  margin-bottom: 20px;
}

.alert-danger {
  background-color: #FDF2F0;
  color: #C25E3E;
  border: 1px solid #F3C5BA;
}

.alert-info {
  background-color: #EEF5F0;
  color: var(--cm-forest, #3E5943);
  border: 1px solid #CCE0D2;
}

/* Empty State */
.empty-cart-card {
  text-align: center;
  padding: 60px 20px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  max-width: 580px;
  margin: 40px auto;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.empty-cart-card h2 {
  font-size: 1.6rem;
  color: var(--cm-charcoal, #2C221E);
  margin-bottom: 8px;
}

.empty-cart-card p {
  color: #7A6F68;
  font-size: 0.95rem;
  line-height: 1.6;
}

/* Layout Grid */
.cart-layout-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
  align-items: start;
}

@media (max-width: 960px) {
  .cart-layout-grid {
    grid-template-columns: 1fr;
  }
}

/* Items Card */
.cart-items-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  overflow: hidden;
}

.cart-items-header {
  display: grid;
  grid-template-columns: 1fr 140px 120px;
  padding: 16px 24px;
  background-color: #F9F5EF;
  border-bottom: 1px solid #E8DFD5;
  font-size: 0.8rem;
  font-weight: 700;
  color: #7A6F68;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.cart-item-row {
  display: grid;
  grid-template-columns: 1fr 140px 120px;
  padding: 24px;
  border-bottom: 1px solid #F0E8DE;
  align-items: center;
}

.cart-item-row:last-child {
  border-bottom: none;
}

.item-product-col {
  display: flex;
  gap: 16px;
  align-items: center;
}

.item-thumbnail {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #E8DFD5;
}

.item-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.item-seller {
  font-size: 0.76rem;
  color: #7A6F68;
  text-transform: uppercase;
  font-weight: 600;
}

.item-title a {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
  text-decoration: none;
  line-height: 1.35;
}

.item-title a:hover {
  color: var(--cm-terracotta, #C25E3E);
}

.item-price {
  font-size: 0.9rem;
  color: #555;
  font-weight: 600;
}

.item-stock-tag {
  font-size: 0.72rem;
  color: #2E7D32;
  font-weight: 600;
}

.item-stock-tag.tag-low {
  color: var(--cm-terracotta, #C25E3E);
}

.btn-remove-mobile {
  display: none;
  background: none;
  border: none;
  color: #a33;
  font-size: 0.75rem;
  text-decoration: underline;
  cursor: pointer;
  margin-top: 4px;
  text-align: left;
  padding: 0;
}

/* Quantity Control */
.qty-control {
  display: inline-flex;
  align-items: center;
  border: 1px solid #D6C8B8;
  border-radius: 6px;
  background: #ffffff;
  overflow: hidden;
}

.qty-btn {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  font-size: 1.1rem;
  color: var(--cm-charcoal, #2C221E);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.qty-btn:hover:not(:disabled) {
  background: #F0E8DE;
}

.qty-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.qty-val {
  min-width: 34px;
  text-align: center;
  font-weight: 600;
  font-size: 0.92rem;
}

/* Subtotal Column */
.item-subtotal-col {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
}

.item-subtotal-amount {
  font-size: 1.08rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.btn-remove-desktop {
  background: none;
  border: none;
  color: #999;
  font-size: 0.75rem;
  cursor: pointer;
  transition: color 0.15s;
}

.btn-remove-desktop:hover {
  color: #a33;
}

/* Summary Card */
.cart-summary-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.summary-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 12px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  font-size: 0.92rem;
  color: #665;
}

.summary-val {
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
}

.free-shipping-tag {
  background: #E8F5E9;
  color: #2E7D32;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}

.shipping-progress-box {
  background-color: #FAF6EF;
  border: 1px solid #E8DFD5;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #554;
}

.shipping-progress-bar {
  height: 6px;
  background: #E8DFD5;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 8px;
}

.shipping-progress-fill {
  height: 100%;
  background-color: var(--cm-forest, #3E5943);
  transition: width 0.3s ease;
}

.summary-total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  border-top: 2px solid #E8DFD5;
  padding-top: 16px;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.summary-total-val {
  font-size: 1.5rem;
  color: var(--cm-terracotta, #C25E3E);
}

.payment-method-pill {
  display: flex;
  gap: 10px;
  align-items: center;
  background: #FAF6F0;
  border: 1px solid #E8DFD5;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.82rem;
}

.payment-method-pill strong {
  display: block;
  color: var(--cm-charcoal, #2C221E);
}

.payment-method-pill p {
  color: #776;
  margin: 0;
}

.btn-checkout-main {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  padding: 14px;
  font-size: 1rem;
  text-decoration: none;
  border-radius: 8px;
}

.btn-continue-shopping {
  text-align: center;
  font-size: 0.85rem;
  color: #7A6F68;
  text-decoration: none;
  font-weight: 500;
}

.btn-continue-shopping:hover {
  color: var(--cm-terracotta, #C25E3E);
}

.escrow-guarantee-note {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  font-size: 0.78rem;
  color: #7A6F68;
  border-top: 1px solid #E8DFD5;
  padding-top: 12px;
  line-height: 1.45;
}

@media (max-width: 680px) {
  .cart-items-header {
    display: none;
  }
  .cart-item-row {
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .item-subtotal-col {
    flex-direction: row;
    justify-content: space-between;
  }
  .btn-remove-desktop {
    display: none;
  }
  .btn-remove-mobile {
    display: block;
  }
}
</style>
