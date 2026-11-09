import apiClient from './api.js';
import { customerOrdersData } from '../data/dashboardData.js';

export const orderService = {
  /**
   * Get orders for a customer with fallback to sample data.
   */
  async getCustomerOrders(userId = null) {
    try {
      const params = userId ? { user_id: userId } : {};
      const response = await apiClient.get('/orders', { params });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data.map(order => {
          const formattedDate = new Date(order.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          });
          const sellerNames = order.items && order.items.length > 0
            ? [...new Set(order.items.map(i => i.seller?.name || i.seller?.shop_name || 'Local Artisan'))].join(', ')
            : 'Local Artisan';
          const totalQty = order.items && order.items.length > 0
            ? order.items.reduce((sum, i) => sum + (i.quantity || 1), 0)
            : 1;

          return {
            id: order.id,
            orderNumber: order.order_number,
            orderDate: formattedDate,
            date: formattedDate,
            artisan: sellerNames,
            productName: order.items && order.items.length > 0 
              ? order.items.map(i => i.product_name).join(', ') 
              : 'Handmade Items',
            itemsCount: order.items ? order.items.length : 1,
            quantity: totalQty,
            subtotal: order.subtotal != null ? parseFloat(order.subtotal) : null,
            shippingFee: order.shipping_fee != null ? parseFloat(order.shipping_fee) : null,
            totalAmount: parseFloat(order.total_amount),
            status: order.status.charAt(0).toUpperCase() + order.status.slice(1),
            paymentMethod: order.payment_method || 'Cash on Delivery',
            contactNumber: order.contact_number || null,
            shippingAddress: order.shipping_address || 'Registered Address',
            deliveryAddress: order.shipping_address || 'Registered Address',
            trackingNumber: order.tracking_number || null,
            items: order.items || [],
            isFromApi: true,
          };
        });
      }
    } catch (err) {
      console.warn('[OrderService] Could not fetch customer orders from API, using fallback.', err.message);
    }
    return [];
  },

  /**
   * Get single order by ID.
   */
  async getOrderById(orderId) {
    try {
      const response = await apiClient.get(`/orders/${orderId}`);
      if (response.data && response.data.success) {
        return response.data.data;
      }
    } catch (err) {
      console.warn(`[OrderService] Could not fetch order ${orderId}.`, err.message);
    }
    return null;
  },

  /**
   * Cancel an order (customer initiated for pending orders).
   */
  async cancelOrder(orderId) {
    const response = await apiClient.patch(`/orders/${orderId}/status`, { status: 'cancelled' });
    return response.data;
  },

  /**
   * Create a new order.
   */
  async createOrder(payload) {
    const response = await apiClient.post('/orders', payload);
    return response.data;
  }
};
