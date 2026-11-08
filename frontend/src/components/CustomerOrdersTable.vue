<template>
  <div>
    <div v-if="orders.length > 0" class="table-responsive-wrapper">
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
            <td><strong>{{ order.orderNumber || order.id }}</strong></td>
            <td>
              {{ order.productName }}
              <span v-if="order.items && order.items.length > 1" style="color: #7A6F68; font-size: 0.8rem; display: block;">
                {{ order.items.length }} distinct crafts ({{ order.quantity }} pcs total)
              </span>
              <span v-else-if="order.quantity > 1" style="color: #7A6F68; font-size: 0.82rem;">
                (×{{ order.quantity }})
              </span>
            </td>
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

    <!-- Empty Orders State -->
    <div v-else class="empty-orders-state" style="text-align: center; padding: 3rem 1.5rem; background: #FAF6F0; border-radius: 8px; border: 1px dashed #D9CFC4; margin-top: 8px;">
      <div style="font-size: 2.2rem; margin-bottom: 12px;">📦</div>
      <h4 style="font-size: 1.15rem; color: #2C221E; margin-bottom: 6px; font-weight: 700;">No handcrafted orders yet</h4>
      <p style="color: #7A6F68; font-size: 0.92rem; max-width: 440px; margin: 0 auto 18px; line-height: 1.5;">
        You haven't placed any orders yet. Discover unique pottery, handwoven textiles, and local crafts made by Filipino artisans.
      </p>
      <router-link to="/products" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
        Explore Marketplace Catalog →
      </router-link>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click.self="selectedOrder = null">
      <div class="modal-dialog-box">
        <div class="modal-header-row">
          <h3 class="modal-title">Order Summary: {{ selectedOrder.orderNumber || selectedOrder.id }}</h3>
          <button type="button" class="btn-modal-close" @click="selectedOrder = null">&times;</button>
        </div>

        <div style="font-size: 0.9rem; line-height: 1.7; color: #423B36;">
          <div v-if="selectedOrder.items && selectedOrder.items.length > 0" style="margin-bottom: 10px;">
            <strong>Ordered Items:</strong>
            <ul style="margin: 4px 0 0 18px; padding: 0;">
              <li v-for="item in selectedOrder.items" :key="item.id" style="margin-bottom: 2px;">
                {{ item.product_name || item.name }}
                <span style="color: #7A6F68;">— Qty: {{ item.quantity }} × ₱{{ Number(item.unit_price || item.price || 0).toFixed(2) }}</span>
              </li>
            </ul>
          </div>
          <p v-else><strong>Craft Item:</strong> {{ selectedOrder.productName }} (Qty: {{ selectedOrder.quantity }})</p>
          <p><strong>Artisan Seller:</strong> {{ selectedOrder.artisan }}</p>
          <p><strong>Order Date:</strong> {{ selectedOrder.date }}</p>
          <p><strong>Total Amount:</strong> ₱{{ Number(selectedOrder.totalAmount).toFixed(2) }}</p>
          <p><strong>Payment Method:</strong> {{ selectedOrder.paymentMethod }}</p>
          <p><strong>Tracking Number:</strong> <code v-if="selectedOrder.trackingNumber">{{ selectedOrder.trackingNumber }}</code><span v-else style="color: #7A6F68; font-style: italic;">Not yet assigned (Pending fulfillment)</span></p>
          <p><strong>Delivery Address:</strong> {{ selectedOrder.deliveryAddress }}</p>
          <p><strong>Current Status:</strong> <span class="status-pill" :class="getStatusClass(selectedOrder.status)">{{ selectedOrder.status }}</span></p>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: space-between; align-items: center;">
          <div>
            <button 
              v-if="selectedOrder.status && selectedOrder.status.toLowerCase() === 'pending'"
              type="button" 
              class="btn btn-sm" 
              style="background-color: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; font-weight: 600;"
              :disabled="cancelling"
              @click="handleCancelOrder(selectedOrder)"
            >
              {{ cancelling ? 'Cancelling…' : 'Cancel Order' }}
            </button>
          </div>
          <button type="button" class="btn btn-secondary btn-sm" @click="selectedOrder = null">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { orderService } from '../services/orderService.js';

defineProps({
  orders: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['order-updated']);

const selectedOrder = ref(null);
const cancelling = ref(false);

const handleCancelOrder = async (order) => {
  if (!confirm(`Are you sure you want to cancel order ${order.orderNumber || order.id}?`)) {
    return;
  }
  cancelling.value = true;
  try {
    await orderService.cancelOrder(order.id);
    order.status = 'Cancelled';
    alert('Order cancelled successfully.');
    selectedOrder.value = null;
    emit('order-updated');
  } catch (err) {
    alert(err.response?.data?.message || 'Could not cancel order. Please try again.');
  } finally {
    cancelling.value = false;
  }
};

const openOrderDetails = (order) => {
  selectedOrder.value = order;
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s.includes('delivered') || s.includes('complete')) return 'status-delivered';
  if (s.includes('transit') || s.includes('shipped')) return 'status-transit';
  if (s.includes('cancel') || s.includes('refund')) return 'status-cancelled';
  return 'status-pending';
};
</script>
