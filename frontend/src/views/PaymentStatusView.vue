<template>
  <main class="payment-status-wrapper">
    <div class="container">

      <!-- Loading State -->
      <div v-if="isLoading" class="status-card loading-card">
        <div class="status-spinner"></div>
        <h2>Verifying Payment…</h2>
        <p>Please wait while we confirm your payment with our secure payment provider.</p>
      </div>

      <!-- SUCCESS -->
      <div v-else-if="paymentStatus === 'paid'" class="status-card success-card">
        <div class="status-icon success-icon"></div>
        <span class="status-eyebrow">PAYMENT CONFIRMED</span>
        <span v-if="isSimulated" class="sandbox-badge">SIMULATED PAYMENT — NO REAL MONEY CHARGED</span>
        <h2 class="status-title">Bayad Na! Payment Successful</h2>
        <p class="status-subtitle">
          Your payment for order <strong class="order-tag">{{ orderNumber }}</strong> has been confirmed.
          The artisan makers have been notified to start preparing your handcrafted items.
        </p>
        <p v-if="isSimulated" class="simulated-note">
          This order was settled through the <strong>local sandbox simulator</strong>, not PayMongo.
          It is safe for demos and development, but no real money moved.
        </p>

        <div class="status-details-box" v-if="paymentInfo">
          <div class="detail-row">
            <span>Order Number:</span>
            <strong>{{ orderNumber }}</strong>
          </div>
          <div class="detail-row">
            <span>Payment Method:</span>
            <strong>{{ formatPaymentMethod(paymentInfo.payment_method) }}</strong>
          </div>
          <div class="detail-row">
            <span>Amount Paid:</span>
            <strong>₱{{ Number(paymentInfo.amount).toFixed(2) }}</strong>
          </div>
          <div class="detail-row" v-if="paymentInfo.paid_at">
            <span>Paid At:</span>
            <strong>{{ formatDate(paymentInfo.paid_at) }}</strong>
          </div>
        </div>

        <div class="email-sent-notice" v-if="authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Official payment receipt has been emailed to <strong>{{ authUser.email }}</strong></span>
        </div>

        <div class="status-actions">
          <router-link to="/orders" class="btn btn-primary">
            View My Orders →
          </router-link>
          <router-link to="/products" class="btn btn-secondary">
            Continue Shopping
          </router-link>
        </div>
      </div>

      <!-- CANCELLED / EXPIRED -->
      <div v-else-if="paymentStatus === 'cancelled' || urlStatus === 'cancelled'" class="status-card cancelled-card">
        <div class="status-icon cancelled-icon"></div>
        <span class="status-eyebrow">PAYMENT CANCELLED</span>
        <h2 class="status-title">Payment was Cancelled</h2>
        <p class="status-subtitle">
          Your payment for order <strong class="order-tag">{{ orderNumber }}</strong> was cancelled or you left the payment page.
          Your order is still saved — you can retry payment or choose Cash on Delivery.
        </p>
        <div class="email-sent-notice" v-if="authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Official payment receipt has been emailed to <strong>{{ authUser.email }}</strong></span>
        </div>

        <div class="status-actions">
          <router-link to="/orders" class="btn btn-primary">
            View My Orders →
          </router-link>
          <router-link to="/cart" class="btn btn-secondary">
            Return to Cart
          </router-link>
        </div>
      </div>

      <!-- FAILED -->
      <div v-else-if="paymentStatus === 'failed'" class="status-card failed-card">
        <div class="status-icon failed-icon">!</div>
        <span class="status-eyebrow">PAYMENT FAILED</span>
        <h2 class="status-title">Payment Could Not Be Processed</h2>
        <p class="status-subtitle">
          Your payment attempt for order <strong class="order-tag">{{ orderNumber }}</strong> was unsuccessful.
          Please try again or choose a different payment method.
        </p>
        <div class="email-sent-notice" v-if="authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Official payment receipt has been emailed to <strong>{{ authUser.email }}</strong></span>
        </div>

        <div class="status-actions">
          <router-link to="/orders" class="btn btn-primary">
            View My Orders →
          </router-link>
          <router-link to="/cart" class="btn btn-secondary">
            Return to Cart
          </router-link>
        </div>
      </div>

      <!-- PENDING (still processing) -->
      <div v-else-if="paymentStatus === 'pending'" class="status-card pending-card">
        <div class="status-icon pending-icon"></div>
        <span class="status-eyebrow">PAYMENT PENDING</span>
        <h2 class="status-title">Payment Is Being Processed</h2>
        <p class="status-subtitle">
          Your payment for order <strong class="order-tag">{{ orderNumber }}</strong> is still being verified.
          This may take a few moments. Please check your Orders page for updates.
        </p>
        <div class="email-sent-notice" v-if="authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Official payment receipt has been emailed to <strong>{{ authUser.email }}</strong></span>
        </div>

        <div class="status-actions">
          <router-link to="/orders" class="btn btn-primary">
            Check My Orders →
          </router-link>
          <button class="btn btn-secondary" @click="recheckStatus" :disabled="isRechecking">
            {{ isRechecking ? 'Checking…' : 'Refresh Status' }}
          </button>
        </div>
      </div>

      <!-- UNKNOWN / ERROR -->
      <div v-else class="status-card error-card">
        <div class="status-icon error-icon">?</div>
        <span class="status-eyebrow">UNKNOWN STATUS</span>
        <h2 class="status-title">Payment Status Unknown</h2>
        <p class="status-subtitle">
          We could not verify your payment status at this time. Please check your Orders page for the latest order and payment information.
        </p>
        <p v-if="errorMsg" class="error-detail">{{ errorMsg }}</p>
        <div class="email-sent-notice" v-if="authUser?.email" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0faf3; border: 1px solid #b8e4c6; color: #235438; padding: 12px 18px; border-radius: 8px; margin: 18px 0; font-size: 0.92rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          <span>Official payment receipt has been emailed to <strong>{{ authUser.email }}</strong></span>
        </div>

        <div class="status-actions">
          <router-link to="/orders" class="btn btn-primary">
            View My Orders →
          </router-link>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth.js';
