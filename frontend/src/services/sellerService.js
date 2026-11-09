import apiClient from './api.js';

const ESCROW_LABELS = {
  pending: 'Awaiting Payment',
  held: 'In Escrow',
  released_to_seller: 'Released to Artisan',
  refunded: 'Refunded to Buyer',
  cancelled: 'Cancelled',
};

function escrowLabel(status) {
  return ESCROW_LABELS[status] || 'Awaiting Payment';
}

export const sellerService = {
  /**
   * Get products belonging to a seller.
   */
  async getProducts(sellerId = null) {
    try {
      const params = sellerId ? { seller_id: sellerId } : {};
      const response = await apiClient.get('/seller/products', { params });
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
    return [];
  },

  /**
   * Get orders received by a seller.
   */
  async getOrders(sellerId = null) {
    try {
      const params = sellerId ? { seller_id: sellerId } : {};
      const response = await apiClient.get('/seller/orders', { params });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data.map(item => {
          const grossAmount = parseFloat(item.subtotal) || 0;
          const commissionRate = item.commission_rate !== null && item.commission_rate !== undefined
            ? parseFloat(item.commission_rate)
            : 8;
          const storedFee = parseFloat(item.platform_fee);
          const platformFee = storedFee > 0 ? storedFee : Math.round(grossAmount * commissionRate) / 100;
          const storedPayout = parseFloat(item.seller_payout);
          const sellerPayout = storedPayout > 0 ? storedPayout : Math.round((grossAmount - platformFee) * 100) / 100;
          const escrowStatus = item.escrow_status
            || item.order?.escrow_status
            || (item.order?.payment_status === 'paid' ? 'held' : 'pending');

          return {
            id: item.order?.id || item.id,
            orderItemId: item.id, // ID of the specific order_item for multi-seller tracking
            orderNumber: item.order?.order_number || `CM-${item.id}`,
            customerName: item.order?.user?.name || 'Valued Customer',
            productName: item.product_name,
            product: item.product_name, // Map both for backward and forward compatibility
            quantity: item.quantity,
            totalAmount: grossAmount,
            grossAmount,
            commissionRate,
            platformFee,
            sellerPayout,
            escrowStatus,
            payoutStatus: escrowLabel(escrowStatus),
            paymentStatus: item.order?.payment_status || 'unpaid',
            status: (item.status || item.order?.status || 'pending').charAt(0).toUpperCase() + (item.status || item.order?.status || 'pending').slice(1),
            rawStatus: item.status || 'pending',
            trackingNumber: item.tracking_number || '',
            dispatchCode: item.dispatch_code || null,
            dispatchedAt: item.dispatched_at || null,
            dispatchConfirmedAt: item.dispatch_confirmed_at || null,
            orderDate: new Date(item.created_at).toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'short',
              day: 'numeric'
            }),
            shippingAddress: item.order?.shipping_address || 'Registered Delivery Address',
            isFromApi: true,
          };
        });
      }
    } catch (err) {
      console.warn('[SellerService] Could not fetch seller orders from API, using fallback.', err.message);
    }
    return [];
  },

  /**
   * Update the fulfillment status of a specific order item.
   * PATCH /api/seller/order-items/{id}/status
   */
  async updateOrderItemStatus(orderItemId, status, trackingNumber = null) {
    const payload = { status: status.toLowerCase() };
    if (trackingNumber) payload.tracking_number = trackingNumber;
    const response = await apiClient.patch(`/seller/order-items/${orderItemId}/status`, payload);
    return response.data;
  },

  /**
   * QR dispatch confirmation.
   */
  async generateDispatchCode(orderItemId) {
    const response = await apiClient.post(`/seller/order-items/${orderItemId}/dispatch-code`);
    return response.data;
  },

  async confirmDispatch(orderItemId, dispatchCode, trackingNumber = null) {
    const payload = { dispatch_code: dispatchCode };
    if (trackingNumber) payload.tracking_number = trackingNumber;
    const response = await apiClient.post(`/seller/order-items/${orderItemId}/dispatch-confirm`, payload);
    return response.data;
  },

  /**
   * Payout ledger and cash-out requests.
   */
  async getPayouts(sellerId = null) {
    try {
      const params = sellerId ? { seller_id: sellerId } : {};
      const response = await apiClient.get('/seller/payouts', { params });
      return response.data?.data || null;
    } catch (err) {
      console.warn('[SellerService] Could not fetch payout ledger from API.', err.message);
      return null;
    }
  },

  async requestPayout(payload) {
    const response = await apiClient.post('/seller/payouts', payload);
    return response.data;
  },

  async cancelPayout(payoutId) {
    const response = await apiClient.delete(`/seller/payouts/${payoutId}`);
    return response.data;
  },

  /**
   * Get stats for seller dashboard cards.
   */
  async getStats(sellerId = null) {
    try {
      const params = sellerId ? { seller_id: sellerId } : {};
      const response = await apiClient.get('/seller/stats', { params });
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
   * Upload an image file for a product.
   * POST /api/products/:id/image
   */
  async uploadProductImage(productId, file) {
    const formData = new FormData();
    formData.append('image', file);
    const response = await apiClient.post(`/products/${productId}/image`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  /**
   * Delete/remove the product image.
   * DELETE /api/products/:id/image
   */
  async deleteProductImage(productId) {
    const response = await apiClient.delete(`/products/${productId}/image`);
    return response.data;
  },

  /**
   * Delete product by ID.
   */
  async deleteProduct(productId) {
    const response = await apiClient.delete(`/products/${productId}`);
    return response.data;
  }
};
