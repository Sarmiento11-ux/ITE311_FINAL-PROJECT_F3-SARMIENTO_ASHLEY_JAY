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
        return response.data.data.map(order => ({
          id: order.id,
          orderNumber: order.order_number,
          orderDate: new Date(order.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          }),
          productName: order.items && order.items.length > 0 
            ? order.items.map(i => i.product_name).join(', ') 
            : 'Handmade Items',
          itemsCount: order.items ? order.items.length : 1,
          totalAmount: parseFloat(order.total_amount),
          status: order.status.charAt(0).toUpperCase() + order.status.slice(1),
          paymentMethod: order.payment_method || 'Cash on Delivery',
          shippingAddress: order.shipping_address || 'Registered Address',
          items: order.items || [],
          isFromApi: true,
        }));
      }
    } catch (err) {
      console.warn('[OrderService] Could not fetch customer orders from API, using fallback.', err.message);
    }
    return customerOrdersData;
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
   * Create a new order.
   */
  async createOrder(payload) {
    const response = await apiClient.post('/orders', payload);
    return response.data;
  }
};
