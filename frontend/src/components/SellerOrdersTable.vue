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
            <td><strong>{{ order.id }}</strong></td>
            <td>{{ order.customerName }}</td>
            <td>{{ order.product }}</td>
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
          <p><strong>Product:</strong> {{ activeOrder.product }} (Quantity: {{ activeOrder.quantity }})</p>
          <p><strong>Order Date:</strong> {{ activeOrder.orderDate }}</p>
          <p><strong>Total Transaction:</strong> ₱{{ Number(activeOrder.totalAmount).toFixed(2) }}</p>
          <p><strong>Shipping Address:</strong> {{ activeOrder.shippingAddress }}</p>
          <p><strong>Payout Status:</strong> <span class="status-pill status-published">{{ activeOrder.payoutStatus }}</span></p>

          <div style="margin-top: 16px; padding: 12px; background-color: #FAF6F0; border-radius: 8px;">
            <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px;">
              Update Delivery Status:
            </label>
            <select v-model="activeOrder.status" class="form-input" style="padding: 8px; font-size: 0.88rem;">
              <option value="Pending Dispatch">Pending Dispatch</option>
              <option value="In Transit">In Transit</option>
              <option value="Delivered">Delivered</option>
            </select>
          </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn btn-secondary btn-sm" @click="activeOrder = null">Close</button>
          <button type="button" class="btn btn-primary btn-sm" @click="saveStatus">Save Update</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  orders: {
    type: Array,
    required: true
  }
});

const activeOrder = ref(null);

const openOrderModal = (order) => {
  activeOrder.value = order;
};

const saveStatus = () => {
  alert(`Order ${activeOrder.value.id} updated to status: "${activeOrder.value.status}"`);
  activeOrder.value = null;
};

const getStatusClass = (status) => {
  const s = status.toLowerCase();
  if (s.includes('delivered')) return 'status-delivered';
  if (s.includes('transit')) return 'status-transit';
  return 'status-pending';
};
</script>
