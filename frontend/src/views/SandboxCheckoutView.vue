<template>
  <main class="sandbox-wrapper">
    <div class="container">

      <!-- Loading -->
      <div v-if="isLoading" class="sandbox-card loading-card">
        <div class="sandbox-spinner"></div>
        <h2>Loading your order…</h2>
        <p>Please wait while we prepare your checkout.</p>
      </div>

      <!-- Error -->
      <div v-else-if="!order" class="sandbox-card error-card">
        <div class="sandbox-icon error-icon">?</div>
        <span class="sandbox-eyebrow">CHECKOUT UNAVAILABLE</span>
        <h2>We could not load this order</h2>
        <p>{{ errorMsg || 'The order may not exist or does not belong to your account.' }}</p>
        <div class="sandbox-actions">
          <router-link to="/orders" class="btn btn-primary">View My Orders →</router-link>
        </div>
      </div>

      <!-- Checkout -->
      <div v-else class="sandbox-card">
        <span class="sandbox-eyebrow">SECURE CHECKOUT</span>
        <h1 class="sandbox-title">Complete your payment</h1>
        <p class="sandbox-subtitle">
          Order <strong class="order-tag">{{ order.order_number }}</strong>
          <span v-if="order.created_at"> · {{ formatDate(order.created_at) }}</span>
        </p>

        <!-- Order summary -->
        <div class="sandbox-summary">
          <div class="summary-row" v-for="item in order.items" :key="item.id">
            <div class="summary-item">
              <span class="summary-qty">{{ item.quantity }}×</span>
              <span class="summary-name">{{ item.product_name }}</span>
            </div>
            <strong>{{ formatPeso((item.unit_price ?? item.price) * item.quantity) }}</strong>
          </div>

          <div class="summary-row summary-row--muted" v-if="Number(order.shipping_fee) > 0">
            <span>Shipping &amp; handling</span>
            <strong>{{ formatPeso(order.shipping_fee) }}</strong>
          </div>

          <div class="summary-row summary-row--total">
            <span>Total</span>
            <strong>{{ formatPeso(order.total_amount) }}</strong>
          </div>
        </div>

        <!-- Payment method -->
        <div class="sandbox-method">
          <div class="method-logo">PAY</div>
          <div class="method-text">
            <strong>Online Payment</strong>
            <span>GCash, Maya, or credit/debit card</span>
          </div>
          <span class="method-amount">{{ formatPeso(order.total_amount) }}</span>
        </div>

        <!-- Already paid notice -->
        <div v-if="order.payment_status === 'paid'" class="sandbox-notice success">
          This order is already marked as <strong>paid</strong>. Nothing else to do here.
        </div>

        <!-- Inline feedback -->
        <div v-if="errorMsg" class="sandbox-notice error">{{ errorMsg }}</div>

        <div class="sandbox-actions" v-if="order.payment_status !== 'paid'">
          <button
            class="btn btn-primary sandbox-pay-btn"
            :disabled="isProcessing"
            @click="settle('paid')"
          >
            {{ isProcessing ? 'Processing…' : `Pay Now · ${formatPeso(order.total_amount)}` }}
          </button>
          <button
            class="btn btn-secondary"
            :disabled="isProcessing"
            @click="settle('cancelled')"
          >
            Cancel Payment
          </button>
        </div>

        <div class="sandbox-actions" v-else>
          <router-link to="/orders" class="btn btn-primary">View My Orders →</router-link>
        </div>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { orderService } from '../services/orderService.js';
import { paymentService } from '../services/paymentService.js';

const route = useRoute();
const router = useRouter();

const orderId = route.query.order_id;
const order = ref(null);
const isLoading = ref(true);
const isProcessing = ref(false);
const errorMsg = ref('');

onMounted(async () => {
  if (!orderId) {
    errorMsg.value = 'No order ID found in the URL.';
    isLoading.value = false;
    return;
  }

  const data = await orderService.getOrderById(orderId);
  if (data) {
    order.value = data;
  } else {
    errorMsg.value = 'Order not found or you do not have access to it.';
  }
  isLoading.value = false;
});

