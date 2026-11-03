<template>
  <main class="products-catalog-page">
    <!-- 1. CATALOG HERO / BREADCRUMB -->
    <section class="catalog-hero">
      <div class="container">
        <nav class="breadcrumb-trail" aria-label="Breadcrumb">
          <router-link to="/">Home</router-link>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">Product Catalog</span>
        </nav>

        <div class="catalog-header-text">
          <span class="section-eyebrow">ARTISAN SHOP</span>
          <h1 class="catalog-title">Handmade Product Catalog</h1>
          <p class="catalog-subtitle">
            Explore authentic creations crafted by independent local makers across the archipelago. Every purchase supports sustainable livelihoods and heritage craft techniques.
          </p>
        </div>
      </div>
    </section>

    <!-- 2. CONTROLS BAR: ADVANCED SEARCH, FILTERS & SORTING -->
    <section class="catalog-controls-section" id="catalog-controls">
      <div class="container">
        
        <!-- API Status Notice if error occurs -->
        <div v-if="apiNotice" class="catalog-api-notice">
          <span>{{ apiNotice }}</span>
          <button @click="fetchProducts" class="btn btn-sm btn-retry">Retry API</button>
        </div>

        <div class="controls-card">
          <!-- Reusable Debounced Search Component -->
          <ProductSearch 
            v-model="searchQuery" 
            placeholder="Search by craft piece, description, or artisan maker..." 
            @search="handleSearch"
            @clear="handleClearSearch"
          />

          <!-- Reusable Category, Price, Seller, Availability & Sorting Filter Component -->
          <CategoryFilter 
            :categories="filterMeta.categories"
            :sellers="filterMeta.sellers"
            v-model:selectedCategory="selectedCategory"
            v-model:selectedSeller="selectedSeller"
            v-model:minPrice="minPrice"
            v-model:maxPrice="maxPrice"
            v-model:availability="availability"
            v-model:sortBy="sortBy"
            :totalResults="pagination?.total || products.length"
            @reset="resetFilters"
          />
        </div>

      </div>
    </section>

    <!-- 3. PRODUCT GRID, SKELETON & PAGINATION -->
    <ProductGrid 
      :products="products"
      :pagination="pagination"
      :is-loading="isLoading"
      @reset="resetFilters"
      @page-change="handlePageChange"
    />

  </main>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductSearch from '../components/ProductSearch.vue';
import CategoryFilter from '../components/CategoryFilter.vue';
import ProductGrid from '../components/ProductGrid.vue';
import { productService } from '../services/productService.js';

const route = useRoute();
const router = useRouter();

// ── State ────────────────────────────────────────────────────────────────────
const products = ref([]);
const pagination = ref(null);
const isLoading = ref(true);
const apiNotice = ref('');

const filterMeta = reactive({
  categories: [],
  sellers: [],
  price: { min: 0, max: 2000 },
});

// Query State
const searchQuery = ref(route.query.search || '');
const selectedCategory = ref(route.query.category || 'all');
const selectedSeller = ref(route.query.seller_id || route.query.seller || 'all');
const minPrice = ref(route.query.min_price || '');
const maxPrice = ref(route.query.max_price || '');
const availability = ref(route.query.availability || 'all');
const sortBy = ref(route.query.sort || 'latest');
const currentPage = ref(Number(route.query.page) || 1);
const perPage = ref(9);

// ── Load Metadata & Initial Products ──────────────────────────────────────────
const loadFilterMetadata = async () => {
  try {
    const meta = await productService.getFilterMeta();
    if (meta) {
      filterMeta.categories = meta.categories || [];
      filterMeta.sellers = meta.sellers || [];
      if (meta.price) {
        filterMeta.price = meta.price;
      }
    }
  } catch (err) {
    console.warn('[ProductsView] Filter meta load error:', err);
  }
};

