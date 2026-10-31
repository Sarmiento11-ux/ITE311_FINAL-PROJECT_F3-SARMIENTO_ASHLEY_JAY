<template>
  <article class="product-card">
    <div class="product-media">
      <img 
        :src="product.image" 
        :alt="product.title"
        loading="lazy"
      >
      <span v-if="product.badge" class="product-tag" :class="tagClass">{{ product.badge }}</span>
      <button class="btn-wishlist" title="Save to favorites" aria-label="Add to wishlist">♥</button>
    </div>
    
    <div class="product-details">
      <div class="product-artisan-row">
        <span class="artisan-byline">By {{ product.seller }}</span>
        <span class="product-rating">★ {{ product.rating }} ({{ product.reviewsCount || 12 }})</span>
      </div>
      
      <h3 class="product-name">
        <router-link :to="`/products/${product.id}`">{{ product.title }}</router-link>
      </h3>
      
      <p class="product-category">{{ product.category }}</p>
      
      <div class="product-footer">
        <div class="price-wrapper">
          <span class="currency">₱</span>
          <span class="amount">{{ formatPrice(product.price) }}</span>
        </div>
        
        <router-link :to="`/products/${product.id}`" class="btn-add-cart" aria-label="View Product Details">
          <span>View</span>
          <span class="plus-sign">↗</span>
        </router-link>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const tagClass = computed(() => {
  if (props.product.badge === 'Featured') return 'tag-featured';
  if (props.product.badge === 'Best Seller') return 'tag-bestseller';
  return 'tag-handmade';
});

const formatPrice = (price) => {
  return Number(price).toFixed(2);
};
</script>
