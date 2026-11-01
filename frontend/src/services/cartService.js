import apiClient from './api.js';

export const cartService = {
  /**
   * Fetch customer's cart items from Laravel API.
   * GET /api/cart
   */
  async getCart() {
    const response = await apiClient.get('/cart');
    return response.data?.data || { items: [], subtotal: 0, total_items: 0 };
  },

  /**
   * Add a product to the cart.
   * POST /api/cart
   */
  async addItem(productId, quantity = 1) {
    const response = await apiClient.post('/cart', {
      product_id: productId,
      quantity: quantity
    });
    return response.data;
  },

  /**
   * Update item quantity in the cart.
   * PUT /api/cart/{id}
   */
  async updateQuantity(cartItemId, quantity) {
    const response = await apiClient.put(`/cart/${cartItemId}`, {
      quantity: quantity
    });
    return response.data;
  },

  /**
   * Remove an item from the cart.
   * DELETE /api/cart/{id}
   */
  async removeItem(cartItemId) {
    const response = await apiClient.delete(`/cart/${cartItemId}`);
    return response.data;
  },

  /**
   * Clear all items from the cart.
   * DELETE /api/cart
   */
  async clearCart() {
    const response = await apiClient.delete('/cart');
    return response.data;
  }
};
