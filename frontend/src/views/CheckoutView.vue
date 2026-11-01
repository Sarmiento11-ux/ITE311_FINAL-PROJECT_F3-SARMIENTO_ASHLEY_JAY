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

      <!-- Empty Cart Redirection — only shown after loading is done -->
      <div v-if="!cart.isLoading && cart.items.length === 0 && !orderSuccess" class="empty-checkout-card">
        <div class="empty-icon">🛒</div>
        <h2>Your Cart is Empty</h2>
        <p>Please add handmade artisan crafts to your cart before proceeding to checkout.</p>
        <router-link to="/products" class="btn btn-primary" style="margin-top: 20px;">
          Explore Products →
        </router-link>
      </div>

      <!-- Cart Loading Spinner -->
      <div v-if="cart.isLoading" class="cart-loading-state">
        <div class="loading-spinner"></div>
        <p>Loading your cart…</p>
      </div>

      <!-- Checkout Form Grid -->
      <form v-else-if="!orderSuccess && cart.items.length > 0" @submit.prevent="handlePlaceOrder" class="checkout-layout-grid" novalidate>

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

          <!-- COD Option -->
          <div
            class="payment-selection-box"
            :class="{ selected: selectedPayment === 'cod' }"
            @click="selectedPayment = 'cod'"
            role="radio"
            :aria-checked="selectedPayment === 'cod'"
          >
            <div class="payment-selection-header">
              <span class="payment-radio-bullet">{{ selectedPayment === 'cod' ? 'â—' : '○' }}</span>
              <div class="payment-details">
                <div class="payment-name-row">
                  <strong class="payment-title">🚚 Cash on Delivery (COD)</strong>
                  <span class="status-pill status-cod">Available</span>
                </div>
                <p class="payment-desc">
                  Pay with exact cash directly to the courier upon receiving and inspecting your handcrafted packages.
                </p>
              </div>
            </div>
          </div>

          <!-- PayMongo Online Payment Option -->
          <div
            class="payment-selection-box paymongo-box"
            :class="{ selected: selectedPayment === 'paymongo' }"
            @click="selectedPayment = 'paymongo'"
            role="radio"
            :aria-checked="selectedPayment === 'paymongo'"
            style="margin-top: 12px;"
          >
            <div class="payment-selection-header">
              <span class="payment-radio-bullet">{{ selectedPayment === 'paymongo' ? 'â—' : '○' }}</span>
              <div class="payment-details">
                <div class="payment-name-row">
                  <strong class="payment-title">💳 Online Payment via PayMongo</strong>
                  <span class="status-pill status-online">Secure</span>
                </div>
                <p class="payment-desc">
                  Pay securely online using GCash, Maya, credit/debit card, or GrabPay. You will be redirected to PayMongo's secure hosted checkout page.
                </p>
                <div class="paymongo-badges">
                  <span class="pm-badge">GCash</span>
                  <span class="pm-badge">Maya</span>
                  <span class="pm-badge">Visa/MC</span>
                  <span class="pm-badge">GrabPay</span>
                </div>
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
                  <span>{{ item.quantity }} Ã— ₱{{ Number(item.price).toFixed(2) }}</span>
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
          </div>          <!-- Submit Order Button -->
          <button
            type="submit"
            class="btn btn-primary btn-submit-order"
            :class="{ 'btn-paymongo': selectedPayment === 'paymongo' }"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting">Processing…</span>
            <span v-else-if="selectedPayment === 'paymongo'">Proceed to Secure Payment</span>
            <span v-else>Confirm &amp; Place Order (COD)</span>
            <span v-if="!isSubmitting" class="btn-arrow">→</span>
          </button>

          <!-- PayMongo note -->
          <p v-if="selectedPayment === 'paymongo'" class="paymongo-redirect-note">
            You will be redirected to PayMongo secure checkout to complete payment.
          </p>

          <router-link to="/cart" class="btn-back-to-cart">
            â† Return to Basket
          </router-link>

          <div class="checkout-security-card">
            <span>ðŸ”’</span>
            <p><strong>Safe & Transparent:</strong> Total is calculated verified on the server. No hidden fees.</p>
          </div>
        </div>

      </form>

      <!-- Order Success Modal / Screen -->
      <div v-if="orderSuccess" class="order-success-card">
        <div class="success-icon-badge">âœ“</div>
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

        <div class="email-sent-notice" v-if="placedOrder?.user?.email || authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Order confirmation & receipt sent to <strong>{{ placedOrder?.user?.email || authUser?.email }}</strong></span>
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
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { cart } from '../data/cart.js';
import { auth } from '../data/auth.js';
import { authService } from '../services/authService.js';
import { orderService } from '../services/orderService.js';
import { paymentService } from '../services/paymentService.js';

