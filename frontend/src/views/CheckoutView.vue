<template>
  <main class="checkout-page-wrapper">
    <div class="container">

      <!-- Breadcrumbs -->
      <div class="breadcrumb-trail">
        <router-link to="/">Home</router-link>
        <span class="breadcrumb-separator">/</span>
        <router-link to="/cart">Cart</router-link>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Secure Checkout</span>
      </div>

      <div class="checkout-header-row">
        <div>
          <span class="section-eyebrow">ORDER FULFILLMENT</span>
          <h1 class="checkout-page-title">Checkout & Delivery Details</h1>
        </div>
      </div>

      <!-- Error / Alert Banner -->
      <div v-if="alertMsg" class="checkout-alert-msg" :class="alertClass">
        {{ alertMsg }}
      </div>

      <!-- Empty Cart Redirection -->
      <div v-if="cart.items.length === 0 && !orderSuccess" class="empty-checkout-card">
        <div class="empty-icon">🛒</div>
        <h2>Your Cart is Empty</h2>
        <p>Please add handmade artisan crafts to your cart before proceeding to checkout.</p>
        <router-link to="/products" class="btn btn-primary" style="margin-top: 20px;">
          Explore Products →
        </router-link>
      </div>

      <!-- Checkout Form Grid -->
      <form v-else-if="!orderSuccess" @submit.prevent="handlePlaceOrder" class="checkout-layout-grid" novalidate>

        <!-- Left Column: Shipping & Contact Form -->
        <div class="checkout-form-card">
          <h2 class="form-section-title">1. Shipping & Contact Information</h2>

          <div class="form-row-2col">
            <div class="form-group">
              <label for="fullName" class="form-label">Full Name <span class="required">*</span></label>
              <input
                type="text"
                id="fullName"
                v-model="form.fullName"
                class="form-input"
                :class="{ 'input-error': errors.fullName }"
                placeholder="e.g. Maria Clara Santos"
                required
              >
              <span v-if="errors.fullName" class="field-error-msg">{{ errors.fullName }}</span>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email Address <span class="required">*</span></label>
              <input
                type="email"
                id="email"
                v-model="form.email"
                class="form-input"
                :class="{ 'input-error': errors.email }"
                placeholder="maria@example.ph"
                required
              >
              <span v-if="errors.email" class="field-error-msg">{{ errors.email }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="contactNumber" class="form-label">Contact Phone Number <span class="required">*</span></label>
            <input
              type="tel"
              id="contactNumber"
              v-model="form.contactNumber"
              class="form-input"
              :class="{ 'input-error': errors.contactNumber }"
              placeholder="+63 917 123 4567"
              required
            >
            <span v-if="errors.contactNumber" class="field-error-msg">{{ errors.contactNumber }}</span>
            <small class="field-help-text">Our delivery riders will send SMS notifications prior to package delivery.</small>
          </div>

          <div class="form-group">
            <label for="shippingAddress" class="form-label">Complete Delivery Address <span class="required">*</span></label>
            <textarea
              id="shippingAddress"
              v-model="form.shippingAddress"
              class="form-input textarea-address"
              :class="{ 'input-error': errors.shippingAddress }"
              rows="3"
              placeholder="House/Unit No., Street Name, Barangay, Municipality/City, Province, Postal Code"
              required
            ></textarea>
            <span v-if="errors.shippingAddress" class="field-error-msg">{{ errors.shippingAddress }}</span>
          </div>

          <div class="form-group">
            <label for="notes" class="form-label">Delivery Notes / Landmark (Optional)</label>
            <input
              type="text"
              id="notes"
              v-model="form.notes"
              class="form-input"
              placeholder="e.g. Leave with building reception, near yellow gate"
            >
          </div>

          <!-- Payment Section -->
          <h2 class="form-section-title" style="margin-top: 32px;">2. Payment Method</h2>

          <div class="payment-selection-box selected">
            <div class="payment-selection-header">
              <span class="payment-radio-bullet">●</span>
              <div class="payment-details">
                <div class="payment-name-row">
                  <strong class="payment-title">Cash on Delivery (COD)</strong>
                  <span class="status-pill status-cod">Active Option</span>
                </div>
                <p class="payment-desc">
                  Pay with exact cash directly to the courier upon receiving and inspecting your handcrafted packages.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Order Items Summary & Confirmation -->
        <div class="checkout-summary-card">
          <h3 class="summary-title">Order Items ({{ cart.totalCount }})</h3>

          <!-- Items Mini List -->
          <div class="checkout-items-list">
            <div
              v-for="item in cart.items"
              :key="item.id || item.productId"
              class="checkout-item-mini"
            >
              <img :src="item.image || fallbackImg" :alt="item.title" class="mini-img">
              <div class="mini-details">
                <h4 class="mini-title">{{ item.title }}</h4>
                <p class="mini-seller">Maker: {{ item.seller || 'Artisan' }}</p>
                <div class="mini-price-row">
                  <span>{{ item.quantity }} × ₱{{ Number(item.price).toFixed(2) }}</span>
                  <strong>₱{{ (Number(item.price) * Number(item.quantity)).toFixed(2) }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Subtotal Breakdown -->
          <div class="checkout-breakdown">
            <div class="breakdown-row">
              <span>Items Subtotal</span>
              <span>₱{{ Number(cart.subtotal).toFixed(2) }}</span>
            </div>

            <div class="breakdown-row">
              <span>Shipping & Handling</span>
              <span v-if="cart.subtotal >= 1500" class="free-shipping-tag">FREE (₱0.00)</span>
              <span v-else>₱150.00</span>
            </div>

            <div class="breakdown-total-row">
              <span>Grand Total</span>
              <span class="total-amount-highlight">
                ₱{{ (cart.subtotal + (cart.subtotal >= 1500 ? 0 : 150)).toFixed(2) }}
              </span>
            </div>
          </div>

          <!-- Submit Order Button -->
          <button
            type="submit"
            class="btn btn-primary btn-submit-order"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting">Processing Order…</span>
            <span v-else>Confirm & Place Order (COD)</span>
            <span v-if="!isSubmitting" class="btn-arrow">→</span>
          </button>

          <router-link to="/cart" class="btn-back-to-cart">
            ← Return to Basket
          </router-link>

          <div class="checkout-security-card">
            <span>🔒</span>
            <p><strong>Safe & Transparent:</strong> Total is calculated verified on the server. No hidden fees.</p>
          </div>
        </div>

      </form>

      <!-- Order Success Modal / Screen -->
      <div v-if="orderSuccess" class="order-success-card">
        <div class="success-icon-badge">✓</div>
        <span class="success-eyebrow">ORDER CONFIRMED</span>
        <h2 class="success-title">Salamat! Your Order is Placed.</h2>
        <p class="success-subtitle">
          Your order number is <strong class="order-number-tag">{{ placedOrder?.order_number }}</strong>.
          We have notified the artisan makers to begin handcrafting and preparing your packages.
        </p>

        <div class="order-success-details-box">
          <div class="detail-line">
            <span>Payment Method:</span>
            <strong>Cash on Delivery (COD)</strong>
          </div>
          <div class="detail-line">
            <span>Amount Due on Delivery:</span>
            <strong>₱{{ Number(placedOrder?.total_amount).toFixed(2) }}</strong>
          </div>
          <div class="detail-line">
            <span>Delivery Destination:</span>
            <span>{{ placedOrder?.shipping_address }}</span>
          </div>
          <div class="detail-line">
            <span>Contact Rider:</span>
            <span>{{ placedOrder?.contact_number }}</span>
          </div>
        </div>

        <div class="success-actions-row">
          <router-link to="/orders" class="btn btn-primary">
            View My Orders →
          </router-link>
          <router-link to="/products" class="btn btn-secondary">
            Continue Shopping
          </router-link>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { cart } from '../data/cart.js';
import { auth } from '../data/auth.js';
import { orderService } from '../services/orderService.js';

const router = useRouter();
const isSubmitting = ref(false);
const alertMsg = ref('');
const alertClass = ref('');
const orderSuccess = ref(false);
const placedOrder = ref(null);

const fallbackImg = 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80';

const form = reactive({
  fullName: '',
  email: '',
  contactNumber: '',
  shippingAddress: '',
  notes: ''
});

const errors = reactive({
  fullName: '',
  email: '',
  contactNumber: '',
  shippingAddress: ''
});

onMounted(async () => {
  await cart.loadCart();

  // Pre-populate with authenticated customer details
  const user = auth.user.value;
  if (user) {
    form.fullName = user.fullName || user.name || '';
    form.email = user.email || '';
    form.contactNumber = user.phone || user.contact_number || '';
    form.shippingAddress = user.address || '';
  }
});

const validateForm = () => {
  let isValid = true;
  errors.fullName = '';
  errors.email = '';
  errors.contactNumber = '';
  errors.shippingAddress = '';

  if (!form.fullName.trim()) {
    errors.fullName = 'Full Name is required.';
    isValid = false;
  }

  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!form.email.trim()) {
    errors.email = 'Email Address is required.';
    isValid = false;
  } else if (!emailRegex.test(form.email.trim())) {
    errors.email = 'Please enter a valid email format.';
    isValid = false;
  }

  if (!form.contactNumber.trim()) {
    errors.contactNumber = 'Contact Phone Number is required for delivery riders.';
    isValid = false;
  }

  if (!form.shippingAddress.trim()) {
    errors.shippingAddress = 'Shipping delivery address is required.';
    isValid = false;
  }

  return isValid;
};

const handlePlaceOrder = async () => {
  alertMsg.value = '';
  if (!validateForm()) return;

  if (cart.items.length === 0) {
    alertMsg.value = 'Your basket is empty. Please select products to order.';
    alertClass.value = 'alert-danger';
    return;
  }

  isSubmitting.value = true;

  try {
    const payload = {
      items: cart.items.map(i => ({
        product_id: i.productId || i.id,
        quantity: i.quantity
      })),
      shipping_address: form.shippingAddress.trim(),
      contact_number: form.contactNumber.trim(),
      notes: form.notes.trim() || undefined,
      payment_method: 'Cash on Delivery'
    };

    const res = await orderService.createOrder(payload);

    if (res && res.success) {
      placedOrder.value = res.data;
      orderSuccess.value = true;
      // Clear cart locally as server already cleared persistent cart
      cart.items.splice(0, cart.items.length);
      localStorage.removeItem('craftmarket_local_cart');
    } else {
      alertMsg.value = res.message || 'Failed to place order. Please review your cart.';
      alertClass.value = 'alert-danger';
    }
  } catch (err) {
    const backendMsg = err.response?.data?.message 
      || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : null)
      || err.message 
      || 'Failed to place order. Please try again.';
    alertMsg.value = backendMsg;
    alertClass.value = 'alert-danger';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.checkout-page-wrapper {
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

.checkout-header-row {
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

.checkout-page-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  margin-top: 4px;
}

.checkout-alert-msg {
  padding: 14px 20px;
  border-radius: 8px;
  font-size: 0.92rem;
  margin-bottom: 24px;
}

.alert-danger {
  background-color: #FDF2F0;
  color: #C25E3E;
  border: 1px solid #F3C5BA;
}

/* Form Grid */
.checkout-layout-grid {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 32px;
  align-items: start;
}

@media (max-width: 960px) {
  .checkout-layout-grid {
    grid-template-columns: 1fr;
  }
}

.checkout-form-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  padding: 28px;
}

.form-section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 12px;
  margin-bottom: 20px;
}

.form-row-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

@media (max-width: 600px) {
  .form-row-2col {
    grid-template-columns: 1fr;
  }
}

.form-group {
  margin-bottom: 18px;
}

.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
  margin-bottom: 6px;
}

