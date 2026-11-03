import { reactive, computed } from 'vue';
import { wishlistService } from '../services/wishlistService.js';
import { authService } from '../services/authService.js';
import { cart } from './cart.js';

const STORAGE_KEY = 'craftmarket_local_wishlist';

function getLocalItems() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function saveLocalItems(items) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    // Ignore quota errors
  }
}

const state = reactive({
  items:        getLocalItems(),
  ids:          new Set(getLocalItems().map(i => i.id || i.product_id)),
  isLoading:    false,
  isLoaded:     false,
  toastMessage: '',
  showToast:    false,
});

function syncIds() {
  state.ids = new Set(state.items.map(i => Number(i.id || i.product_id)));
  saveLocalItems(state.items);
}

export const wishlist = reactive({
  get items()        { return state.items; },
  get isLoading()    { return state.isLoading; },
  get toastMessage() { return state.toastMessage; },
  get showToast()    { return state.showToast; },

  totalCount: computed(() => state.items.length),

  /**
   * Check if a product is in the wishlist.
   */
  isWishlisted(productId) {
    if (!productId) return false;
    return state.ids.has(Number(productId));
  },

  /**
   * Fetch wishlist from Laravel backend if authenticated.
   */
  async loadWishlist(force = false) {
    if (state.isLoading) return;
    if (state.isLoaded && !force) return;

    if (!authService.isAuthenticated()) {
      state.items = getLocalItems();
      syncIds();
      return;
    }

    state.isLoading = true;
    try {
      const res = await wishlistService.getWishlist();
      if (res && res.success && Array.isArray(res.data)) {
        state.items = res.data.map(item => ({
          wishlistId:   item.id,
          id:           item.product_id,
          productId:    item.product_id,
          title:        item.name || item.title,
          name:         item.name || item.title,
          price:        Number(item.price),
          image:        item.image,
          stock:        Number(item.stock),
          isAvailable:  item.is_available,
          seller:       item.seller_name || item.seller,
          category:     item.category_name || item.category,
          rating:       Number(item.rating) || 5,
        }));
        syncIds();
      }
      state.isLoaded = true;
    } catch (err) {
      console.warn('[Wishlist] Could not fetch server wishlist:', err?.message);
    } finally {
      state.isLoading = false;
    }
  },

  /**
   * Toggle product in/out of wishlist.
   */
  async toggle(product) {
    const id = Number(product.id || product.productId);
    if (!id) return { success: false };

    if (this.isWishlisted(id)) {
      return await this.remove(id);
    } else {
      return await this.add(product);
    }
  },

  /**
   * Add a product to the customer's wishlist.
   */
  async add(product) {
    const id = Number(product.id || product.productId);
    if (!id) return { success: false, message: 'Invalid product.' };

    if (!authService.isAuthenticated()) {
      return {
        success: false,
        requiresAuth: true,
        message: 'Please sign in to save items to your favorites.',
      };
    }

    const title = product.title || product.name || 'Craft item';

    // Optimistically update
    if (!state.ids.has(id)) {
      state.items.unshift({
        id,
        productId:   id,
        title,
        name:        title,
        price:       Number(product.price),
        image:       product.image,
        stock:       Number(product.stock) || 0,
        isAvailable: product.stock > 0,
        seller:      product.seller || product.seller_name || 'Artisan Maker',
        category:    product.category || product.category_name || 'Handmade',
        rating:      Number(product.rating) || 5,
      });
      syncIds();
    }

    try {
      const res = await wishlistService.addToWishlist(id);
      const msg = ` "${title}" saved to your favorites!`;
      this.triggerToast(msg);
      return { success: true, message: msg, in_wishlist: true };
    } catch (err) {
      console.warn('[Wishlist] Server add failed:', err?.message);
      return { success: false, message: 'Failed to save product to wishlist.' };
    }
  },

  /**
   * Remove a product from the wishlist.
   */
  async remove(productId) {
    const id = Number(productId);
    if (!id) return { success: false };

    const idx = state.items.findIndex(i => Number(i.id || i.productId) === id);
    const item = idx !== -1 ? state.items[idx] : null;

    if (idx !== -1) {
      state.items.splice(idx, 1);
      syncIds();
    }

    if (authService.isAuthenticated()) {
      try {
        await wishlistService.removeFromWishlist(id);
      } catch (err) {
        console.warn('[Wishlist] Server remove failed:', err?.message);
      }
    }

    const title = item?.title || item?.name || 'Item';
    const msg = ` Removed "${title}" from favorites.`;
    this.triggerToast(msg);

    return { success: true, message: msg, in_wishlist: false };
  },

  /**
   * Add a wishlisted item to shopping cart.
   */
  async moveToCart(product) {
    const res = await cart.addToCart(product, 1);
    return res;
  },

  triggerToast(message) {
    state.toastMessage = message;
    state.showToast    = true;
    setTimeout(() => { state.showToast = false; }, 3000);
  },

  reset() {
    state.isLoaded = false;
    state.items = [];
    state.ids = new Set();
    saveLocalItems([]);
  }
});

// Auto-load wishlist on startup
wishlist.loadWishlist();
