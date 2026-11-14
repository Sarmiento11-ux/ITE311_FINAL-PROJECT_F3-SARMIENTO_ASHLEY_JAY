<template>
  <div class="invoice-modal-overlay" @click.self="$emit('close')">
    <div class="invoice-modal-container">
      <!-- Modal Action Bar (Hidden when printing) -->
      <div class="invoice-action-bar no-print">
        <div class="action-bar-info">
          <span class="action-icon">🧾</span>
          <div>
            <strong>Official Order Invoice</strong>
            <small>Ready to print or save as PDF</small>
          </div>
        </div>
        <div class="action-bar-buttons">
          <button type="button" class="btn btn-primary btn-sm" @click="handlePrint">
            🖨️ Print / Save as PDF
          </button>
          <button type="button" class="btn btn-secondary btn-sm" @click="$emit('close')">
            ✕ Close
          </button>
        </div>
      </div>

      <!-- Printable Invoice Sheet -->
      <div class="invoice-sheet" id="craftmarket-printable-receipt">
        <!-- Invoice Header -->
        <header class="invoice-header">
          <div class="invoice-brand">
            <h1 class="brand-title">CraftMarket</h1>
            <p class="brand-subtitle">Philippine Local Handmade Marketplace</p>
            <p class="brand-tax-note">Escrow-Protected Artisan Platform</p>
          </div>
          <div class="invoice-meta">
            <div class="invoice-badge">OFFICIAL RECEIPT</div>
            <table class="meta-table">
              <tr>
                <td><strong>Order No:</strong></td>
                <td><code class="order-code">{{ orderReference }}</code></td>
              </tr>
              <tr>
                <td><strong>Date:</strong></td>
                <td>{{ formattedDate }}</td>
              </tr>
              <tr>
                <td><strong>Payment:</strong></td>
                <td>{{ paymentMethodLabel }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  <span class="status-pill-print">{{ order.status || 'Confirmed' }}</span>
                </td>
              </tr>
            </table>
          </div>
        </header>

        <hr class="invoice-divider" />

        <!-- Parties Information -->
        <section class="invoice-parties-grid">
          <div class="party-box">
            <span class="party-label">CUSTOMER / SHIP TO:</span>
            <strong class="party-name">{{ customerName }}</strong>
            <p class="party-detail">{{ shippingAddress }}</p>
            <p v-if="contactNumber" class="party-detail">Contact: {{ contactNumber }}</p>
          </div>
          <div class="party-box">
            <span class="party-label">ARTISAN WORKSHOP / FULFILLED BY:</span>
            <strong class="party-name">{{ artisanName }}</strong>
            <p class="party-detail">CraftMarket Verified Artisan Workshop</p>
            <p v-if="trackingNumber" class="party-detail">
              Courier Tracking: <strong>{{ trackingNumber }}</strong>
            </p>
          </div>
        </section>

        <!-- Line Items Table -->
        <section class="invoice-items-section">
          <table class="invoice-items-table">
            <thead>
              <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Handcrafted Item Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Unit Price</th>
                <th style="width: 15%; text-align: right;">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in normalizedItems" :key="idx">
                <td>{{ idx + 1 }}</td>
                <td>
                  <strong>{{ item.name }}</strong>
                  <small v-if="item.category" class="item-category d-block">Category: {{ item.category }}</small>
                </td>
                <td style="text-align: center;">{{ item.quantity }}</td>
                <td style="text-align: right;">₱{{ Number(item.price).toFixed(2) }}</td>
                <td style="text-align: right; font-weight: 600;">₱{{ Number(item.subtotal).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </section>

        <!-- Financial Summary -->
        <section class="invoice-totals-wrapper">
          <div class="escrow-notice-box">
            <div class="escrow-badge-row">
              <span class="escrow-icon">🛡️</span>
              <strong>Escrow Protection Seal</strong>
            </div>
            <p>
              This transaction is protected by CraftMarket Escrow. Funds are held safely in trust until final package delivery is verified by the customer.
            </p>
            <small class="escrow-ref">Authentic Handmade Guarantee • Direct Maker Payout</small>
          </div>

          <table class="invoice-totals-table">
            <tr>
              <td>Items Subtotal:</td>
              <td style="text-align: right;">₱{{ Number(itemsSubtotal).toFixed(2) }}</td>
            </tr>
            <tr>
              <td>Standard Shipping:</td>
              <td style="text-align: right;">
                <span v-if="shippingFee === 0" style="color: #2E7D32; font-weight: 600;">FREE (₱1,500+ Qualified)</span>
                <span v-else>₱{{ Number(shippingFee).toFixed(2) }}</span>
              </td>
            </tr>
            <tr class="total-row">
              <td><strong>Total Paid:</strong></td>
              <td style="text-align: right;"><strong class="total-amount">₱{{ Number(totalAmount).toFixed(2) }}</strong></td>
            </tr>
            <!-- Seller Specific Breakdown -->
            <tr v-if="isSellerView && sellerCommissionRate != null" class="seller-fee-row">
              <td>Platform Commission ({{ Number(sellerCommissionRate).toFixed(1) }}%):</td>
              <td style="text-align: right; color: #C62828;">−₱{{ Number(platformFeeAmount).toFixed(2) }}</td>
            </tr>
            <tr v-if="isSellerView && netSellerPayout != null" class="seller-payout-row">
              <td><strong style="color: #2E7D32;">Your Net Artisan Payout:</strong></td>
              <td style="text-align: right;"><strong style="color: #2E7D32;">₱{{ Number(netSellerPayout).toFixed(2) }}</strong></td>
            </tr>
          </table>
        </section>

        <!-- Invoice Footer -->
        <footer class="invoice-footer">
          <p class="footer-thank-you">Thank you for supporting independent local Filipino artisans!</p>
          <div class="footer-meta-row">
            <span>CraftMarket E-Commerce Platform</span>
            <span>Generated on {{ printTimestamp }}</span>
            <span>https://craftmarket.local</span>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  userRole: {
    type: String,
    default: 'customer' // 'customer' | 'seller' | 'admin'
  }
});

