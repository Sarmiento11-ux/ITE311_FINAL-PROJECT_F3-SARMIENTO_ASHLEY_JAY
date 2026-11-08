<template>
  <div>
    <div v-if="orders.length > 0" class="table-responsive-wrapper">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Product Ordered</th>
            <th>Qty</th>
            <th>Gross Sale</th>
            <th>Payout</th>
            <th>Fulfillment Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td><strong>{{ order.orderNumber || order.id }}</strong></td>
            <td>{{ order.customerName }}</td>
            <td>{{ order.product || order.productName }}</td>
            <td>{{ order.quantity }}</td>
            <td><strong>₱{{ Number(order.totalAmount).toFixed(2) }}</strong></td>
            <td>
              <span class="status-pill" :class="getEscrowClass(order.escrowStatus)">
                {{ order.payoutStatus || 'Awaiting Payment' }}
              </span>
            </td>
            <td>
              <span class="status-pill" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </td>
            <td>
              <button 
                type="button" 
                class="btn-table-action btn-action-view" 
                @click="openOrderModal(order)"
              >
                <span>View Details</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty Orders State -->
    <div v-else class="empty-orders-state" style="text-align: center; padding: 3rem 1.5rem; background: #FAF6F0; border-radius: 8px; border: 1px dashed #D9CFC4; margin-top: 8px;">
      <div style="font-size: 2.2rem; margin-bottom: 12px;">📋</div>
      <h4 style="font-size: 1.15rem; color: #2C221E; margin-bottom: 6px; font-weight: 700;">No customer orders received yet</h4>
      <p style="color: #7A6F68; font-size: 0.92rem; max-width: 440px; margin: 0 auto; line-height: 1.5;">
        Once shoppers order your handmade creations, their orders and fulfillment dispatches will appear here.
      </p>
    </div>

    <!-- Seller Order Modal with Status Dispatch Update -->
    <div v-if="activeOrder" class="modal-overlay" @click.self="activeOrder = null">
      <div class="modal-dialog-box">
        <div class="modal-header-row">
          <h3 class="modal-title">Order Fulfillment: {{ activeOrder.id }}</h3>
          <button type="button" class="btn-modal-close" @click="activeOrder = null">&times;</button>
        </div>

        <div style="font-size: 0.9rem; line-height: 1.7; color: #423B36;">
          <p><strong>Customer Name:</strong> {{ activeOrder.customerName }}</p>
          <p><strong>Product:</strong> {{ activeOrder.product || activeOrder.productName }} (Quantity: {{ activeOrder.quantity }})</p>
          <p><strong>Order Number:</strong> {{ activeOrder.orderNumber }}</p>
          <p><strong>Order Date:</strong> {{ activeOrder.orderDate }}</p>
          <p><strong>Shipping Address:</strong> {{ activeOrder.shippingAddress }}</p>

          <div style="margin-top: 12px; padding: 12px; background-color: #F4FBF4; border: 1px solid #D6EBD6; border-radius: 8px;">
            <p style="margin: 0 0 4px;"><strong>Gross Sale:</strong> ₱{{ Number(activeOrder.grossAmount ?? activeOrder.totalAmount).toFixed(2) }}</p>
            <p style="margin: 0 0 4px;">
              <strong>Platform Commission ({{ Number(activeOrder.commissionRate ?? 8).toFixed(2) }}%):</strong>
              −₱{{ Number(activeOrder.platformFee ?? 0).toFixed(2) }}
            </p>
            <p style="margin: 0 0 6px; color: #2E7D32;">
              <strong>Your Payout:</strong> ₱{{ Number(activeOrder.sellerPayout ?? activeOrder.totalAmount).toFixed(2) }}
            </p>
            <p style="margin: 0;">
              <strong>Escrow Status:</strong>
              <span class="status-pill" :class="getEscrowClass(activeOrder.escrowStatus)">{{ activeOrder.payoutStatus }}</span>
            </p>
          </div>

          <div style="margin-top: 16px; padding: 12px; background-color: #FAF6F0; border-radius: 8px;">
            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px;">
              Update Artisan Fulfillment Status:
            </label>
            <select v-model="selectedStatus" class="form-input" style="padding: 8px; font-size: 0.88rem;">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="processing">Processing</option>
              <option value="shipped">In Transit / Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Tracking number field: visible only when marking as Shipped -->
          <div v-if="selectedStatus === 'shipped'" style="margin-top: 12px; padding: 12px; background-color: #F0F7FF; border-radius: 8px; border: 1px solid #BFDBFE;">
            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; color: #1E40AF;">
              Courier Tracking Number:
            </label>
            <input
              type="text"
              v-model="trackingNumber"
              placeholder="e.g. JRS-1234567890 / LBC-98765"
              class="form-input"
              style="padding: 8px; font-size: 0.88rem;"
            >
            <small style="color: #6B7280; font-size: 0.78rem;">Enter the tracking reference from your courier so the customer can monitor delivery.</small>
          </div>

          <div v-if="statusFeedback" style="margin-top: 10px; font-size: 0.84rem;" :style="{ color: feedbackIsError ? '#c25e3e' : '#2e7d32' }">
            {{ statusFeedback }}
          </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn btn-secondary btn-sm" @click="activeOrder = null">Close</button>
          <button type="button" class="btn btn-primary btn-sm" @click="saveStatus" :disabled="isSaving">
            {{ isSaving ? 'Saving…' : 'Save Status' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { sellerService } from '../services/sellerService.js';

const props = defineProps({
  orders: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['order-updated']);

const activeOrder = ref(null);
const selectedStatus = ref('pending');
const isSaving = ref(false);
const statusFeedback = ref('');
const feedbackIsError = ref(false);
const trackingNumber = ref('');

const openOrderModal = (order) => {
  activeOrder.value = order;
  selectedStatus.value = (order.rawStatus || order.status || 'pending').toLowerCase();
  statusFeedback.value = '';
  feedbackIsError.value = false;
  trackingNumber.value = '';
};

const saveStatus = async () => {
  if (!activeOrder.value) return;

  isSaving.value = true;
  statusFeedback.value = '';
  feedbackIsError.value = false;

  try {
    const itemId = activeOrder.value.orderItemId || activeOrder.value.id;
    const res = await sellerService.updateOrderItemStatus(itemId, selectedStatus.value, trackingNumber.value.trim() || null);

    if (res && res.success) {
      activeOrder.value.status = selectedStatus.value.charAt(0).toUpperCase() + selectedStatus.value.slice(1);
      activeOrder.value.rawStatus = selectedStatus.value;
      statusFeedback.value = 'Fulfillment status updated! Email notification sent.';

      feedbackIsError.value = false;
      emit('order-updated');
      setTimeout(() => {
        activeOrder.value = null;
      }, 1200);
    } else {
      statusFeedback.value = res.message || 'Failed to update status.';
      feedbackIsError.value = true;
    }
  } catch (err) {
    statusFeedback.value = err.response?.data?.message || 'Failed to update order fulfillment status.';
    feedbackIsError.value = true;
  } finally {
    isSaving.value = false;
  }
};

const getEscrowClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s === 'released_to_seller') return 'status-delivered';
  if (s === 'held') return 'status-published';
  if (s === 'refunded' || s === 'cancelled') return 'status-cancelled';
  return 'status-pending';
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s.includes('delivered')) return 'status-delivered';
  if (s.includes('transit') || s.includes('shipped') || s.includes('process')) return 'status-transit';
  if (s.includes('cancel')) return 'status-cancelled';
  return 'status-pending';
};
</script>
