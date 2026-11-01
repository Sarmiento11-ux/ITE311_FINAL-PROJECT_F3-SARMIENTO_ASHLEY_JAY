import { reactive, computed } from 'vue';
import { cartService } from '../services/cartService.js';
import { authService } from '../services/authService.js';

const STORAGE_KEY = 'craftmarket_local_cart';

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
    // Ignore storage quota errors
  }
}

const state = reactive({
  items: getLocalItems(),
  isLoading: false,
  toastMessage: '',
  showToast: false
});

export const cart = {
  items: state.items,
  isLoading: computed(() => state.isLoading),
  toastMessage: computed(() => state.toastMessage),
  showToast: computed(() => state.showToast),

  totalCount: computed(() => {
    return state.items.reduce((total, item) => total + (Number(item.quantity) || 0), 0);
  }),

  subtotal: computed(() => {
    return state.items.reduce((total, item) => total + ((Number(item.price) || 0) * (Number(item.quantity) || 1)), 0);
  }),

  /**
   * Load cart from Laravel backend API if authenticated, or from local storage.
   */
  async loadCart() {
    if (!authService.isAuthenticated()) {
      state.items = getLocalItems();
      return;
    }

    state.isLoading = true;
    try {
      const res = await cartService.getCart();
      if (res && Array.isArray(res.items)) {
        state.items = res.items.map(item => ({
          cartItemId: item.id,
          id: item.product_id, // keep id as product_id for compatibility
          productId: item.product_id,
          title: item.product_name,
          price: Number(item.price),
          quantity: Number(item.quantity),
          stock: Number(item.stock),
          image: item.image,
          seller: item.seller_name,
          category: item.category,
          inStock: item.in_stock
        }));
        saveLocalItems(state.items);
      }
    } catch (err) {
      console.warn('[Cart] Could not fetch server cart, falling back to local:', err.message);
      state.items = getLocalItems();
    } finally {
      state.isLoading = false;
    }
  },

  /**
   * Add a product to cart.
   * If authenticated, syncs with Laravel API.
   */
  async addToCart(product, quantity = 1) {
    if (quantity < 1) return { success: false, message: 'Quantity must be at least 1.' };

    const availStock = Number(product.stock) || 0;
    if (availStock <= 0) {
      return { success: false, message: 'This craft item is currently sold out.' };
    }

    const existing = state.items.find(i => i.id === product.id || i.productId === product.id);
    const currentQty = existing ? Number(existing.quantity) : 0;
    const desiredTotal = currentQty + quantity;

    if (desiredTotal > availStock) {
      return {
        success: false,
        message: `Cannot add more. You have ${currentQty} in cart, and only ${availStock} available in inventory.`
      };
    }

    // If authenticated, sync to backend API
    if (authService.isAuthenticated()) {
      try {
        await cartService.addItem(product.id, quantity);
        await this.loadCart();
      } catch (err) {
        const errorMsg = err.response?.data?.message || 'Failed to add item to cart.';
        return { success: false, message: errorMsg };
      }
    } else {
      // Guest mode: update reactive local state
      if (existing) {
        existing.quantity = desiredTotal;
      } else {
        state.items.push({
          id: product.id,
          productId: product.id,
          title: product.title || product.name,
          price: Number(product.price),
          quantity: quantity,
          stock: availStock,
          image: product.image,
          seller: product.seller || product.seller_name || 'Artisan Maker',
          category: product.category || 'Handmade'
        });
      }
      saveLocalItems(state.items);
    }

    // Trigger toast notification
    state.toastMessage = `✓ Added ${quantity} × "${product.title || product.name}" to your cart!`;
    state.showToast = true;
    setTimeout(() => {
      state.showToast = false;
    }, 3200);

    return { success: true, message: state.toastMessage };
  },

  /**
   * Update quantity of an item in the cart.
   */
  async updateQuantity(productId, newQty) {
    const item = state.items.find(i => i.id === productId || i.productId === productId);
    if (!item) return { success: false, message: 'Item not found in cart.' };

    if (newQty < 1) {
      return this.removeItem(productId);
    }

    if (newQty > item.stock) {
      return {
        success: false,
        message: `Requested quantity exceeds available stock (${item.stock} left).`
      };
    }

    if (authService.isAuthenticated() && item.cartItemId) {
      try {
        await cartService.updateQuantity(item.cartItemId, newQty);
        item.quantity = newQty;
        saveLocalItems(state.items);
      } catch (err) {
        return {
          success: false,
          message: err.response?.data?.message || 'Failed to update quantity.'
        };
      }
    } else {
      item.quantity = newQty;
      saveLocalItems(state.items);
    }

    return { success: true };
  },

  /**
   * Remove an item from the cart.
   */
  async removeItem(productId) {
    const index = state.items.findIndex(i => i.id === productId || i.productId === productId);
    if (index === -1) return;

    const item = state.items[index];

    if (authService.isAuthenticated() && item.cartItemId) {
      try {
        await cartService.removeItem(item.cartItemId);
      } catch (err) {
        console.warn('[Cart] Server remove failed:', err.message);
      }
    }

    state.items.splice(index, 1);
    saveLocalItems(state.items);
  },

  /**
   * Clear all items in the cart.
   */
  async clearCart() {
    if (authService.isAuthenticated()) {
      try {
        await cartService.clearCart();
      } catch (err) {
        console.warn('[Cart] Server clear failed:', err.message);
      }
    }

    state.items.splice(0, state.items.length);
    saveLocalItems([]);
  }
};

// Automatically attempt to load persistent cart at boot
cart.loadCart();