defineEmits(['close']);

const isSellerView = computed(() => props.userRole === 'seller' || props.userRole === 'admin');

const orderReference = computed(() => {
  return props.order.orderNumber || props.order.order_number || props.order.id || 'CM-ORDER';
});

const formattedDate = computed(() => {
  return props.order.orderDate || props.order.date || props.order.created_at || new Date().toLocaleDateString();
});

const paymentMethodLabel = computed(() => {
  return props.order.paymentMethod || props.order.payment_method || 'Cash on Delivery (COD)';
});

const customerName = computed(() => {
  return props.order.customerName || props.order.customer_name || props.order.user?.name || 'Valued Shopper';
});

const shippingAddress = computed(() => {
  return props.order.deliveryAddress || props.order.shippingAddress || props.order.shipping_address || 'Customer Delivery Address';
});

const contactNumber = computed(() => {
  return props.order.contactNumber || props.order.contact_number || '';
});

const artisanName = computed(() => {
  return props.order.artisan || props.order.sellerName || props.order.shop_name || 'Philippine Artisan Workshop';
});

const trackingNumber = computed(() => {
  return props.order.trackingNumber || props.order.tracking_number || '';
});

const normalizedItems = computed(() => {
  if (Array.isArray(props.order.items) && props.order.items.length > 0) {
    return props.order.items.map(i => {
      const price = Number(i.unit_price ?? i.price ?? 0);
      const qty = Number(i.quantity ?? 1);
      return {
        name: i.product_name || i.product?.name || i.name || 'Handcrafted Item',
        category: i.category || i.product?.category?.name || '',
        quantity: qty,
        price: price,
        subtotal: Number(i.subtotal ?? (price * qty))
      };
    });
  }

  // Fallback single item representation
  const singlePrice = Number(props.order.totalAmount || props.order.total_amount || 0);
  const singleQty = Number(props.order.quantity || 1);
  return [{
    name: props.order.product || props.order.productName || 'Handcrafted Craft Item',
    category: '',
    quantity: singleQty,
    price: singleQty > 0 ? singlePrice / singleQty : singlePrice,
    subtotal: singlePrice
  }];
});

const itemsSubtotal = computed(() => {
  return normalizedItems.value.reduce((acc, curr) => acc + curr.subtotal, 0);
});

const totalAmount = computed(() => {
  return Number(props.order.totalAmount ?? props.order.total_amount ?? itemsSubtotal.value);
});

const shippingFee = computed(() => {
  if (props.order.shippingFee != null) return Number(props.order.shippingFee);
  if (props.order.shipping_fee != null) return Number(props.order.shipping_fee);
  return itemsSubtotal.value >= 1500 ? 0 : 150;
});

const sellerCommissionRate = computed(() => {
  return props.order.commissionRate ?? props.order.commission_rate ?? null;
});

const platformFeeAmount = computed(() => {
  if (props.order.platformFee != null) return Number(props.order.platformFee);
  if (props.order.platform_fee != null) return Number(props.order.platform_fee);
  if (sellerCommissionRate.value != null) {
    return itemsSubtotal.value * (Number(sellerCommissionRate.value) / 100);
  }
  return 0;
});

const netSellerPayout = computed(() => {
  if (props.order.sellerPayout != null) return Number(props.order.sellerPayout);
  if (props.order.seller_payout != null) return Number(props.order.seller_payout);
  return itemsSubtotal.value - platformFeeAmount.value;
});

const printTimestamp = computed(() => {
  return new Date().toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
});

const handlePrint = () => {
  window.print();
};
</script>

<style scoped>
.invoice-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(44, 34, 30, 0.65);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  overflow-y: auto;
}

.invoice-modal-container {
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  background: #FFF;
}