const fetchProducts = async () => {
  isLoading.value = true;
  apiNotice.value = '';

  const params = {
    page: currentPage.value,
    per_page: perPage.value,
    sort: sortBy.value,
  };

  if (searchQuery.value.trim()) {
    params.search = searchQuery.value.trim();
  }

  if (selectedCategory.value && selectedCategory.value !== 'all') {
    params.category = selectedCategory.value;
  }

  if (selectedSeller.value && selectedSeller.value !== 'all') {
    params.seller_id = selectedSeller.value;
  }

  if (minPrice.value !== '') {
    params.min_price = minPrice.value;
  }

  if (maxPrice.value !== '') {
    params.max_price = maxPrice.value;
  }

  if (availability.value && availability.value !== 'all') {
    params.availability = availability.value;
  }

  try {
    const res = await productService.getAll(params);
    products.value = res.items || [];
    pagination.value = res.pagination || null;
  } catch (err) {
    console.error('[ProductsView] Failed to fetch products:', err);
    apiNotice.value = 'Could not load products from CraftMarket API. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// ── URL Synchronization ───────────────────────────────────────────────────────
const syncUrl = () => {
  const query = {};
  if (searchQuery.value.trim()) query.search = searchQuery.value.trim();
  if (selectedCategory.value && selectedCategory.value !== 'all') query.category = selectedCategory.value;
  if (selectedSeller.value && selectedSeller.value !== 'all') query.seller_id = selectedSeller.value;
  if (minPrice.value !== '') query.min_price = minPrice.value;
  if (maxPrice.value !== '') query.max_price = maxPrice.value;
  if (availability.value && availability.value !== 'all') query.availability = availability.value;
  if (sortBy.value && sortBy.value !== 'latest') query.sort = sortBy.value;
  if (currentPage.value > 1) query.page = currentPage.value;

  router.replace({ query }).catch(() => {});
};

// ── Handlers ─────────────────────────────────────────────────────────────────
const handleSearch = () => {
  currentPage.value = 1;
  syncUrl();
  fetchProducts();
};

const handleClearSearch = () => {
  searchQuery.value = '';
  currentPage.value = 1;
  syncUrl();
  fetchProducts();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  syncUrl();
  fetchProducts();
  const el = document.getElementById('catalog-controls');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = 'all';
  selectedSeller.value = 'all';
  minPrice.value = '';
  maxPrice.value = '';
  availability.value = 'all';
  sortBy.value = 'latest';
  currentPage.value = 1;

  syncUrl();
  fetchProducts();
};

// ── Watchers for filter triggers ──────────────────────────────────────────────
watch([selectedCategory, selectedSeller, minPrice, maxPrice, availability, sortBy], () => {
  currentPage.value = 1;
  syncUrl();
  fetchProducts();
});

onMounted(async () => {
  await Promise.all([
    loadFilterMetadata(),
    fetchProducts()
  ]);
});
</script>

<style scoped>
.products-catalog-page {
  min-height: 85vh;
  background-color: var(--cm-cream, #FAF6F0);
}

.catalog-hero {
  padding: 36px 0 24px;
}

.breadcrumb-trail {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #7a6a60;
  margin-bottom: 20px;
}

.breadcrumb-trail a {
  color: #7a6a60;
  text-decoration: none;
}

.breadcrumb-trail a:hover {
  color: #c25e3e;
}

.breadcrumb-separator {
  color: #c4b8ad;
}

.breadcrumb-current {
  color: #2d2420;
  font-weight: 600;
}

.catalog-header-text {
  max-width: 700px;
}

.section-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #c25e3e;
  display: block;
  margin-bottom: 6px;
}

.catalog-title {
  font-size: 2.2rem;
  color: #2d2420;
  margin: 0 0 10px;
  line-height: 1.2;
}

.catalog-subtitle {
  color: #7a6a60;
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0;
}

.catalog-controls-section {
  padding: 16px 0;
}

.controls-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 14px;
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.catalog-api-notice {
  margin-bottom: 16px;
  padding: 12px 18px;
  border-radius: 8px;
  background: #fff8e7;
  border: 1px solid #e6d2b5;
  color: #8a6d3b;
  font-size: 0.88rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.btn-retry {
  padding: 4px 10px;
  font-size: 0.8rem;
  background: #8a6d3b;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
