<template>
  <div>
    <div class="table-responsive-wrapper">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Product Ordered</th>
            <th>Qty</th>
            <th>Total Amount</th>
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
          <p><strong>Order Date:</strong> {{ activeOrder.orderDate }}</p>
          <p><strong>Total Transaction:</strong> ₱{{ Number(activeOrder.totalAmount).toFixed(2) }}</p>
          <p><strong>Shipping Address:</strong> {{ activeOrder.shippingAddress }}</p>
          <p><strong>Payout Status:</strong> <span class="status-pill status-published">{{ activeOrder.payoutStatus }}</span></p>

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

const openOrderModal = (order) => {
  activeOrder.value = order;
  selectedStatus.value = (order.rawStatus || order.status || 'pending').toLowerCase();
  statusFeedback.value = '';
  feedbackIsError.value = false;
};

const saveStatus = async () => {
  if (!activeOrder.value) return;

  isSaving.value = true;
  statusFeedback.value = '';
  feedbackIsError.value = false;

  try {
    const itemId = activeOrder.value.orderItemId || activeOrder.value.id;
    const res = await sellerService.updateOrderItemStatus(itemId, selectedStatus.value);

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

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s.includes('delivered')) return 'status-delivered';
  if (s.includes('transit') || s.includes('shipped') || s.includes('process')) return 'status-transit';
  if (s.includes('cancel')) return 'status-cancelled';
  return 'status-pending';
};
</script>
