import apiClient from './api.js';

export const paymentService = {
  /**
   * Create a PayMongo checkout session for an existing order.
   * Returns { checkout_url, session_id, order_number, payment_id }
   */
  async createCheckoutSession(orderId) {
    const response = await apiClient.post('/payments/checkout-session', {
      order_id: orderId,
    });
    return response.data;
  },

  /**
   * Get the current payment status for an order.
   * Always verified server-side — never trust query params.
   */
  async getPaymentStatus(orderId) {
    const response = await apiClient.get(`/payments/status/${orderId}`);
    return response.data;
  },
};
