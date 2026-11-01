import apiClient from './api.js';
import { sellerProductsData, sellerOrdersData } from '../data/dashboardData.js';

export const sellerService = {
  /**
   * Get products belonging to a seller.
   */
  async getProducts(sellerId = 2) {
    try {
      const response = await apiClient.get('/seller/products', {
        params: { seller_id: sellerId }
      });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data.map(p => ({
          id: p.id,
          name: p.name,
          category: p.category?.name || 'Handmade',
          categoryId: p.category_id,
          price: parseFloat(p.price),
          stock: p.stock,
          status: p.status === 'active' ? 'Active' : (p.status === 'out_of_stock' ? 'Out of Stock' : 'Draft'),
          salesCount: 0,
          image: p.image,
          description: p.description,
          material: p.material,
          dimensions: p.dimensions,
          isFromApi: true,
        }));
      }
    } catch (err) {
      console.warn('[SellerService] Could not fetch seller products from API, using fallback.', err.message);
    }
    return [...sellerProductsData];
  },

  /**
   * Get orders received by a seller.
   */
  async getOrders(sellerId = 2) {
    try {
      const response = await apiClient.get('/seller/orders', {
        params: { seller_id: sellerId }
      });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data.map(item => ({
          id: item.order?.id || item.id,
          orderItemId: item.id, // ID of the specific order_item for multi-seller tracking
          orderNumber: item.order?.order_number || `CM-${item.id}`,
          customerName: item.order?.user?.name || 'Valued Customer',
          productName: item.product_name,
          quantity: item.quantity,
          totalAmount: parseFloat(item.subtotal),
          status: (item.status || item.order?.status || 'pending').charAt(0).toUpperCase() + (item.status || item.order?.status || 'pending').slice(1),
          rawStatus: item.status || 'pending',
          orderDate: new Date(item.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          }),
          shippingAddress: item.order?.shipping_address || 'Registered Delivery Address',
          isFromApi: true,
        }));
      }
    } catch (err) {
      console.warn('[SellerService] Could not fetch seller orders from API, using fallback.', err.message);
    }
    return [...sellerOrdersData];
  },

  /**
   * Update the fulfillment status of a specific order item.
   * PATCH /api/seller/order-items/{id}/status
   */
  async updateOrderItemStatus(orderItemId, status) {
    const response = await apiClient.patch(`/seller/order-items/${orderItemId}/status`, {
      status: status.toLowerCase()
    });
    return response.data;
  },

  /**
   * Get stats for seller dashboard cards.
   */
  async getStats(sellerId = 2) {
    try {
      const response = await apiClient.get('/seller/stats', {
        params: { seller_id: sellerId }
      });
      if (response.data && response.data.success) {
        return response.data.data;
      }
    } catch (err) {
      console.warn('[SellerService] Could not fetch seller stats from API.', err.message);
    }
    return null;
  },

  /**
   * Save (create or update) product.
   */
  async saveProduct(productData, isEdit = false) {
    if (isEdit && productData.id) {
      const response = await apiClient.put(`/products/${productData.id}`, productData);
      return response.data;
    } else {
      const response = await apiClient.post('/products', productData);
      return response.data;
    }
  },

  /**
   * Delete product by ID.
   */
  async deleteProduct(productId) {
    const response = await apiClient.delete(`/products/${productId}`);
    return response.data;
  }
};
