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

      <div v-if="filteredProducts.length > 0" class="product-grid">
        <ProductCard 
          v-for="item in filteredProducts" 
          :key="item.id" 
          :product="item" 
        />
      </div>
      <div v-else class="empty-products-state" style="text-align: center; padding: 3rem 1rem; color: #7A6A60;">
        <p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">No artisan creations found for this filter.</p>
        <button type="button" @click="currentTab = 'all'" class="btn btn-secondary" style="margin-top: 0.5rem;">
          View All Pieces
        </button>
      </div>

      <!-- Seller Dashboard Quick Access for logged-in Sellers -->
      <div v-if="isSeller" class="products-cta-banner">
        <div class="banner-text">
          <h3>Manage your artisan workshop</h3>
          <p>Check your active orders, update inventory stock, and track customer earnings.</p>
        </div>
        <router-link to="/dashboard/seller" class="btn btn-primary" id="sellers">
          Go to Artisan Dashboard →
        </router-link>
      </div>

      <!-- Seller Recruitment CTA Banner for guests & non-sellers -->
      <div v-else class="products-cta-banner">
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
import { auth } from '../data/auth.js';

const isSeller = computed(() => {
  if (!auth.isAuthenticated || !auth.user.value) return false;
  const role = (auth.user.value.rawRole || auth.user.value.role || '').toLowerCase();
  return role === 'seller';
});

const currentTab = ref('all');
const featuredList = ref([]);

onMounted(async () => {
  featuredList.value = await productService.getFeatured();
});

const filteredProducts = computed(() => {
  if (currentTab.value === 'top-rated') {
    const withRating = featuredList.value.filter(p => Number(p.rating || 0) >= 4.0);
    if (withRating.length > 0) {
      return [...withRating].sort((a, b) => Number(b.rating || 0) - Number(a.rating || 0));
    }
    return [...featuredList.value].sort((a, b) => Number(b.rating || 0) - Number(a.rating || 0));
  }
  if (currentTab.value === 'under-500') {
    return featuredList.value.filter(p => Number(p.price) <= 500);
  }
  return featuredList.value;
});
</script>
