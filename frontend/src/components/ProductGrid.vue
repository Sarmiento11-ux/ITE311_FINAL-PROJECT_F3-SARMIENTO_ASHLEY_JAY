<template>
  <section class="catalog-grid-section">
    <div class="container">

      <!-- Active Filters / Results Summary -->
      <div v-if="!isLoading && products.length > 0" class="catalog-summary-row">
        <span class="results-stat-text">
          Showing <strong>{{ showingFrom }}–{{ showingTo }}</strong> of <strong>{{ totalCount }}</strong> artisan crafts
        </span>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="catalog-loading-state" aria-busy="true">
        <div class="loading-spinner"></div>
        <p>Loading artisan crafts from CraftMarket database...</p>
      </div>

      <!-- Product Grid -->
      <div 
        v-else-if="products.length > 0" 
        class="catalog-product-grid" 
        aria-live="polite"
      >
        <ProductCard 
          v-for="product in products" 
          :key="product.id" 
          :product="product" 
        />
      </div>

      <!-- Empty State Message -->
      <div v-else class="empty-state-box" id="emptyState">
        <div class="empty-icon">🔍</div>
        <h3 class="empty-title">No handmade products found</h3>
        <p class="empty-desc">
          We couldn't find any crafts matching your search terms or active filters. Try adjusting your keywords, price bounds, or reset filters to see all available listings.
        </p>
        <button @click="$emit('reset')" class="btn btn-primary" id="resetFiltersBtn">
          Reset All Filters
        </button>
      </div>

      <!-- Pagination Bar -->
      <nav 
        v-if="!isLoading && pagination && pagination.last_page > 1" 
        class="catalog-pagination-nav" 
        aria-label="Products pagination"
      >
        <button
          type="button"
          class="page-nav-btn"
          :disabled="pagination.current_page <= 1"
          @click="$emit('page-change', pagination.current_page - 1)"
          aria-label="Previous page"
        >
          &larr; Prev
        </button>

        <div class="page-numbers-group">
          <button
            v-for="p in visiblePages"
            :key="p"
            type="button"
            class="page-num-btn"
            :class="{ active: p === pagination.current_page }"
            @click="$emit('page-change', p)"
          >
            {{ p }}
          </button>
        </div>

        <button
          type="button"
          class="page-nav-btn"
          :disabled="pagination.current_page >= pagination.last_page"
          @click="$emit('page-change', pagination.current_page + 1)"
          aria-label="Next page"
        >
          Next &rarr;
        </button>
      </nav>

    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  pagination: {
    type: Object,
    default: () => null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['reset', 'page-change']);

const totalCount = computed(() => {
  return props.pagination?.total ?? props.products.length;
});

const showingFrom = computed(() => {
  if (props.pagination?.from) return props.pagination.from;
  return props.products.length > 0 ? 1 : 0;
});

const showingTo = computed(() => {
  if (props.pagination?.to) return props.pagination.to;
  return props.products.length;
});

const visiblePages = computed(() => {
  if (!props.pagination || !props.pagination.last_page) return [1];
  const total = props.pagination.last_page;
  const current = props.pagination.current_page;
  const pages = [];

  const start = Math.max(1, current - 2);
  const end = Math.min(total, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});
</script>

<style scoped>
.catalog-grid-section {
  padding: 30px 0 70px;
}

.catalog-summary-row {
  margin-bottom: 20px;
  font-size: 0.88rem;
  color: #7a6a60;
}

.catalog-summary-row strong {
  color: #2d2420;
}

/* ── Loading State ── */
.catalog-loading-state {
  padding: 60px 0;
  text-align: center;
  color: #7a6a60;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #efe8de;
  border-top-color: #3e5943;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ── Grid Layout ── */
.catalog-product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 28px;
}

/* ── Empty State ── */
.empty-state-box {
  background: #ffffff;
  border: 1px dashed #d4c8be;
  border-radius: 16px;
  padding: 60px 24px;
  text-align: center;
  max-width: 540px;
  margin: 40px auto;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 12px;
}

.empty-title {
  font-size: 1.4rem;
  color: #2d2420;
  margin-bottom: 8px;
}

.empty-desc {
  color: #7a6a60;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 24px;
}

/* ── Pagination ── */
.catalog-pagination-nav {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 48px;
}

.page-nav-btn {
  padding: 8px 16px;
  border: 1px solid #ddd3c8;
  border-radius: 8px;
  background: #ffffff;
  color: #4a3f38;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-nav-btn:hover:not(:disabled) {
  border-color: #3e5943;
  color: #3e5943;
  background: #edf4ee;
}

.page-nav-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-numbers-group {
  display: flex;
  gap: 6px;
}

.page-num-btn {
  width: 38px;
  height: 38px;
  border: 1px solid #ddd3c8;
  border-radius: 8px;
  background: #ffffff;
  color: #4a3f38;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-num-btn:hover {
  border-color: #3e5943;
  color: #3e5943;
  background: #edf4ee;
}

.page-num-btn.active {
  background: #3e5943;
  color: #ffffff;
  border-color: #3e5943;
}

/* Prevent pagination buttons from stretching to 100% on mobile */
.page-nav-btn,
.page-num-btn {
  width: auto;
  flex-shrink: 0;
}

.page-num-btn {
  width: 38px;
  height: 38px;
}

/* Keyboard focus rings */
.page-nav-btn:focus-visible,
.page-num-btn:focus-visible {
  outline: 2px solid #3e5943;
  outline-offset: 2px;
}
</style>
