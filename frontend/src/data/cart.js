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

// Reactive state with pre-initialized array
const state = reactive({
  items: getLocalItems(),
  isLoading: false,
  toastMessage: '',
  showToast: false
});

/**
 * Helper to update items in-place so all references to state.items stay reactive
 */
function replaceItems(newItems) {
  state.items.splice(0, state.items.length, ...newItems);
  saveLocalItems(state.items);
}

export const cart = {
  // Use state.items in place (never reassign state.items = ...)
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
   * If the user had guest items in localStorage, automatically syncs them to the backend!
   */
  async loadCart() {
    const localItems = getLocalItems();

    if (!authService.isAuthenticated()) {
      if (state.items.length === 0 && localItems.length > 0) {
        replaceItems(localItems);
      }
      return;
    }

    state.isLoading = true;
    try {
      // 1. If guest items exist locally that are not yet on the server, sync them
      if (localItems.length > 0) {
        for (const localItem of localItems) {
          if (!localItem.cartItemId) {
            try {
              await cartService.addItem(localItem.productId || localItem.id, localItem.quantity || 1);
            } catch (syncErr) {
              // Ignore duplicate sync issues
            }
          }
        }
      }

      // 2. Fetch the live authenticated cart from Laravel MySQL
      const res = await cartService.getCart();
      if (res && Array.isArray(res.items)) {
        const mapped = res.items.map(item => ({
          cartItemId: item.id,
          id: item.product_id,
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

        // If backend returned items, update local cart
        if (mapped.length > 0) {
          replaceItems(mapped);
        } else if (localItems.length > 0) {
          // If backend was empty but local had items, keep local items
          replaceItems(localItems);
        }
      }
    } catch (err) {
      console.warn('[Cart] Could not fetch server cart, keeping local cart:', err.message);
      if (localItems.length > 0) {
        replaceItems(localItems);
      }
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

    const existing = state.items.find(i => (i.id === product.id) || (i.productId === product.id));
    const currentQty = existing ? Number(existing.quantity) : 0;
    const desiredTotal = currentQty + quantity;

    if (desiredTotal > availStock) {
      return {
        success: false,
        message: `Cannot add more. You have ${currentQty} in cart, and only ${availStock} available in inventory.`
      };
    }

    // Update in-place locally first for instantaneous UI responsiveness
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

    // If authenticated, also sync to backend API in the background
    if (authService.isAuthenticated()) {
      try {
        const res = await cartService.addItem(product.id, quantity);
        if (res && res.data && res.data.id) {
          const addedItem = state.items.find(i => (i.id === product.id) || (i.productId === product.id));
          if (addedItem) {
            addedItem.cartItemId = res.data.id;
            saveLocalItems(state.items);
          }
        }
      } catch (err) {
        console.warn('[Cart] Server add sync failed:', err.message);
      }
    }

    // Trigger visual toast notification
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
    const item = state.items.find(i => (i.id === productId) || (i.productId === productId));
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

    item.quantity = newQty;
    saveLocalItems(state.items);

    if (authService.isAuthenticated() && item.cartItemId) {
      try {
        await cartService.updateQuantity(item.cartItemId, newQty);
      } catch (err) {
        console.warn('[Cart] Server quantity update failed:', err.message);
      }
    }

    return { success: true };
  },

  /**
   * Remove an item from the cart.
   */
  async removeItem(productId) {
    const index = state.items.findIndex(i => (i.id === productId) || (i.productId === productId));
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

// Automatically load cart on start
cart.loadCart();
