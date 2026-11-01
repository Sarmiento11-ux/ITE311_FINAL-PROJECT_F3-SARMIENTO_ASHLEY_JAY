import apiClient from './api.js';

export const adminService = {
  // ─── Dashboard Stats & Activity ──────────────────────────────────────────
  async getStats() {
    const response = await apiClient.get('/admin/stats');
    return response.data?.data || null;
  },

  async getRecentActivity() {
    const response = await apiClient.get('/admin/recent-activity');
    return response.data?.data || { recent_orders: [], recent_users: [], activities: [] };
  },

  async getReports() {
    const response = await apiClient.get('/admin/reports');
    return response.data?.data || null;
  },

  // ─── Users Management ───────────────────────────────────────────────────
  async getUsers(params = {}) {
    const response = await apiClient.get('/users', { params });
    return response.data?.data || [];
  },

  async getUser(id) {
    const response = await apiClient.get(`/users/${id}`);
    return response.data?.data || null;
  },

  async updateUser(id, data) {
    const response = await apiClient.put(`/users/${id}`, data);
    return response.data;
  },

  async toggleUserActive(id) {
    const response = await apiClient.patch(`/users/${id}/toggle-active`);
    return response.data;
  },

  // ─── Sellers Oversight ──────────────────────────────────────────────────
  async getSellers(params = {}) {
    const response = await apiClient.get('/admin/sellers', { params });
    return response.data?.data || [];
  },

  async getSeller(id) {
    const response = await apiClient.get(`/admin/sellers/${id}`);
    return response.data?.data || null;
  },

  // ─── Products Oversight ─────────────────────────────────────────────────
  async getProducts(params = {}) {
    const response = await apiClient.get('/products', { params });
    return response.data?.data || [];
  },

  async updateProduct(id, data) {
    const response = await apiClient.put(`/products/${id}`, data);
    return response.data;
  },

  async deleteProduct(id) {
    const response = await apiClient.delete(`/products/${id}`);
    return response.data;
  },

  // ─── Categories Management ──────────────────────────────────────────────
  async getCategories() {
    const response = await apiClient.get('/categories', { params: { all: 1 } });
    return response.data?.data || [];
  },

  async createCategory(data) {
    const response = await apiClient.post('/categories', data);
    return response.data;
  },

  async updateCategory(id, data) {
    const response = await apiClient.put(`/categories/${id}`, data);
    return response.data;
  },

  async deleteCategory(id) {
    const response = await apiClient.delete(`/categories/${id}`);
    return response.data;
  },

  // ─── Orders Oversight ───────────────────────────────────────────────────
  async getOrders(params = {}) {
    const response = await apiClient.get('/orders', { params });
    return response.data?.data || [];
  },

  async getOrder(id) {
    const response = await apiClient.get(`/orders/${id}`);
    return response.data?.data || null;
  },

  async updateOrderStatus(id, status) {
    const response = await apiClient.patch(`/orders/${id}/status`, { status });
    return response.data;
  }
};
