<template>
  <main class="orders-page-wrapper">
    <div class="container">

      <!-- Breadcrumbs -->
      <div class="breadcrumb-trail">
        <router-link to="/">Home</router-link>
        <span class="breadcrumb-separator">/</span>
        <router-link to="/dashboard/customer">Dashboard</router-link>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">My Orders</span>
      </div>

      <div class="orders-header-row">
        <div>
          <span class="section-eyebrow">CUSTOMER ACCOUNT</span>
          <h1 class="orders-page-title">My Orders & Purchase History</h1>
          <p class="orders-page-subtitle">Track fulfillment progress, view artisan details, and access receipts.</p>
        </div>
        <router-link to="/products" class="btn btn-secondary btn-sm">
          Browse More Crafts →
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="orders-loading-state">
        <div class="loading-spinner"></div>
        <p>Loading your order history from the marketplace…</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="empty-orders-card">
        <div class="empty-icon">📦</div>
        <h2>No Orders Found</h2>
        <p>You haven't placed any handcrafted orders yet. Browse our curated collection to support local makers.</p>
        <router-link to="/products" class="btn btn-primary" style="margin-top: 18px;">
          Start Shopping →
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="orders-list">
        <article
          v-for="order in orders"
          :key="order.id"
          class="order-card"
        >
          <!-- Order Card Header -->
          <div class="order-card-header">
            <div class="header-meta-group">
              <div>
                <span class="meta-label">ORDER PLACED</span>
                <span class="meta-val">{{ order.orderDate }}</span>
              </div>
              <div>
                <span class="meta-label">TOTAL AMOUNT</span>
                <span class="meta-val highlight">₱{{ Number(order.totalAmount).toFixed(2) }}</span>
              </div>
              <div>
                <span class="meta-label">SHIP TO</span>
                <span class="meta-val recipient">{{ order.shippingAddress }}</span>
              </div>
            </div>

            <div class="header-number-group">
              <span class="order-id-tag">{{ order.orderNumber || `CM-${order.id}` }}</span>
              <span class="status-pill" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </div>
          </div>

          <!-- Order Card Items -->
          <div class="order-card-body">
            <div class="order-items-scroll">
              <div
                v-for="item in (order.items || [])"
                :key="item.id"
                class="order-item-row"
              >
                <img
                  :src="item.product?.image || fallbackImg"
                  :alt="item.product_name"
                  class="order-item-img"
                  @error="handleImgError($event)"
                >
                <div class="order-item-info">
                  <h4 class="order-item-name">{{ item.product_name }}</h4>
                  <p class="order-item-seller">
                    Artisan: <strong>{{ item.seller?.name || 'Local Artisan' }}</strong>
                  </p>
                  <span class="item-fulfillment-tag" :class="getStatusClass(item.status || order.status)">
                    Status: {{ item.status || order.status }}
                  </span>
                </div>
                <div class="order-item-pricing">
                  <span>{{ item.quantity }} × ₱{{ Number(item.unit_price || item.price).toFixed(2) }}</span>
                  <strong>₱{{ Number(item.subtotal).toFixed(2) }}</strong>
                </div>
              </div>
            </div>

            <div class="order-card-footer">
              <div class="payment-method-info">
                <span>Payment: <strong>{{ order.paymentMethod || 'Cash on Delivery' }}</strong></span>
                <span class="dot-separator">•</span>
                <span>Fulfillment: <strong>Doorstep Courier Delivery</strong></span>
              </div>

              <button
                type="button"
                class="btn btn-secondary btn-sm"
                @click="openDetailsModal(order)"
              >
                View Full Order Receipt
              </button>
            </div>
          </div>
        </article>
      </div>

      <!-- Order Details Receipt Modal -->
      <div v-if="selectedOrder" class="modal-overlay" @click.self="selectedOrder = null">
        <div class="modal-dialog-box">
          <div class="modal-header-row">
            <div>
              <span class="modal-eyebrow">OFFICIAL ORDER RECEIPT</span>
              <h3 class="modal-title">{{ selectedOrder.orderNumber || `CM-${selectedOrder.id}` }}</h3>
            </div>
            <button type="button" class="btn-modal-close" @click="selectedOrder = null">&times;</button>
          </div>

          <div class="receipt-content">
            <div class="receipt-meta-grid">
              <div>
                <span class="receipt-label">Order Date:</span>
                <p>{{ selectedOrder.orderDate }}</p>
              </div>
              <div>
                <span class="receipt-label">Order Status:</span>
                <p><span class="status-pill" :class="getStatusClass(selectedOrder.status)">{{ selectedOrder.status }}</span></p>
              </div>
              <div>
                <span class="receipt-label">Payment Method:</span>
                <p>{{ selectedOrder.paymentMethod || 'Cash on Delivery' }}</p>
              </div>
              <div>
                <span class="receipt-label">Shipping Destination:</span>
                <p>{{ selectedOrder.shippingAddress }}</p>
              </div>
            </div>

            <h4 class="receipt-section-heading">Itemized Craft Breakdown</h4>
            <div class="receipt-items-table">
              <div
                v-for="item in (selectedOrder.items || [])"
                :key="item.id"
                class="receipt-item-line"
              >
                <div>
                  <strong>{{ item.product_name }}</strong>
                  <div style="font-size: 0.8rem; color: #7A6F68;">
                    Artisan: {{ item.seller?.name || 'Local Artisan' }} | Qty: {{ item.quantity }}
                  </div>
                </div>
                <strong>₱{{ Number(item.subtotal).toFixed(2) }}</strong>
              </div>
            </div>

            <div class="receipt-summary-box">
              <div class="receipt-sum-line">
                <span>Items Subtotal:</span>
                <span>₱{{ (Number(selectedOrder.totalAmount) - (Number(selectedOrder.totalAmount) >= 1500 ? 0 : 150)).toFixed(2) }}</span>
              </div>
              <div class="receipt-sum-line">
                <span>Shipping Fee:</span>
                <span>{{ Number(selectedOrder.totalAmount) >= 1500 ? 'FREE (₱0.00)' : '₱150.00' }}</span>
              </div>
              <div class="receipt-sum-total">
                <span>Total Amount Due on Delivery:</span>
                <span class="total-highlight">₱{{ Number(selectedOrder.totalAmount).toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <div class="modal-footer-row">
            <button type="button" class="btn btn-secondary btn-sm" @click="selectedOrder = null">Close Receipt</button>
          </div>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { orderService } from '../services/orderService.js';

const orders = ref([]);
const isLoading = ref(true);
const selectedOrder = ref(null);

const fallbackImg = 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80';

onMounted(async () => {
  isLoading.value = true;
  try {
    orders.value = await orderService.getCustomerOrders();
  } catch (err) {
    console.warn('[OrdersView] Could not load orders:', err);
    orders.value = [];
  } finally {
    isLoading.value = false;
  }
});

const handleImgError = (event) => {
  event.target.src = fallbackImg;
};

const openDetailsModal = (order) => {
  selectedOrder.value = order;
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s.includes('delivered') || s.includes('complete')) return 'status-delivered';
  if (s.includes('ship') || s.includes('transit')) return 'status-transit';
  if (s.includes('cancel')) return 'status-cancelled';
  if (s.includes('process') || s.includes('confirm')) return 'status-processing';
  return 'status-pending';
};
</script>

<style scoped>
.orders-page-wrapper {
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

.orders-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 32px;
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 18px;
}

.section-eyebrow {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--cm-terracotta, #C25E3E);
  text-transform: uppercase;
}

.orders-page-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  margin-top: 4px;
}