async function settle(outcome) {
  if (isProcessing.value || !order.value) return;

  isProcessing.value = true;
  errorMsg.value = '';

  try {
    const res = await paymentService.simulatePayment(order.value.id, outcome);

    if (!res || !res.success) {
      errorMsg.value = res?.message || 'Payment could not be completed. Please try again.';
      return;
    }

    router.push({
      path: '/payment/status',
      query: {
        status: outcome === 'paid' ? 'success' : 'cancelled',
        order_id: order.value.id,
      },
    });
  } catch (err) {
    errorMsg.value =
      err.response?.data?.message || err.message || 'Payment could not be completed. Please try again.';
  } finally {
    isProcessing.value = false;
  }
}

function formatPeso(value) {
  return '₱' + Number(value || 0).toFixed(2);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<style scoped>
.sandbox-wrapper {
  padding: 2.5rem 0 5rem;
  background-color: var(--cm-cream, #faf6f0);
  min-height: 80vh;
}

.sandbox-card {
  max-width: 640px;
  margin: 0 auto;
  background: #fff;
  border-radius: 18px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid #f0e8df;
}

.sandbox-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #9c8a80;
  display: block;
  margin-bottom: 10px;
}

.sandbox-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #2c2c2c;
  margin-bottom: 10px;
}

.sandbox-subtitle {
  font-size: 0.95rem;
  color: #7a6f68;
  margin-bottom: 24px;
}

.order-tag {
  color: #8b5e3c;
  font-family: monospace;
}

.sandbox-summary {
  background: #faf6f0;
  border: 1px solid #e8d5b8;
  border-radius: 12px;
  padding: 18px 22px;
  text-align: left;
  margin-bottom: 18px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  font-size: 0.9rem;
  padding: 8px 0;
  border-bottom: 1px solid #f0e8df;
  color: #5a4f47;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-item {
  display: flex;
  align-items: baseline;
  gap: 8px;
  min-width: 0;
}

.summary-qty {
  flex: 0 0 auto;
  font-weight: 700;
  color: #8b5e3c;
}

.summary-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.summary-row--muted {
  color: #9c8a80;
}

.summary-row--total {
  border-top: 2px solid #e8d5b8;
  border-bottom: none;
  margin-top: 6px;
  padding-top: 12px;
  font-size: 1rem;
  color: #2c2c2c;
}

.summary-row--total strong {
  color: #8b5e3c;
  font-size: 1.15rem;
}

.sandbox-method {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border: 1px solid #e8d5b8;
  border-radius: 12px;
  margin-bottom: 22px;
  text-align: left;
}

.method-logo {
  flex: 0 0 42px;
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: #2c2c2c;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  display: flex;
  align-items: center;
  justify-content: center;
}

.method-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.method-text strong {
  color: #2c2c2c;
  font-size: 0.92rem;
}

.method-text span {
  color: #9c8a80;
  font-size: 0.8rem;
  line-height: 1.4;
}

.method-amount {
  margin-left: auto;
  font-weight: 700;
  color: #2c2c2c;
  white-space: nowrap;
}

.sandbox-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sandbox-pay-btn {
  width: 100%;
}

.sandbox-notice {
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 0.88rem;
  margin-bottom: 16px;
  text-align: left;
}

.sandbox-notice.success {
  background: #e8f5e9;
  border: 1px solid #b8e4c6;
  color: #235438;
}

.sandbox-notice.error {
  background: #ffebee;
  border: 1px solid #f5c2c7;
  color: #b3261e;
}

/* Loading */
.sandbox-spinner {
  width: 48px;
  height: 48px;
  border: 5px solid #e8d5b8;
  border-top-color: #8b5e3c;
  border-radius: 50%;
  animation: sandbox-spin 0.8s linear infinite;
  margin: 0 auto 20px;
}

@keyframes sandbox-spin {
  to { transform: rotate(360deg); }
}

.loading-card h2 {
  color: #7a6f68;
}

.loading-card p {
  color: #9c8a80;
  font-size: 0.92rem;
}

/* Error */
.sandbox-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0 auto 18px;
}

.error-icon {
  background: #f3e5f5;
  color: #6a1b9a;
}

.error-card h2 {
  font-size: 1.4rem;
  color: #2c2c2c;
  margin-bottom: 10px;
}

.error-card p {
  color: #7a6f68;
  font-size: 0.93rem;
  margin-bottom: 22px;
}

@media (max-width: 560px) {
  .sandbox-card {
    padding: 28px 20px;
  }

  .sandbox-title {
    font-size: 1.3rem;
  }

  .summary-name {
    white-space: normal;
  }

  .method-amount {
    display: none;
  }
}
</style>
