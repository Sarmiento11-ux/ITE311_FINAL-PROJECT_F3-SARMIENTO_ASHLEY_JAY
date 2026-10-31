<template>
  <div>
    <div class="table-responsive-wrapper">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Craft Item</th>
            <th>Artisan Maker</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Order Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td><strong>{{ order.id }}</strong></td>
            <td>{{ order.productName }} <span v-if="order.quantity > 1" style="color: #7A6F68;">(×{{ order.quantity }})</span></td>
            <td>{{ order.artisan }}</td>
            <td>{{ order.date }}</td>
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
                @click="openOrderDetails(order)"
              >
                <span>View Details</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click.self="selectedOrder = null">
      <div class="modal-dialog-box">
        <div class="modal-header-row">
          <h3 class="modal-title">Order Summary: {{ selectedOrder.id }}</h3>
          <button type="button" class="btn-modal-close" @click="selectedOrder = null">&times;</button>
        </div>

        <div style="font-size: 0.9rem; line-height: 1.7; color: #423B36;">
          <p><strong>Craft Item:</strong> {{ selectedOrder.productName }} (Qty: {{ selectedOrder.quantity }})</p>
          <p><strong>Artisan Seller:</strong> {{ selectedOrder.artisan }}</p>
          <p><strong>Order Date:</strong> {{ selectedOrder.date }}</p>
          <p><strong>Total Amount:</strong> ₱{{ Number(selectedOrder.totalAmount).toFixed(2) }}</p>
          <p><strong>Payment Method:</strong> {{ selectedOrder.paymentMethod }}</p>
          <p><strong>Tracking Number:</strong> <code>{{ selectedOrder.trackingNumber }}</code></p>
          <p><strong>Delivery Address:</strong> {{ selectedOrder.deliveryAddress }}</p>
          <p><strong>Current Status:</strong> <span class="status-pill" :class="getStatusClass(selectedOrder.status)">{{ selectedOrder.status }}</span></p>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: flex-end;">
          <button type="button" class="btn btn-secondary btn-sm" @click="selectedOrder = null">Close</button>
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

const selectedOrder = ref(null);

const openOrderDetails = (order) => {
  selectedOrder.value = order;
};

const getStatusClass = (status) => {
  const s = status.toLowerCase();
  if (s.includes('delivered')) return 'status-delivered';
  if (s.includes('transit')) return 'status-transit';
  return 'status-pending';
};
</script>