.orders-page-subtitle {
  color: #7A6F68;
  font-size: 0.95rem;
  margin-top: 4px;
}

/* Loading & Empty States */
.orders-loading-state {
  text-align: center;
  padding: 60px 20px;
  color: #7A6F68;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E8DFD5;
  border-top-color: var(--cm-forest, #3E5943);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-orders-card {
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

.empty-orders-card h2 {
  font-size: 1.6rem;
  color: var(--cm-charcoal, #2C221E);
  margin-bottom: 8px;
}

.empty-orders-card p {
  color: #7A6F68;
  font-size: 0.95rem;
  line-height: 1.6;
}

/* Orders List */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DFD5;
  overflow: hidden;
}

.order-card-header {
  background-color: #F9F5EF;
  border-bottom: 1px solid #E8DFD5;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.header-meta-group {
  display: flex;
  gap: 28px;
  flex-wrap: wrap;
}

.meta-label {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  color: #7A6F68;
  letter-spacing: 0.05em;
  margin-bottom: 2px;
}

.meta-val {
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
}

.meta-val.highlight {
  color: var(--cm-terracotta, #C25E3E);
}

.meta-val.recipient {
  max-width: 260px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
}

.header-number-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.order-id-tag {
  font-family: monospace;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  font-size: 0.95rem;
}

.order-card-body {
  padding: 24px;
}

.order-items-scroll {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.order-item-row {
  display: flex;
  gap: 18px;
  align-items: center;
  padding-bottom: 18px;
  border-bottom: 1px solid #F0E8DE;
}

.order-item-row:last-child {
  border-bottom: none;
}

.order-item-img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #E8DFD5;
}

.order-item-info {
  flex: 1;
}

.order-item-name {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
  margin-bottom: 3px;
}

.order-item-seller {
  font-size: 0.82rem;
  color: #7A6F68;
  margin-bottom: 6px;
}

.item-fulfillment-tag {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
}

.order-item-pricing {
  text-align: right;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.order-item-pricing span {
  font-size: 0.82rem;
  color: #7A6F68;
}

.order-item-pricing strong {
  font-size: 1.05rem;
  color: var(--cm-charcoal, #2C221E);
}

.order-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid #E8DFD5;
  flex-wrap: wrap;
  gap: 12px;
}

.payment-method-info {
  font-size: 0.85rem;
  color: #665;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dot-separator {
  color: #bbb;
}

/* Status Pills */
.status-pill {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 50px;
  text-transform: capitalize;
}

.status-delivered {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.status-transit, .status-processing {
  background-color: #E3F2FD;
  color: #1565C0;
}

.status-pending {
  background-color: #FFF3E0;
  color: #E65100;
}

.status-cancelled {
  background-color: #FFEBEE;
  color: #C62828;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-dialog-box {
  background: #ffffff;
  border-radius: 12px;
  max-width: 580px;
  width: 100%;
  padding: 28px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 1px solid #E8DFD5;
  padding-bottom: 14px;
  margin-bottom: 18px;
}

.modal-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--cm-terracotta, #C25E3E);
  letter-spacing: 0.1em;
}

.modal-title {
  font-size: 1.4rem;
  color: var(--cm-charcoal, #2C221E);
  margin-top: 2px;
}

.btn-modal-close {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #999;
  cursor: pointer;
  line-height: 1;
}

.receipt-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  font-size: 0.88rem;
  margin-bottom: 18px;
}

.receipt-label {
  display: block;
  font-size: 0.74rem;
  color: #7A6F68;
  font-weight: 600;
}

.receipt-section-heading {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
  border-bottom: 1px solid #F0E8DE;
  padding-bottom: 6px;
  margin-bottom: 10px;
}

.receipt-items-table {
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 0.9rem;
  margin-bottom: 18px;
}

.receipt-item-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 8px;
  border-bottom: 1px dashed #E8DFD5;
}

.receipt-summary-box {
  background-color: #FAF6EF;
  border-radius: 8px;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.88rem;
}

.receipt-sum-line {
  display: flex;
  justify-content: space-between;
  color: #554;
}

.receipt-sum-total {
  display: flex;
  justify-content: space-between;
  border-top: 1px solid #E8DFD5;
  padding-top: 8px;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.total-highlight {
  font-size: 1.25rem;
  color: var(--cm-terracotta, #C25E3E);
}

.modal-footer-row {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>
