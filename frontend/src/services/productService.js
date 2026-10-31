import apiClient from './api.js';
import { sampleProducts } from '../data/products.js';

export const craftProducts = sampleProducts;

/**
 * Normalizes backend Product model to frontend format.
 * Ensures backward compatibility with existing templates.
 */
export function normalizeProduct(p) {
  if (!p) return null;

  const sellerName = typeof p.seller === 'object' && p.seller 
    ? p.seller.name 
    : (p.seller || 'Artisan Maker');

  const categoryName = typeof p.category === 'object' && p.category 
    ? p.category.name 
    : (p.category || 'Handmade');

  return {
    id: p.id,
    title: p.name || p.title || 'Handmade Craft',
    name: p.name || p.title || 'Handmade Craft',
    slug: p.slug || '',
    category: categoryName,
    categoryId: p.category_id || p.category?.id || 1,
    categorySlug: p.category?.slug || '',
    price: typeof p.price === 'string' ? parseFloat(p.price) : (p.price || 0),
    seller: sellerName,
    sellerId: p.seller_id || p.seller?.id || 1,
    sellerEmail: p.seller?.email || '',
    sellerAvatar: p.seller?.avatar || 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&q=80',
    location: p.location || p.seller?.address || 'Philippines',
    rating: p.rating || 4.9,
    reviewsCount: p.reviews_count || 24,
    badge: p.is_featured ? 'Featured' : (p.badge || (p.stock < 5 ? 'Low Stock' : '')),
    stock: p.stock !== undefined ? p.stock : 10,
    isAvailable: p.status === 'active' && p.stock > 0,
    status: p.status || 'active',
    image: p.image || 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
    images: Array.isArray(p.images) ? p.images : [],
    shortDesc: p.shortDesc || (p.description ? p.description.slice(0, 110) + '...' : ''),
    description: p.description || '',
    materials: p.material || p.materials || 'Locally sourced sustainable materials',
    dimensions: p.dimensions || 'Standard handcrafted dimensions',
    origin: p.origin || p.location || 'Philippines',
    isFromApi: Boolean(p.created_at),
  };
}

export const productService = {
  /**
   * Retrieve all products from Laravel API with fallback to sample data.
   */
  async getAll(params = {}) {
    try {
      const response = await apiClient.get('/products', { params });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data.map(normalizeProduct);
      }
    } catch (err) {
      console.warn('[ProductService] Backend API unreachable, falling back to local dataset.', err.message);
    }
    return sampleProducts.map(normalizeProduct);
  },

  /**
   * Retrieve single product by ID from Laravel API with fallback.
   */
  async getById(id) {
    const numId = parseInt(id, 10);
    try {
      const response = await apiClient.get(`/products/${numId}`);
      if (response.data && response.data.success && response.data.data) {
        return normalizeProduct(response.data.data);
      }
    } catch (err) {
      console.warn(`[ProductService] Could not fetch product ${id} from API, using fallback.`, err.message);
    }
    const fallback = sampleProducts.find(p => p.id === numId);
    return fallback ? normalizeProduct(fallback) : null;
  },

  /**
   * Retrieve active categories from Laravel API with fallback.
   */
  async getCategories() {
    try {
      const response = await apiClient.get('/categories');
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data;
      }
    } catch (err) {
      console.warn('[ProductService] Could not fetch categories from API, using default list.', err.message);
    }
    return [
      { id: 1, name: 'Ceramics & Pottery', slug: 'ceramics-pottery' },
      { id: 2, name: 'Woven Goods & Bags', slug: 'woven-goods-bags' },
      { id: 3, name: 'Artisan Jewelry', slug: 'artisan-jewelry' },
      { id: 4, name: 'Home Decor & Candles', slug: 'home-decor-candles' },
      { id: 5, name: 'Wooden Crafts', slug: 'wooden-crafts' },
      { id: 6, name: 'Baskets & Storage', slug: 'baskets-storage' },
    ];
  },

  /**
   * Retrieve featured products.
   */
  async getFeatured() {
    try {
      const response = await apiClient.get('/products', { params: { featured: 1 } });
      if (response.data && response.data.success && Array.isArray(response.data.data) && response.data.data.length > 0) {
        return response.data.data.map(normalizeProduct);
      }
    } catch {
      // Fallback
    }
    return sampleProducts.slice(0, 4).map(normalizeProduct);
  },

  /**
   * Retrieve related products.
   */
  async getRelated(category, currentId) {
    try {
      const response = await apiClient.get('/products', { params: { category } });
      if (response.data && response.data.success && Array.isArray(response.data.data)) {
        return response.data.data
          .filter(p => p.id !== currentId)
          .slice(0, 4)
          .map(normalizeProduct);
      }
    } catch {
      // Fallback
    }
    return sampleProducts
      .filter(p => p.id !== currentId && p.category === category)
      .slice(0, 4)
      .map(normalizeProduct);
  },

  /**
   * Create a new product via Laravel API.
   */
  async create(productData) {
    const response = await apiClient.post('/products', productData);
    return response.data;
  },

  /**
   * Update product via Laravel API.
   */
  async update(id, productData) {
    const response = await apiClient.put(`/products/${id}`, productData);
    return response.data;
  },

  /**
   * Delete product via Laravel API.
   */
  async delete(id) {
    const response = await apiClient.delete(`/products/${id}`);
    return response.data;
  }
};
