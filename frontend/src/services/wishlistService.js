import apiClient from './api.js';

export const wishlistService = {
  /**
   * Retrieve all wishlisted items for the authenticated user.
   */
  async getWishlist() {
    const response = await apiClient.get('/wishlist');
    return response.data;
  },

  /**
   * Retrieve wishlisted product IDs only (for fast card indicators).
   */
  async getWishlistIds() {
    const response = await apiClient.get('/wishlist/ids');
    return response.data?.product_ids || [];
  },

  /**
   * Check wishlist status for a single product.
   */
  async checkStatus(productId) {
    const response = await apiClient.get(`/wishlist/status/${productId}`);
    return response.data?.in_wishlist || false;
  },

  /**
   * Add a product to the customer's wishlist.
   */
  async addToWishlist(productId) {
    const response = await apiClient.post('/wishlist', { product_id: productId });
    return response.data;
  },

  /**
   * Remove a product from the customer's wishlist.
   */
  async removeFromWishlist(productId) {
    const response = await apiClient.delete(`/wishlist/${productId}`);
    return response.data;
  },

  /**
   * Clear all items from customer's wishlist.
   */
  async clearWishlist() {
    const response = await apiClient.delete('/wishlist');
    return response.data;
  }
};