import { useRoute } from 'vue-router';
import { paymentService } from '../services/paymentService.js';

const route        = useRoute();
const { user: authUser } = useAuth();
const isLoading    = ref(true);
const isRechecking = ref(false);
const paymentStatus = ref(null);
const paymentInfo  = ref(null);
const orderNumber  = ref('');
const errorMsg     = ref('');

// True when this payment was settled by the local sandbox simulator rather
// than the real PayMongo gateway.
const isSimulated = computed(() => {
  const p = paymentInfo.value;
  if (!p) return false;
  return p.payment_gateway === 'paymongo_sandbox'
    || String(p.payment_reference || '').startsWith('SIMULATED-');
});

// URL query params set by PayMongo redirect
const urlStatus  = route.query.status;     // 'success' | 'cancelled'
const orderId    = route.query.order_id;

onMounted(async () => {
  if (!orderId) {
    isLoading.value = false;
    errorMsg.value  = 'No order ID found in the URL. Please check your Orders page.';
    return;
  }
  await verifyPaymentStatus();
});

async function verifyPaymentStatus() {
  isLoading.value = true;
  try {
    const res = await paymentService.getPaymentStatus(orderId);
    if (res.success) {
      paymentStatus.value = res.data.payment?.status ?? res.data.payment_status;
      paymentInfo.value   = res.data.payment;
      orderNumber.value   = res.data.order_number;

      // Map order payment_status to payment status if no payment record yet
      if (!paymentStatus.value) {
        paymentStatus.value = res.data.payment_status === 'paid' ? 'paid' : (urlStatus || 'pending');
      }
    } else {
      errorMsg.value = res.message || 'Unable to verify payment.';
    }
  } catch (err) {
    errorMsg.value = err.message || 'Error verifying payment status.';
    // Fallback: use the URL status param if available
    paymentStatus.value = urlStatus || null;
  } finally {
    isLoading.value = false;
  }
}

async function recheckStatus() {
  isRechecking.value = true;
  await verifyPaymentStatus();
  isRechecking.value = false;
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}

function formatPaymentMethod(method) {
  if (!method) return 'Online Payment';
  const map = {
    gcash: 'GCash',
    card: 'Credit/Debit Card',
    paymaya: 'Maya (PayMaya)',
    grab_pay: 'GrabPay',
    billease: 'BillEase',
    dob: 'Online Banking',
    online: 'Online Payment',
    sandbox: 'Sandbox Simulation (Demo)',
  };
  return map[method.toLowerCase()] || method;
}
</script>

<style scoped>
.payment-status-wrapper {
  padding: 3rem 0 5rem;
  background-color: var(--cm-cream, #FAF6F0);
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.status-card {
  max-width: 600px;
  margin: 0 auto;
  background: #fff;
  border-radius: 18px;
  padding: 48px 40px;
  text-align: center;
  box-shadow: 0 4px 30px rgba(0,0,0,0.08);
  border: 1px solid #f0e8df;
}

/* Icons */
.status-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 auto 20px;
}
.success-icon  { background: #e8f5e9; color: #2e7d32; }
.cancelled-icon{ background: #fff3e0; color: #e65100; }
.failed-icon   { background: #ffebee; color: #c62828; font-size: 1.6rem; }
.pending-icon  { background: #e3f2fd; color: #1565c0; font-size: 1.5rem; }
.error-icon    { background: #f3e5f5; color: #6a1b9a; }

/* Spinner for loading */
.status-spinner {
  width: 52px;
  height: 52px;
  border: 5px solid #e8d5b8;
  border-top-color: #8b5e3c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 24px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Loading card */
.loading-card h2 { color: #7A6F68; }
.loading-card p  { color: #9c8a80; font-size: 0.95rem; }

.status-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #9c8a80;
  display: block;
  margin-bottom: 10px;
}

.status-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #2c2c2c;
  margin-bottom: 14px;
}

.status-subtitle {
  font-size: 0.97rem;
  color: #7A6F68;
  line-height: 1.6;
  margin-bottom: 24px;
}

.order-tag {
  color: #8b5e3c;
  font-family: monospace;
}

.status-details-box {
  background: #faf6f0;
  border: 1px solid #e8d5b8;
  border-radius: 10px;
  padding: 20px 24px;
  text-align: left;
  margin-bottom: 28px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  padding: 6px 0;
  border-bottom: 1px solid #f0e8df;
  color: #5a4f47;
}
.detail-row:last-child { border-bottom: none; }
.detail-row strong     { color: #2c2c2c; }

.status-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.error-detail {
  font-size: 0.85rem;
  color: #c62828;
  background: #ffebee;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 20px;
}

/* Local sandbox simulation badge */
.sandbox-badge {
  display: inline-block;
  margin-bottom: 14px;
  padding: 5px 12px;
  background: #fff4e5;
  border: 1px solid #f0b46a;
  border-radius: 999px;
  color: #8a4b00;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.simulated-note {
  font-size: 0.86rem;
  line-height: 1.6;
  color: #8a4b00;
  background: #fff8ef;
  border: 1px dashed #f0b46a;
  border-radius: 8px;
  padding: 12px 16px;
  margin: -8px 0 24px;
}

/* Responsive */
@media (max-width: 480px) {
  .status-card { padding: 32px 20px; }
  .status-title { font-size: 1.3rem; }
}
</style>