.invoice-action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #2F4533;
  color: #FFF;
}

.action-bar-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.action-icon {
  font-size: 1.4rem;
}

.action-bar-info strong {
  display: block;
  font-size: 0.95rem;
}

.action-bar-info small {
  color: #C8D6C8;
  font-size: 0.75rem;
}

.action-bar-buttons {
  display: flex;
  gap: 10px;
}

.invoice-sheet {
  background: #FFFFFF;
  padding: 36px 40px;
  overflow-y: auto;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  color: #2D2420;
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.brand-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #2F4533;
  letter-spacing: -0.02em;
  margin: 0 0 2px 0;
}

.brand-subtitle {
  font-size: 0.88rem;
  color: #C25E3E;
  font-weight: 600;
  margin: 0 0 2px 0;
}

.brand-tax-note {
  font-size: 0.75rem;
  color: #7A6F68;
  margin: 0;
}

.invoice-meta {
  text-align: right;
}

.invoice-badge {
  display: inline-block;
  background: #2F4533;
  color: #FFF;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  padding: 3px 10px;
  border-radius: 4px;
  margin-bottom: 8px;
}

.meta-table {
  font-size: 0.84rem;
  text-align: left;
}

.meta-table td {
  padding: 2px 6px;
}

.order-code {
  font-family: monospace;
  font-weight: 700;
  color: #2F4533;
}

.status-pill-print {
  background: #E8F5E9;
  color: #1B5E20;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: capitalize;
}

.invoice-divider {
  border: none;
  border-top: 2px solid #EAE3DB;
  margin: 20px 0;
}

.invoice-parties-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
  background: #FAF8F5;
  padding: 16px 20px;
  border-radius: 8px;
  border: 1px solid #EFE8DF;
}

.party-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #7A6F68;
  display: block;
  margin-bottom: 4px;
}

.party-name {
  font-size: 0.95rem;
  color: #2C221E;
  display: block;
  margin-bottom: 4px;
}

.party-detail {
  font-size: 0.82rem;
  color: #5C524B;
  margin: 0 0 2px 0;
  line-height: 1.4;
}

.invoice-items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 24px;
}

.invoice-items-table th {
  background: #F3EEE8;
  color: #2F4533;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 10px 12px;
  border-bottom: 2px solid #D9CFC4;
}

.invoice-items-table td {
  padding: 12px;
  font-size: 0.86rem;
  border-bottom: 1px solid #EFEAE3;
  color: #332B26;
}

.item-category {
  color: #8C7F77;
  font-size: 0.75rem;
  margin-top: 2px;
}

.invoice-totals-wrapper {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
  align-items: flex-start;
  margin-bottom: 28px;
}

.escrow-notice-box {
  background: #F7FAF7;
  border: 1px solid #D1E7D1;
  border-radius: 8px;
  padding: 14px 16px;
}

.escrow-badge-row {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #2E7D32;
  font-size: 0.88rem;
  margin-bottom: 4px;
}

.escrow-notice-box p {
  font-size: 0.78rem;
  color: #4A5B4C;
  line-height: 1.5;
  margin: 0 0 6px 0;
}

.escrow-ref {
  font-size: 0.72rem;
  color: #2E7D32;
  font-weight: 600;
}

.invoice-totals-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.86rem;
}

.invoice-totals-table td {
  padding: 6px 0;
  color: #4A3F38;
}

.total-row td {
  border-top: 2px solid #2F4533;
  border-bottom: 2px solid #2F4533;
  padding: 10px 0;
  color: #2F4533;
}

.total-amount {
  font-size: 1.25rem;
  color: #2F4533;
}

.seller-fee-row td {
  padding-top: 8px;
  font-size: 0.82rem;
}

.seller-payout-row td {
  border-top: 1px dashed #A5D6A7;
  padding: 8px 0;
  font-size: 0.92rem;
}

.invoice-footer {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #EAE3DB;
}

.footer-thank-you {
  font-size: 0.92rem;
  font-weight: 700;
  color: #2F4533;
  margin: 0 0 8px 0;
}

.footer-meta-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.72rem;
  color: #8C7F77;
}

/* ─────────────── Clean Print Styles ─────────────── */
@media print {
  /* Hide all dashboard/web UI elements */
  body * {
    visibility: hidden;
  }
  
  .no-print {
    display: none !important;
  }

  .invoice-modal-overlay {
    position: static;
    background: transparent;
    padding: 0;
    margin: 0;
  }

  .invoice-modal-container {
    max-width: 100%;
    max-height: none;
    box-shadow: none;
    border-radius: 0;
  }

  /* Make only the invoice sheet visible */
  #craftmarket-printable-receipt,
  #craftmarket-printable-receipt * {
    visibility: visible;
  }

  #craftmarket-printable-receipt {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 20mm;
    margin: 0;
    background: #FFF !important;
    color: #000 !important;
  }
}
</style>