.required {
  color: #c25e3e;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #D6C8B8;
  border-radius: 6px;
  font-size: 0.92rem;
  font-family: inherit;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: var(--cm-forest, #3E5943);
}

.input-error {
  border-color: #c25e3e !important;
  background-color: #fffaf8 !important;
}

.field-error-msg {
  display: block;
  font-size: 0.78rem;
  color: #c25e3e;
  margin-top: 4px;
}

.field-help-text {
  display: block;
  font-size: 0.78rem;
  color: #7A6F68;
  margin-top: 4px;
}

.textarea-address {
  resize: vertical;
}

/* Payment Selection Box */
.payment-selection-box {
  background: #FAF6EF;
  border: 2px solid var(--cm-forest, #3E5943);
  border-radius: 8px;
  padding: 16px 20px;
}

.payment-selection-header {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}

.payment-radio-bullet {
  color: var(--cm-forest, #3E5943);
  font-size: 1.2rem;
  line-height: 1;
}

.payment-name-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 4px;
}

.payment-title {
  font-size: 1rem;
  color: var(--cm-charcoal, #2C221E);
}

.status-cod {
  background: #E8F5E9;
  color: #2E7D32;
  font-size: 0.72rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}

.payment-desc {
  font-size: 0.85rem;
  color: #665;
  line-height: 1.5;
  margin: 0;
}

/* Summary Card */
.checkout-summary-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.summary-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 12px;
}

.checkout-items-list {
  max-height: 280px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding-right: 6px;
}

.checkout-item-mini {
  display: flex;
  gap: 12px;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #F0E8DE;
}

.mini-img {
  width: 54px;
  height: 54px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #E8DFD5;
}

.mini-details {
  flex: 1;
}

.mini-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
  line-height: 1.3;
  margin-bottom: 2px;
}

.mini-seller {
  font-size: 0.74rem;
  color: #7A6F68;
}

.mini-price-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  margin-top: 4px;
}

