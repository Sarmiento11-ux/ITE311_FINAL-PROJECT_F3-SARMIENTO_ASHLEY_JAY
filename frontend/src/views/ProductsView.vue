<template>
  <main>
    <!-- 1. CATALOG HERO / BREADCRUMB -->
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

    <!-- 2. CONTROLS BAR: SEARCH, FILTERS & SORTING -->
    <section class="catalog-controls-section">
      <div class="container">
        <!-- API Status Notice if fallback was triggered -->
        <div v-if="apiNotice" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #FFF8E7; border: 1px solid #E6D2B5; color: #8A6D3B; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
          <span>{{ apiNotice }}</span>
          <button @click="loadData" class="btn btn-sm" style="padding: 4px 10px; font-size: 0.8rem; background: #8A6D3B; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Retry API</button>
        </div>

        <div class="controls-card">
          
          <!-- Reusable Search Component -->
          <ProductSearch 
            v-model="searchQuery" 
            placeholder="Search by product name or artisan..." 
          />

          <!-- Reusable Category & Sorting Component -->
          <CategoryFilter 
            :categories="categoryNames"
            v-model:selectedCategory="selectedCategory"
            v-model:sortBy="sortBy"
            :totalResults="filteredProducts.length"
          />

        </div>
      </div>
    </section>

    <!-- Loading State Indicator -->
    <div v-if="isLoading" class="container" style="padding: 60px 0; text-align: center; color: #7A6F68;">
      <p style="font-size: 1.1rem;">Loading artisan products from CraftMarket API...</p>
    </div>

    <!-- 3. REUSABLE PRODUCT GRID & EMPTY STATE -->
    <ProductGrid 
      v-else
      :products="filteredProducts" 
      @reset="resetFilters" 
    />

  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import ProductSearch from '../components/ProductSearch.vue';
import CategoryFilter from '../components/CategoryFilter.vue';
import ProductGrid from '../components/ProductGrid.vue';
import { productService } from '../services/productService.js';

const route = useRoute();

const products = ref([]);
const categoriesData = ref([]);
const isLoading = ref(true);
const apiNotice = ref('');
const searchQuery = ref('');
const selectedCategory = ref('all');
const sortBy = ref('featured');

const categoryNames = computed(() => {
  if (categoriesData.value.length > 0) {
    return categoriesData.value.map(c => c.name);
  }
  return [
    'Ceramics & Pottery',
    'Woven Goods & Bags',
    'Artisan Jewelry',
    'Home Decor & Candles',
    'Wooden Crafts',
    'Baskets & Storage'
  ];
});

const loadData = async () => {
  isLoading.value = true;
  apiNotice.value = '';
  try {
    const [fetchedProducts, fetchedCats] = await Promise.all([
      productService.getAll(),
      productService.getCategories()
    ]);
    products.value = fetchedProducts;
    categoriesData.value = fetchedCats;

    const hasLiveApi = fetchedProducts.some(p => p.isFromApi);
    if (!hasLiveApi && fetchedProducts.length > 0) {
      apiNotice.value = 'Notice: Currently displaying local preview catalogue while connecting to Laravel API.';
    }
  } catch (err) {
    console.error('Failed to load products:', err);
    apiNotice.value = 'Could not reach backend API. Showing offline catalog.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  if (route.query.search) {
    searchQuery.value = route.query.search;
  }
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
  await loadData();
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
  let list = [...products.value];

  // 1. Category Filter
  if (selectedCategory.value !== 'all') {
    list = list.filter(p => p.category.toLowerCase() === selectedCategory.value.toLowerCase());
  }

  // 2. Search Keyword (Name, Artisan, Category)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(p => 
      p.title.toLowerCase().includes(q) || 
      p.seller.toLowerCase().includes(q) ||
      p.category.toLowerCase().includes(q)
    );
  }

  // 3. Sorting Logic
  if (sortBy.value === 'price-low-high') {
    list.sort((a, b) => a.price - b.price);
  } else if (sortBy.value === 'price-high-low') {
    list.sort((a, b) => b.price - a.price);
  } else if (sortBy.value === 'rating-high') {
    list.sort((a, b) => b.rating - a.rating);
  } else if (sortBy.value === 'name-a-z') {
    list.sort((a, b) => a.title.localeCompare(b.title));
  }

  return list;
});
</script>
