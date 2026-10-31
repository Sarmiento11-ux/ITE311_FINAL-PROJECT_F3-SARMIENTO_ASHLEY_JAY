import { reactive, computed } from 'vue';

const state = reactive({
  items: [
    // Pre-loaded sample cart items for preview
    {
      id: 1,
      title: "Earthy Terracotta Coffee Mug",
      price: 420.00,
      quantity: 1,
      stock: 8,
      image: "https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80",
      seller: "Studio Clayworks"
    },
    {
      id: 2,
      title: "Handwoven Abaca Market Tote",
      price: 780.00,
      quantity: 1,
      stock: 12,
      image: "https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=400&q=80",
      seller: "Cordillera Weaves"
    }
  ],
  toastMessage: '',
  showToast: false
});

export const cart = {
  items: state.items,
  toastMessage: computed(() => state.toastMessage),
  showToast: computed(() => state.showToast),
  
  totalCount: computed(() => {
    return state.items.reduce((total, item) => total + item.quantity, 0);
  }),
  
  subtotal: computed(() => {
    return state.items.reduce((total, item) => total + (item.price * item.quantity), 0);
  }),

  addToCart(product, quantity = 1) {
    if (quantity < 1) return { success: false, message: "Quantity must be at least 1." };
    if (quantity > product.stock) {
      return { success: false, message: `Only ${product.stock} items currently in stock.` };
    }

    const existingItem = state.items.find(item => item.id === product.id);
    
    if (existingItem) {
      const newQty = existingItem.quantity + quantity;
      if (newQty > product.stock) {
        return { 
          success: false, 
          message: `Cannot add more. You already have ${existingItem.quantity} in cart (Maximum available: ${product.stock}).` 
        };
      }
      existingItem.quantity = newQty;
    } else {
      state.items.push({
        id: product.id,
        title: product.title,
        price: product.price,
        quantity: quantity,
        stock: product.stock,
        image: product.image,
        seller: product.seller
      });
    }

    // Trigger visual toast confirmation
    state.toastMessage = `✓ Added ${quantity} × "${product.title}" to your cart!`;
    state.showToast = true;
    setTimeout(() => {
      state.showToast = false;
    }, 3200);

    return { success: true, message: state.toastMessage };
  },

  removeItem(productId) {
    const index = state.items.findIndex(item => item.id === productId);
    if (index > -1) {
      state.items.splice(index, 1);
    }
  },

  clearCart() {
    state.items.splice(0, state.items.length);
  }
};
