<template>
  <main>
    <!-- CATALOG HERO -->
    <section class="catalog-hero">
      <div class="container">
        <div class="breadcrumb-trail">
          <router-link to="/">Home</router-link>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">Product Catalog</span>
        </div>

        <div class="catalog-header-text">
          <span class="section-eyebrow">ARTISAN SHOP</span>
          <h1 class="catalog-title">Handmade Product Catalog</h1>
          <p class="catalog-subtitle">
            Explore authentic creations crafted by independent local makers across the archipelago. Every purchase supports sustainable livelihoods and heritage craft techniques.
          </p>
        </div>
      </div>
    </section>

    <!-- CONTROLS BAR: SEARCH, FILTERS & SORTING -->
    <section class="catalog-controls-section">
      <div class="container">
        <div class="controls-card">
          
          <!-- Search Input -->
          <div class="catalog-search-wrapper">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by product name or artisan..." 
              aria-label="Search handmade products"
            >
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-search-btn" title="Clear search">&times;</button>
          </div>

          <!-- Category Filter Buttons -->
          <div class="category-filters-scroll" role="tablist" aria-label="Filter products by category">
            <button 
              class="cat-filter-btn" 
              :class="{ active: selectedCategory === 'all' }"
              @click="selectedCategory = 'all'"
            >
              All Crafts
            </button>
            <button 
              v-for="cat in categories" 
              :key="cat"
              class="cat-filter-btn" 
              :class="{ active: selectedCategory === cat }"
              @click="selectedCategory = cat"
            >
              {{ cat }}
            </button>
          </div>

          <!-- Sorting and Results Count Bar -->
          <div class="catalog-meta-bar">
            <div class="results-count-text">
              Showing <strong>{{ filteredProducts.length }}</strong> handmade products
            </div>

            <div class="sort-selector-wrapper">
              <label for="sortSelect" class="sort-label">Sort by:</label>
              <select id="sortSelect" v-model="sortBy" class="sort-select" aria-label="Sort products">
                <option value="featured">Featured Picks</option>
                <option value="price-low-high">Price: Low to High</option>
                <option value="price-high-low">Price: High to Low</option>
                <option value="rating-high">Highest Rated</option>
                <option value="name-a-z">Name: A to Z</option>
              </select>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- PRODUCT GRID & EMPTY STATE -->
    <section class="catalog-grid-section">
      <div class="container">

        <div v-if="filteredProducts.length > 0" class="catalog-product-grid">
          <ProductCard 
            v-for="item in filteredProducts" 
            :key="item.id" 
            :product="item" 
          />
        </div>

        <!-- Empty State Message -->
        <div v-else class="empty-state-box">
          <div class="empty-icon">🍂</div>
          <h3 class="empty-title">No handmade products found</h3>
          <p class="empty-desc">
            We couldn't find any crafts matching your search terms or selected filters. Try searching for something else or reset your filters.
          </p>
          <button @click="resetFilters" class="btn btn-primary">
            Reset All Filters
          </button>
        </div>

      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import { productService } from '../services/productService.js';

const route = useRoute();
const products = ref([]);
const searchQuery = ref('');
const selectedCategory = ref('all');
const sortBy = ref('featured');

const categories = [
  'Ceramics & Pottery',
  'Woven Goods & Bags',
  'Artisan Jewelry',
  'Home Decor & Candles',
  'Wooden Crafts'
];

onMounted(async () => {
  products.value = await productService.getAll();
  
  if (route.query.search) {
    searchQuery.value = route.query.search;
  }
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
});

watch(() => route.query, (newQuery) => {
  if (newQuery.search !== undefined) searchQuery.value = newQuery.search;
  if (newQuery.category !== undefined) selectedCategory.value = newQuery.category;
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = 'all';
  sortBy.value = 'featured';
};

const filteredProducts = computed(() => {
  let result = [...products.value];

  // Filter Category
  if (selectedCategory.value !== 'all') {
    result = result.filter(p => p.category.toLowerCase() === selectedCategory.value.toLowerCase());
  }

  // Search Query
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    result = result.filter(p => 
      p.title.toLowerCase().includes(q) || 
      p.seller.toLowerCase().includes(q) ||
      p.category.toLowerCase().includes(q)
    );
  }

  // Sort
  if (sortBy.value === 'price-low-high') {
    result.sort((a, b) => a.price - b.price);
  } else if (sortBy.value === 'price-high-low') {
    result.sort((a, b) => b.price - a.price);
  } else if (sortBy.value === 'rating-high') {
    result.sort((a, b) => b.rating - a.rating);
  } else if (sortBy.value === 'name-a-z') {
    result.sort((a, b) => a.title.localeCompare(b.title));
  }

  return result;
});
</script>
