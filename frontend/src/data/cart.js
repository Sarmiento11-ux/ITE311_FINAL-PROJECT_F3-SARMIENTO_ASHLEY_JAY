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

// ─── Internal reactive state ──────────────────────────────────────────────────
const state = reactive({
  items:        getLocalItems(),   // pre-seeded from localStorage immediately
  isLoading:    false,
  isLoaded:     false,             // prevents duplicate loadCart() calls
  toastMessage: '',
  showToast:    false,
});

/**
 * Replace items in-place so all template references to state.items stay reactive.
 */
function replaceItems(newItems) {
  state.items.splice(0, state.items.length, ...newItems);
  saveLocalItems(state.items);
}

// ─── Public cart API (all properties derived from state) ─────────────────────
// IMPORTANT: export a reactive() wrapper so Vue tracks ALL property access in templates.
export const cart = reactive({
  // Expose items directly as a computed so templates always re-evaluate
  get items()        { return state.items; },
  get isLoading()    { return state.isLoading; },
  get toastMessage() { return state.toastMessage; },
  get showToast()    { return state.showToast; },

  totalCount: computed(() =>
    state.items.reduce((t, i) => t + (Number(i.quantity) || 0), 0)
  ),

  subtotal: computed(() =>
    state.items.reduce((t, i) => t + ((Number(i.price) || 0) * (Number(i.quantity) || 1)), 0)
  ),

  /**
   * Load cart from Laravel backend API if authenticated, or from localStorage.
   * Guards against concurrent calls with isLoaded / isLoading flags.
   */
  async loadCart(force = false) {
    // If already loading, wait — don't start a second concurrent fetch
    if (state.isLoading) return;

    // If already loaded from the server this session, skip unless forced
    if (state.isLoaded && !force) return;

    const localItems = getLocalItems();

    // Guest user — just use localStorage
    if (!authService.isAuthenticated()) {
      if (state.items.length === 0 && localItems.length > 0) {
        replaceItems(localItems);
      }
      return;
    }

    state.isLoading = true;
    try {
      // 1. Sync any unsynced guest items to the backend
      if (localItems.length > 0) {
        for (const localItem of localItems) {
          if (!localItem.cartItemId) {
            try {
              await cartService.addItem(localItem.productId || localItem.id, localItem.quantity || 1);
            } catch {
              // Ignore duplicates or out-of-stock errors during sync
            }
          }
        }
        // Clear local storage now that we've synced — backend is the source of truth
        localStorage.removeItem(STORAGE_KEY);
      }

      // 2. Fetch the live authenticated cart from Laravel
      const res = await cartService.getCart();
      if (res && Array.isArray(res.items)) {
        const mapped = res.items.map(item => ({
          cartItemId: item.id,
          id:         item.product_id,
          productId:  item.product_id,
          title:      item.product_name,
          price:      Number(item.price),
          quantity:   Number(item.quantity),
          stock:      Number(item.stock),
          image:      item.image,
          seller:     item.seller_name,
          category:   item.category,
          inStock:    item.in_stock,
        }));
        replaceItems(mapped);
      } else if (localItems.length > 0) {
        // Fallback to local items if API returned nothing
        replaceItems(localItems);
      }

      state.isLoaded = true;
    } catch (err) {
      console.warn('[Cart] Could not fetch server cart, keeping current items:', err?.message);
      // Fallback: if local has items and current state is empty, restore local
      if (state.items.length === 0 && localItems.length > 0) {
        replaceItems(localItems);
      }
    } finally {
      state.isLoading = false;
    }
  },

  /**
   * Add a product to cart. Optimistically updates UI, then syncs to backend.
   */
  async addToCart(product, quantity = 1) {
    if (quantity < 1) return { success: false, message: 'Quantity must be at least 1.' };

    const availStock = Number(product.stock) || 0;
    if (availStock <= 0) {
      return { success: false, message: 'This craft item is currently sold out.' };
    }

    const existing    = state.items.find(i => i.id === product.id || i.productId === product.id);
    const currentQty  = existing ? Number(existing.quantity) : 0;
    const desiredTotal = currentQty + quantity;

    if (desiredTotal > availStock) {
      return {
        success: false,
        message: `Cannot add more. You have ${currentQty} in cart, and only ${availStock} available in inventory.`
      };
    }

    // Optimistic local update for instant UI response
    if (existing) {
      existing.quantity = desiredTotal;
    } else {
      state.items.push({
        id:        product.id,
        productId: product.id,
        title:     product.title || product.name,
        price:     Number(product.price),
        quantity:  quantity,
        stock:     availStock,
        image:     product.image,
        seller:    product.seller || product.seller_name || 'Artisan Maker',
        category:  product.category || 'Handmade',
      });
    }
    saveLocalItems(state.items);

    // Sync to backend if authenticated
    if (authService.isAuthenticated()) {
      try {
        const res = await cartService.addItem(product.id, quantity);
        if (res?.data?.id) {
          const added = state.items.find(i => i.id === product.id || i.productId === product.id);
          if (added) {
            added.cartItemId = res.data.id;
            saveLocalItems(state.items);
          }
        }
      } catch (err) {
        console.warn('[Cart] Server add sync failed:', err?.message);
      }
    }

    // Toast notification
    const msg = `✓ Added ${quantity} × "${product.title || product.name}" to your cart!`;
    state.toastMessage = msg;
    state.showToast    = true;
    setTimeout(() => { state.showToast = false; }, 3200);

    return { success: true, message: msg };
  },

  /**
   * Update quantity of an item.
   */
  async updateQuantity(productId, newQty) {
    const item = state.items.find(i => i.id === productId || i.productId === productId);
    if (!item) return { success: false, message: 'Item not found in cart.' };

    if (newQty < 1) return this.removeItem(productId);

    if (newQty > item.stock) {
      return { success: false, message: `Requested quantity exceeds available stock (${item.stock} left).` };
    }

    item.quantity = newQty;
    saveLocalItems(state.items);

    if (authService.isAuthenticated() && item.cartItemId) {
      try {
        await cartService.updateQuantity(item.cartItemId, newQty);
      } catch (err) {
        console.warn('[Cart] Server quantity update failed:', err?.message);
      }
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
        console.warn('[Cart] Server remove failed:', err?.message);
      }
    }

    state.items.splice(index, 1);
    saveLocalItems(state.items);
  },

  /**
   * Clear all items from the cart.
   */
  async clearCart() {
    if (authService.isAuthenticated()) {
      try {
        await cartService.clearCart();
      } catch (err) {
        console.warn('[Cart] Server clear failed:', err?.message);
      }
    }
    state.items.splice(0, state.items.length);
    saveLocalItems([]);
  },

  /**
   * Reset loaded flag so next loadCart() forces a fresh fetch.
   * Call this after login/logout.
   */
  reset() {
    state.isLoaded = false;
    state.items.splice(0, state.items.length);
    saveLocalItems([]);
  },
});

// Auto-load cart on app start (non-blocking — runs in background)
cart.loadCart();
