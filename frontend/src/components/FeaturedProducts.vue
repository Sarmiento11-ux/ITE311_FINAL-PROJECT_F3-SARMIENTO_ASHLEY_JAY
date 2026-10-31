<template>
  <section class="products-section" id="featured">
    <div class="container">
      
      <div class="section-header-split">
        <div>
          <span class="section-eyebrow">HANDPICKED THIS WEEK</span>
          <h2 class="section-title">Featured Artisan Creations</h2>
          <p class="section-subtitle">Individually produced in limited batches by registered sellers.</p>
        </div>

        <div class="product-filters">
          <button 
            class="filter-tab" 
            :class="{ active: currentTab === 'all' }"
            @click="currentTab = 'all'"
          >
            All Pieces
          </button>
          <button 
            class="filter-tab" 
            :class="{ active: currentTab === 'top-rated' }"
            @click="currentTab = 'top-rated'"
          >
            Top Rated
          </button>
          <button 
            class="filter-tab" 
            :class="{ active: currentTab === 'under-500' }"
            @click="currentTab = 'under-500'"
          >
            Under ₱500
          </button>
        </div>
      </div>

      <div class="product-grid">
        <ProductCard 
          v-for="item in filteredProducts" 
          :key="item.id" 
          :product="item" 
        />
      </div>

      <!-- Seller Recruitment CTA Banner -->
      <div class="products-cta-banner">
        <div class="banner-text">
          <h3>Are you a local creator or craftsman?</h3>
          <p>Open your CraftMarket stall today. No setup fees, low commission, direct client reach.</p>
        </div>
        <router-link to="/register?role=seller" class="btn btn-primary" id="sellers">
          Start Selling on CraftMarket
        </router-link>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ProductCard from './ProductCard.vue';
import { productService } from '../services/productService.js';

const currentTab = ref('all');
const featuredList = ref([]);

onMounted(async () => {
  featuredList.value = await productService.getFeatured();
});

const filteredProducts = computed(() => {
  if (currentTab.value === 'top-rated') {
    return featuredList.value.filter(p => p.rating >= 4.9);
  }
  if (currentTab.value === 'under-500') {
    return featuredList.value.filter(p => p.price <= 500);
  }
  return featuredList.value;
});
</script>
