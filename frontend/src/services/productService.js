
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';
import apiClient from './api.js';
import { sampleProducts } from '../data/products.js';

export const craftProducts = sampleProducts;

// Convert category names into URL-friendly slugs.
const slugify = (value = '') =>
  String(value)
    .toLowerCase()
    .trim()
    .replace(/&/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');

// Give each sample seller a consistent ID for fallback filtering.
const fallbackSellerNames = [
  ...new Set(sampleProducts.map(product => product.seller).filter(Boolean))
];

const fallbackSellers = fallbackSellerNames.map((name, index) => ({
  id: index + 1,
  name,
  shop_name: name,
}));

/**
 * Normalizes backend Product model to frontend format.
 * Ensures backward compatibility with existing templates.
 */
export function normalizeProduct(p) {
  if (!p) return null;

  const sellerName = typeof p.seller === 'object' && p.seller
    ? p.seller.name
    : (p.seller || 'Artisan Maker');

  const sellerShop = p.seller?.seller_profile?.shop_name
    || p.seller?.sellerProfile?.shop_name
    || null;

  const categoryName = typeof p.category === 'object' && p.category
    ? p.category.name
    : (p.category || 'Handmade');

  const stock = Number(p.stock ?? 10);
  const status = p.status || 'active';

  return {
    id: p.id,
    title: p.name || p.title || 'Handmade Craft',
    name: p.name || p.title || 'Handmade Craft',
    slug: p.slug || '',
    category: categoryName,
    categoryId: p.category_id || p.category?.id || 1,
    categorySlug: p.category?.slug || slugify(categoryName),
    price: Number(p.price) || 0,
    seller: sellerName,
    sellerShop,
    sellerId: p.seller_id || p.seller?.id || p.sellerId || 1,
    sellerEmail: p.seller?.email || '',
    sellerAvatar: p.seller?.avatar || 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&q=80',
    sellerPlan: p.seller?.subscription_plan || 'basic',
    seller_plan: p.seller?.subscription_plan || 'basic',
    location: p.location || p.seller?.address || 'Philippines',
    rating: p.avg_rating !== undefined && p.avg_rating !== null
      ? Number(p.avg_rating)
      : (Number(p.rating) || 0),
    reviewsCount: p.review_count !== undefined && p.review_count !== null
      ? Number(p.review_count)
      : (p.reviews_count ?? (p.created_at ? 0 : 24)),
    badge: p.is_featured ? 'Featured' : (p.badge || (stock < 5 ? 'Low Stock' : '')),
    stock,
    isAvailable: p.isAvailable !== undefined
      ? Boolean(p.isAvailable)
      : status === 'active' && stock > 0,
    status,
    image: p.image || defaultPlaceholder,
    images: Array.isArray(p.images) ? p.images : [],
    shortDesc: p.shortDesc || (p.description ? p.description.slice(0, 110) + '...' : ''),
    description: p.description || '',
    materials: p.material || p.materials || 'Locally sourced sustainable materials',
    dimensions: p.dimensions || 'Standard handcrafted dimensions',
    origin: p.origin || p.location || 'Philippines',
    isFromApi: Boolean(p.created_at),
  };
}

/**
 * Prepare sample products with consistent seller IDs.
 */
function getFallbackProducts() {
  return sampleProducts.map(product => {
    const sellerId = fallbackSellers.find(
      seller => seller.name === product.seller
    )?.id || 1;

    return normalizeProduct({
      ...product,
      seller_id: sellerId,
    });
  });
}

/**
 * Apply Shop filters to fallback products.
 */
function filterFallbackProducts(products, params = {}) {
  let filtered = [...products];

  const search = String(params.search || '').trim().toLowerCase();
  const category = String(params.category || '').trim().toLowerCase();
  const sellerId = params.seller_id;
  const minPrice = params.min_price;
  const maxPrice = params.max_price;
  const availability = params.availability || 'all';

  // Search by product name, description, seller, or category.
  if (search) {
    filtered = filtered.filter(product =>
      [
        product.title,
        product.description,
        product.shortDesc,
        product.seller,
        product.category,
      ].some(value => String(value || '').toLowerCase().includes(search))
    );
  }

  // Match category by name or slug.
  if (category && category !== 'all') {
    filtered = filtered.filter(product => {
      const categoryName = product.category.toLowerCase();
      const categorySlug = slugify(product.category);

      return categoryName === category || categorySlug === category;
    });
  }

  // Filter by seller ID.
  if (sellerId !== undefined && sellerId !== null && sellerId !== '') {
    filtered = filtered.filter(
      product => Number(product.sellerId) === Number(sellerId)
    );
  }

  // Filter by minimum price.
  if (minPrice !== undefined && minPrice !== null && minPrice !== '') {
    filtered = filtered.filter(
      product => product.price >= Number(minPrice)
    );
  }

  // Filter by maximum price.
  if (maxPrice !== undefined && maxPrice !== null && maxPrice !== '') {
    filtered = filtered.filter(
      product => product.price <= Number(maxPrice)
    );
  }

  // Filter by availability.
  if (availability === 'in_stock') {
    filtered = filtered.filter(product => product.isAvailable && product.stock > 0);
  } else if (availability === 'out_of_stock') {
    filtered = filtered.filter(product => !product.isAvailable || product.stock <= 0);
  }

  // Sorting.
  switch (params.sort) {
    case 'price_asc':
      filtered.sort((a, b) => a.price - b.price);
      break;

    case 'price_desc':
      filtered.sort((a, b) => b.price - a.price);
      break;

    case 'name_asc':
      filtered.sort((a, b) => a.title.localeCompare(b.title));
      break;

    case 'rating_desc':
      filtered.sort((a, b) => b.rating - a.rating);
      break;

    case 'latest':
    default:
      filtered.sort((a, b) => Number(b.id) - Number(a.id));
      break;
  }

  return filtered;
}

/**
 * Retrieve products with search, filters, sorting, and pagination.
 * Returns { items: Array, pagination: Object }.
 */
export const productService = {
  async getAll(params = {}) {
    try {
      const response = await apiClient.get('/products', { params });

      if (
        response.data &&
        response.data.success &&
        Array.isArray(response.data.data)
      ) {
        const items = response.data.data.map(normalizeProduct);

        return {
          items,
          isFallback: false,
          pagination: response.data.pagination || {
            current_page: 1,
            last_page: 1,
            per_page: items.length,
            total: items.length,
            from: items.length ? 1 : null,
            to: items.length || null,
            has_more: false,
          },
        };
      }
    } catch (err) {
      console.warn(
        '[ProductService] Backend API unreachable, falling back to local dataset.',
        err.message
      );
    }

    // Apply the same filters when the API is unavailable.
    const allProducts = getFallbackProducts();
    const filteredProducts = filterFallbackProducts(allProducts, params);

    // Fallback pagination.
    const requestedPage = Math.max(1, Number(params.page) || 1);
    const perPage = Math.max(1, Number(params.per_page) || 9);
    const total = filteredProducts.length;
    const lastPage = Math.max(1, Math.ceil(total / perPage));
    const currentPage = Math.min(requestedPage, lastPage);

    const startIndex = (currentPage - 1) * perPage;
    const items = filteredProducts.slice(startIndex, startIndex + perPage);

    return {
      items,
      isFallback: true,
      pagination: {
        current_page: currentPage,
        last_page: lastPage,
        per_page: perPage,
        total,
        from: total ? startIndex + 1 : null,
        to: total ? startIndex + items.length : null,
        has_more: currentPage < lastPage,
      },
    };
  },

  /**
   * Get dynamic filter options (categories, sellers, price range).
   */
  async getFilterMeta() {
    try {
      const response = await apiClient.get('/products/filters');

      if (response.data && response.data.success) {
        return response.data.data;
      }
    } catch (err) {
      console.warn(
        '[ProductService] Could not fetch filter metadata from API:',
        err.message
      );
    }

    const categories = [
      { id: 1, name: 'Ceramics & Pottery', slug: 'ceramics-pottery' },
      { id: 2, name: 'Woven Goods & Bags', slug: 'woven-goods-bags' },
      { id: 3, name: 'Artisan Jewelry', slug: 'artisan-jewelry' },
      { id: 4, name: 'Home Decor & Candles', slug: 'home-decor-candles' },
      { id: 5, name: 'Wooden Crafts', slug: 'wooden-crafts' },
      { id: 6, name: 'Baskets & Storage', slug: 'baskets-storage' },
    ];

    const prices = sampleProducts.map(product => Number(product.price) || 0);

    return {
      categories,
      sellers: fallbackSellers,
      price: {
        min: prices.length ? Math.min(...prices) : 0,
        max: prices.length ? Math.max(...prices) : 2000,
      },
    };
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
      console.warn(
        `[ProductService] Could not fetch product ${id} from API, using fallback.`,
        err.message
      );
    }

    const fallback = sampleProducts.find(product => Number(product.id) === numId);
    if (!fallback) return null;

    const sellerId = fallbackSellers.find(
      seller => seller.name === fallback.seller
    )?.id || 1;

    return normalizeProduct({
      ...fallback,
      seller_id: sellerId,
    });
  },

  /**
   * Retrieve active categories from Laravel API with fallback.
   */
  async getCategories() {
    try {
      const response = await apiClient.get('/categories');

      if (
        response.data &&
        response.data.success &&
        Array.isArray(response.data.data)
      ) {
        return response.data.data;
      }
    } catch (err) {
      console.warn(
        '[ProductService] Could not fetch categories from API, using default list.',
        err.message
      );
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
   * Retrieve featured products without pagination.
   */
  async getFeatured() {
    try {
      const response = await apiClient.get('/products', {
        params: { featured: 1, paginate: 0 },
      });

      if (
        response.data &&
        response.data.success &&
        Array.isArray(response.data.data) &&
        response.data.data.length > 0
      ) {
        return response.data.data.map(normalizeProduct);
      }
    } catch {
      // Use fallback below.
    }

    return getFallbackProducts().slice(0, 4);
  },

  /**
   * Retrieve related products without pagination.
   */
  async getRelated(category, currentId) {
    try {
      const response = await apiClient.get('/products', {
        params: { category, paginate: 0 },
      });

      if (
        response.data &&
        response.data.success &&
        Array.isArray(response.data.data)
      ) {
        return response.data.data
          .filter(product => Number(product.id) !== Number(currentId))
          .slice(0, 4)
          .map(normalizeProduct);
      }
    } catch {
      // Use fallback below.
    }

    const categorySlug = slugify(category);

    return getFallbackProducts()
      .filter(product =>
        Number(product.id) !== Number(currentId) &&
        (
          product.category.toLowerCase() === String(category).toLowerCase() ||
          product.categorySlug === categorySlug
        )
      )
      .slice(0, 4);
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
  },
};
