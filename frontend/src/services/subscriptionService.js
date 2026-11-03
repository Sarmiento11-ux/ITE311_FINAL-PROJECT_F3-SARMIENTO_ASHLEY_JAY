import api from './api.js';

export const subscriptionService = {
  /**
   * Fetch all subscription plans (Basic vs Pro).
   */
  async getPlans() {
    const res = await api.get('/subscriptions/plans');
    return res.data;
  },

  /**
   * Get current authenticated seller's subscription status, listing count, and limits.
   */
  async getCurrentSubscription() {
    const res = await api.get('/seller/subscription');
    return res.data;
  },

  /**
   * Upgrade artisan account to Artisan Pro (₱299/mo).
   */
  async upgradeToPro(paymentMethod = 'GCash / Online Wallet') {
    const res = await api.post('/seller/subscription/upgrade', { payment_method: paymentMethod });
    return res.data;
  },

  /**
   * Cancel subscription (switch back to Basic Free tier).
   */
  async cancelSubscription() {
    const res = await api.post('/seller/subscription/cancel');
    return res.data;
  },

  /**
   * Admin: fetch subscription metrics, MRR, conversion rate, and billing records.
   */
  async getAdminSubscriptionMetrics() {
    const res = await api.get('/admin/subscriptions/metrics');
    return res.data;
  }
};
