<template>
  <article class="product-card">
    <div class="product-media">
      <router-link :to="`/products/${product.id}`" class="product-media-link">
        <img 
          :src="product.image || defaultPlaceholder" 
          @error="handleImgError" 
          :alt="product.title"
          loading="lazy"
        >
      </router-link>
      <span v-if="product.badge" class="product-tag" :class="tagClass">{{ product.badge }}</span>
      <WishlistButton v-if="isCustomer" :product="product" variant="icon" size="sm" class="btn-wishlist" />
    </div>
    
    <div class="product-details">
      <div class="product-artisan-row">
        <span class="artisan-byline">By {{ product.seller }}</span>
        <span class="product-rating">★ {{ Number(product.rating || 0).toFixed(1) }} ({{ product.reviewsCount || 0 }})</span>
      </div>
      
      <h3 class="product-name">
        <router-link :to="`/products/${product.id}`">{{ product.title }}</router-link>
      </h3>
      
      <div class="product-meta-row">
        <span class="product-category">{{ product.category }}</span>
        <span class="stock-status-pill" :class="{ 'low-stock': product.stock <= 5 }">
          ● {{ product.stock > 0 ? (product.stock <= 5 ? `Only ${product.stock} left` : 'In Stock') : 'Sold Out' }}
        </span>
      </div>
      
      <div class="product-footer">
        <div class="price-wrapper">
          <span class="currency">₱</span>
          <span class="amount">{{ formatPrice(product.price) }}</span>
        </div>
        
        <div class="card-actions-group">
          <router-link :to="`/products/${product.id}`" class="btn-view-link" title="View details">
            View
          </router-link>
          <button 
            type="button" 
            @click="handleAddToCart" 
            class="btn-add-cart" 
            :disabled="product.stock <= 0"
            :title="`Add ${product.title} to cart`"
          >
            <span>+ Cart</span>
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { cart } from '../data/cart.js';
import { authService } from '../services/authService.js';
import WishlistButton from './WishlistButton.vue';
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const isCustomer = computed(() => {
  return authService.isAuthenticated() && (authService.getRole() === 'customer' || authService.isCustomer());
});

const tagClass = computed(() => {
  if (props.product.badge === 'Featured') return 'tag-featured';
  if (props.product.badge === 'Best Seller') return 'tag-bestseller';
  if (props.product.badge === 'New Arrival') return 'tag-new';
  return 'tag-handmade';
});

const formatPrice = (price) => {
  return Number(price).toFixed(2);
};

const handleImgError = (e) => {
  e.target.src = defaultPlaceholder;
};

const router = useRouter();

const handleAddToCart = () => {
  if (!authService.isAuthenticated()) {
    alert('Please log in or create an account to add items to your shopping cart.');
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } });
    return;
  }
  cart.addToCart(props.product, 1);
};
</script>

<style scoped>
.product-media-link {
  display: block;
  width: 100%;
  height: 100%;
}

.product-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.stock-status-pill {
  font-size: 0.75rem;
  color: #2e7d32;
  font-weight: 600;
}

.stock-status-pill.low-stock {
  color: #c25e3e;
}

.card-actions-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-view-link {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--cm-charcoal, #2C221E);
  background-color: var(--cm-cream-dark, #F2ECE1);
  padding: 6px 10px;
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-view-link:hover {
  background-color: var(--cm-terracotta, #C25E3E);
  color: #ffffff;
}

.btn-add-cart {
  background-color: var(--cm-forest, #3E5943);
  color: #ffffff;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-add-cart:hover:not(:disabled) {
  background-color: #2F4533;
}

.btn-add-cart:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Keyboard focus rings */
.btn-view-link:focus-visible,
.btn-add-cart:focus-visible {
  outline: 2px solid var(--cm-forest, #3E5943);
  outline-offset: 2px;
}

/* Prevent stretching on mobile */
.btn-view-link,
.btn-add-cart {
  width: auto;
  flex-shrink: 0;
}
.pro-artisan-badge {
  background: #FFF3D6;
  color: #8A5300;
  border: 1px solid #FCD34D;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 4px;
  line-height: 1.2;
}
</style>
