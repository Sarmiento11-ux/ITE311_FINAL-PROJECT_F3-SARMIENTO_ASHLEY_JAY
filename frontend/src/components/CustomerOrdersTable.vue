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
              <strong style="color: #2C221E; display: block;">{{ order.productName }}</strong>
              <span v-if="order.items && order.items.length > 1" style="color: #7A6F68; font-size: 0.8rem; display: block;">
                {{ order.items.length }} distinct crafts ({{ order.quantity }} pcs total)
              </span>
              <span v-else-if="order.quantity > 1" style="color: #7A6F68; font-size: 0.82rem;">
                (×{{ order.quantity }})
              </span>
            </td>
            <td>{{ order.artisan }}</td>
            <td>{{ order.orderDate || order.date }}</td>
            <td><strong>₱{{ Number(order.totalAmount).toFixed(2) }}</strong></td>
            <td>
              <span class="status-pill" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </td>
            <td>
              <div style="display: flex; gap: 6px; align-items: center;">
                <button 
                  type="button" 
                  class="btn-table-action btn-action-view" 
                  @click="openOrderDetails(order)"
                >
                  <span>View Details</span>
                </button>
                <button 
                  type="button" 
                  class="btn-table-action" 
                  style="border-color: #2F4533; color: #2F4533; background: #FAF8F5;"
                  title="Print Official Order Receipt"
                  @click="invoiceOrder = order"
                >
                  <span>🧾</span>
                </button>
              </div>
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

        <!-- Visual Delivery Tracking Stepper -->
        <OrderTrackingStepper 
          :status="selectedOrder.status" 
          :trackingNumber="selectedOrder.trackingNumber" 
        />

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
          <p><strong>Order Date:</strong> {{ selectedOrder.orderDate || selectedOrder.date }}</p>
          <p><strong>Total Amount:</strong> ₱{{ Number(selectedOrder.totalAmount).toFixed(2) }}</p>
          <p><strong>Payment Method:</strong> {{ selectedOrder.paymentMethod }}</p>
          <p><strong>Tracking Number:</strong> <code v-if="selectedOrder.trackingNumber">{{ selectedOrder.trackingNumber }}</code><span v-else style="color: #7A6F68; font-style: italic;">Not yet assigned (Pending fulfillment)</span></p>
          <p><strong>Delivery Address:</strong> {{ selectedOrder.deliveryAddress || selectedOrder.shippingAddress }}</p>
          <p><strong>Current Status:</strong> <span class="status-pill" :class="getStatusClass(selectedOrder.status)">{{ selectedOrder.status }}</span></p>
        </div>

        <!-- In-Modal Cancellation Confirmation Box (M3 / L3 fix) -->
        <div v-if="confirmCancel" style="margin-top: 16px; padding: 14px; background: #FFF5F5; border: 1px solid #FEB2B2; border-radius: 8px;">
          <p style="margin: 0 0 6px; font-weight: 700; color: #9B2C2C; font-size: 0.92rem;">
            ⚠️ Cancel this handcrafted order?
          </p>
          <p style="margin: 0 0 12px; font-size: 0.85rem; color: #742A2A; line-height: 1.4;">
            Are you sure you want to cancel order <strong>{{ selectedOrder.orderNumber || selectedOrder.id }}</strong>? The reserved handcrafted items will be returned to the artisan's stock.
          </p>
          <div style="display: flex; gap: 8px;">
            <button 
              type="button" 
              class="btn btn-sm" 
              style="background-color: #E53E3E; color: #ffffff; border: none; font-weight: 600;"
              :disabled="cancelling"
              @click="executeCancelOrder(selectedOrder)"
            >
              {{ cancelling ? 'Cancelling…' : 'Yes, Cancel Order' }}
            </button>
            <button 
              type="button" 
              class="btn btn-secondary btn-sm" 
              :disabled="cancelling"
              @click="confirmCancel = false"
            >
              Keep Order
            </button>
          </div>
        </div>

        <!-- Feedback Notification Banner -->
        <div v-if="cancelFeedback" style="margin-top: 14px; padding: 10px 14px; border-radius: 6px; font-size: 0.88rem;" :style="cancelFeedback.success ? 'background: #DEF7EC; color: #03543F; border: 1px solid #BCF0DA;' : 'background: #FDE8E8; color: #9B1C1C; border: 1px solid #FBD5D5;'">
          {{ cancelFeedback.message }}
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div style="display: flex; gap: 8px; align-items: center;">
            <button 
              type="button" 
              class="btn btn-secondary btn-sm" 
              style="display: inline-flex; align-items: center; gap: 6px;"
              @click="invoiceOrder = selectedOrder"
            >
              <span>🧾 Print Receipt / Invoice</span>
            </button>
            <button 
              v-if="selectedOrder.status && selectedOrder.status.toLowerCase() === 'pending' && !confirmCancel"
              type="button" 
              class="btn btn-sm" 
              style="background-color: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; font-weight: 600;"
              :disabled="cancelling"
              @click="confirmCancel = true; cancelFeedback = null;"
            >
              Cancel Order
            </button>
          </div>
          <button type="button" class="btn btn-secondary btn-sm" @click="selectedOrder = null">Close</button>
        </div>
      </div>
    </div>

    <!-- Official Order Invoice & Receipt Modal -->
    <OrderInvoiceModal 
      v-if="invoiceOrder" 
      :order="invoiceOrder" 
      userRole="customer" 
      @close="invoiceOrder = null" 
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { orderService } from '../services/orderService.js';
import OrderTrackingStepper from './OrderTrackingStepper.vue';
import OrderInvoiceModal from './OrderInvoiceModal.vue';

defineProps({
  orders: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['order-updated']);

const selectedOrder = ref(null);
const invoiceOrder = ref(null);
const cancelling = ref(false);
const confirmCancel = ref(false);
const cancelFeedback = ref(null);

const executeCancelOrder = async (order) => {
  cancelling.value = true;
  cancelFeedback.value = null;
  try {
    await orderService.cancelOrder(order.id);
    order.status = 'Cancelled';
    confirmCancel.value = false;
    cancelFeedback.value = { success: true, message: 'Order has been successfully cancelled.' };
    emit('order-updated');
  } catch (err) {
    cancelFeedback.value = { success: false, message: err.response?.data?.message || 'Could not cancel order. Please try again.' };
  } finally {
    cancelling.value = false;
  }
};

const openOrderDetails = (order) => {
  selectedOrder.value = order;
  confirmCancel.value = false;
  cancelFeedback.value = null;
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s.includes('delivered') || s.includes('complete')) return 'status-delivered';
  if (s.includes('transit') || s.includes('shipped')) return 'status-transit';
  if (s.includes('cancel') || s.includes('refund')) return 'status-cancelled';
  return 'status-pending';
};
</script>
