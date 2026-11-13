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

  /**
   * LOCAL SANDBOX ONLY — settle a simulated checkout session.
   * The backend rejects this unless APP_ENV=local/testing and
   * PAYMONGO_ALLOW_SIMULATION=true. No real money moves.
   */
  async simulatePayment(orderId, outcome = 'paid') {
    const response = await apiClient.post('/payments/simulate', {
      order_id: orderId,
      outcome,
    });
    return response.data;
  },
};