.checkout-breakdown {
  display: flex;
  flex-direction: column;
  gap: 10px;
  border-top: 1px solid #E8DFD5;
  padding-top: 14px;
}

.breakdown-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #665;
}

.free-shipping-tag {
  background: #E8F5E9;
  color: #2E7D32;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 700;
}

.breakdown-total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  border-top: 2px solid #E8DFD5;
  padding-top: 14px;
  font-size: 1rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.total-amount-highlight {
  font-size: 1.55rem;
  color: var(--cm-terracotta, #C25E3E);
}

.btn-submit-order {
  width: 100%;
  padding: 14px;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-submit-order:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-back-to-cart {
  text-align: center;
  font-size: 0.85rem;
  color: #7A6F68;
  text-decoration: none;
}

.btn-back-to-cart:hover {
  color: var(--cm-terracotta, #C25E3E);
}

.checkout-security-card {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  font-size: 0.78rem;
  color: #7A6F68;
  border-top: 1px solid #E8DFD5;
  padding-top: 12px;
  line-height: 1.45;
}

/* Order Success State */
.order-success-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #E8DFD5;
  padding: 50px 30px;
  text-align: center;
  max-width: 640px;
  margin: 40px auto;
}

.success-icon-badge {
  width: 64px;
  height: 64px;
  background-color: #E8F5E9;
  color: #2E7D32;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 auto 18px;
}

.success-eyebrow {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--cm-forest, #3E5943);
  text-transform: uppercase;
}

.success-title {
  font-size: 2rem;
  color: var(--cm-charcoal, #2C221E);
  margin: 6px 0 12px;
}

.success-subtitle {
  color: #665;
  font-size: 0.96rem;
  line-height: 1.6;
}

.order-number-tag {
  background: #F2ECE1;
  color: var(--cm-charcoal, #2C221E);
  padding: 2px 8px;
  border-radius: 4px;
  font-family: monospace;
}

.order-success-details-box {
  background-color: #FAF6EF;
  border: 1px solid #E8DFD5;
  border-radius: 8px;
  padding: 18px;
  margin: 28px 0;
  text-align: left;
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 0.9rem;
}

.detail-line {
  display: flex;
  justify-content: space-between;
  color: #443;
}

.success-actions-row {
  display: flex;
  justify-content: center;
  gap: 14px;
  margin-top: 10px;
}
</style>