const router = useRouter();
const isSubmitting  = ref(false);
const alertMsg      = ref('');
const alertClass    = ref('');
const orderSuccess  = ref(false);
const placedOrder   = ref(null);
const selectedPayment = ref('cod'); // 'cod' | 'paymongo'

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

// cart.isLoading is now reactive from cart.js

onMounted(async () => {
  
  await cart.loadCart(true);
  

  // Pre-populate with authenticated customer details
  // auth.js normalizeUser() already provides safe defaults for phone/address
  const user = auth.user.value || authService.getUser();
  if (user) {
    form.fullName      = user.fullName || user.name || '';
    form.email         = user.email || '';
    form.contactNumber = user.phone || user.contact_number || '+63 917 123 4567';
    form.shippingAddress = user.address || 'Metro Manila, Philippines';
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

  if (!isValid) {
    alertMsg.value = 'âš ï¸ Please complete all required fields marked with (*) before placing your order.';
    alertClass.value = 'alert-danger';
    // Scroll the alert into view
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  return isValid;
};

const handlePlaceOrder = async () => {
  alertMsg.value   = '';
  alertClass.value = '';

  if (!validateForm()) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  if (cart.items.length === 0) {
    alertMsg.value   = 'Your basket is empty. Please select products to order.';
    alertClass.value = 'alert-danger';
    return;
  }

  isSubmitting.value = true;

  try {
    // Step 1: Always create the order first regardless of payment method
    const payload = {
      items: cart.items.map(i => ({
        product_id: i.productId || i.id,
        quantity: i.quantity
      })),
      shipping_address: form.shippingAddress.trim(),
      contact_number:   form.contactNumber.trim(),
      notes:            form.notes.trim() || undefined,
      payment_method:   selectedPayment.value === 'paymongo'
        ? 'PayMongo Online Payment'
        : 'Cash on Delivery',
    };

    const res = await orderService.createOrder(payload);

    if (!res || !res.success) {
      alertMsg.value   = res?.message || 'Failed to place order. Please review your cart.';
      alertClass.value = 'alert-danger';
      return;
    }

    const order = res.data;

    // Step 2: Branch on payment method
    if (selectedPayment.value === 'paymongo') {
      alertMsg.value   = 'Creating secure payment session...';
      alertClass.value = 'alert-info';

      try {
        const pmRes = await paymentService.createCheckoutSession(order.id);

        if (pmRes && pmRes.success && pmRes.data?.checkout_url) {
          // Clear cart — order is saved, server cleared persistent cart
          cart.items.splice(0, cart.items.length);
          localStorage.removeItem('craftmarket_local_cart');
          // Redirect to PayMongo hosted checkout
          window.location.href = pmRes.data.checkout_url;
        } else {
          alertMsg.value   = pmRes?.message || 'Could not create payment session. Your order was saved — retry from My Orders.';
          alertClass.value = 'alert-danger';
        }
      } catch (pmErr) {
        const pmMsg = pmErr.response?.data?.message || pmErr.message || 'Payment session failed.';
        alertMsg.value   = pmMsg + ' Your order was saved. Try again from My Orders.';
        alertClass.value = 'alert-danger';
      }
    } else {
      // COD — order is complete
      placedOrder.value  = order;
      orderSuccess.value = true;
      cart.items.splice(0, cart.items.length);
      localStorage.removeItem('craftmarket_local_cart');
    }
  } catch (err) {
    const backendMsg = err.response?.data?.message
      || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(' ') : null)
      || err.message
      || 'Failed to place order. Please try again.';
    alertMsg.value   = backendMsg;
    alertClass.value = 'alert-danger';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.cart-loading-state {
  text-align: center;
  padding: 60px 20px;
  color: #7A6F68;
  font-size: 1rem;
}
.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e8d5b8;
  border-top-color: #8b5e3c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

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

/* ─── Payment Method Selector ─────────────────────────────────────────────── */
.payment-selection-box {
  border: 2px solid #e8d5b8;
  border-radius: 12px;
  padding: 16px 18px;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: #fff;
}
.payment-selection-box:hover {
  border-color: #c8a97a;
  background: #fffaf5;
}
.payment-selection-box.selected {
  border-color: #8b5e3c;
  background: #fffaf5;
}
.payment-radio-bullet {
  font-size: 1.2rem;
  color: #8b5e3c;
  margin-right: 12px;
  flex-shrink: 0;
}
.payment-selection-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.status-pill.status-online {
  background: #e3f2fd;
  color: #1565c0;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
}
.paymongo-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}
.pm-badge {
  background: #f0f4ff;
  color: #3d5afe;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #c5cae9;
}
.btn-paymongo {
  background: linear-gradient(135deg, #0070f3 0%, #0050c9 100%) !important;
}
.paymongo-redirect-note {
  font-size: 0.82rem;
  color: #7A6F68;
  text-align: center;
  margin-top: 8px;
  padding: 8px 12px;
  background: #f0f4ff;
  border-radius: 8px;
  border: 1px solid #c5cae9;
}
</style>