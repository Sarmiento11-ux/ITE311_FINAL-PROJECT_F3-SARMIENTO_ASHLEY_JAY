<template>
  <section class="catalog-grid-section">
    <div class="container">

      <!-- Product Grid -->
      <div v-if="products.length > 0" class="catalog-product-grid" aria-live="polite">
        <ProductCard 
          v-for="product in products" 
          :key="product.id" 
          :product="product" 
        />
      </div>

      <!-- Empty State Message (shown when no matches) -->
      <div v-else class="empty-state-box" id="emptyState">
        <div class="empty-icon"></div>
        <h3 class="empty-title">No handmade products found</h3>
        <p class="empty-desc">
          We couldn't find any crafts matching your search terms or selected filters. Try searching for something else or reset your filters.
        </p>
        <button @click="$emit('reset')" class="btn btn-primary" id="resetFiltersBtn">
          Reset All Filters
        </button>
      </div>

    </div>
  </section>
</template>

<script setup>
import ProductCard from './ProductCard.vue';

defineProps({
  products: {
    type: Array,
    required: true
  }
});

defineEmits(['reset']);
</script>
