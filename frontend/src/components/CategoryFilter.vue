<template>
  <div class="category-filter-container">
    
    <!-- 1. Category Quick-Pill Navigation -->
    <div class="category-filters-scroll" role="tablist" aria-label="Filter products by category">
      <button 
        type="button"
        class="cat-filter-btn" 
        :class="{ active: !selectedCategory || selectedCategory === 'all' }"
        @click="selectCategory('all')"
      >
        All Crafts
      </button>
      
      <button 
        v-for="cat in categoryList" 
        :key="cat.id || cat.slug || cat"
        type="button"
        class="cat-filter-btn" 
        :class="{ active: isCategoryActive(cat) }"
        @click="selectCategory(cat)"
      >
        {{ getCategoryName(cat) }}
        <span v-if="cat.products_count !== undefined" class="cat-count-pill">{{ cat.products_count }}</span>
      </button>
    </div>

    <!-- 2. Advanced Filters Drawer / Bar -->
    <div class="advanced-filters-bar">
      
      <!-- Left Controls: Price Range, Availability, Seller -->
      <div class="filters-inputs-group">
        
        <!-- Seller / Artisan Dropdown -->
        <div class="filter-control-item" v-if="sellers && sellers.length > 0">
          <label for="sellerSelect" class="filter-label">Artisan Maker:</label>
          <select
            id="sellerSelect"
            :value="selectedSeller"
            @change="$emit('update:selectedSeller', $event.target.value)"
            class="filter-select"
          >
            <option value="all">All Artisans</option>
            <option
              v-for="s in sellers"
              :key="s.id"
              :value="s.id"
            >
              {{ s.shop_name || s.name }} ({{ s.name }})
            </option>
          </select>
        </div>

        <!-- Availability Dropdown -->
        <div class="filter-control-item">
          <label for="availSelect" class="filter-label">Availability:</label>
          <select
            id="availSelect"
            :value="availability"
            @change="$emit('update:availability', $event.target.value)"
            class="filter-select"
          >
            <option value="all">All Items</option>
            <option value="in_stock">In Stock Only</option>
            <option value="out_of_stock">Sold Out</option>
          </select>
        </div>

        <!-- Price Range -->
        <div class="filter-control-item price-range-group">
          <label class="filter-label">Price Range (₱):</label>
          <div class="price-inputs-row">
            <input
              type="number"
              :value="minPrice"
              @input="$emit('update:minPrice', $event.target.value)"
              placeholder="Min"
              min="0"
              class="filter-input-price"
            />
            <span class="price-sep">–</span>
            <input
              type="number"
              :value="maxPrice"
              @input="$emit('update:maxPrice', $event.target.value)"
              placeholder="Max"
              min="0"
              class="filter-input-price"
            />
          </div>
        </div>

        <!-- Reset Button -->
        <button
          v-if="hasActiveFilters"
          type="button"
          class="btn-clear-filters"
          @click="$emit('reset')"
          title="Clear all active filters"
        >
          ✕ Clear Filters
        </button>
      </div>

      <!-- Right Controls: Sort & Results Counter -->
      <div class="sort-selector-wrapper">
        <label for="sortSelect" class="sort-label">Sort by:</label>
        <select 
          id="sortSelect" 
          :value="sortBy" 
          @change="$emit('update:sortBy', $event.target.value)"
          class="sort-select" 
          aria-label="Sort products"
        >
          <option value="latest">Newest Products</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
          <option value="name_asc">Product Name: A–Z</option>
          <option value="rating_desc">Highest Rated</option>
        </select>
      </div>

    </div>

    <!-- 3. Active Filters Chips Row -->
    <div v-if="activeFilterChips.length > 0" class="active-chips-row">
      <span class="chips-label">Active filters:</span>
      <div
        v-for="chip in activeFilterChips"
        :key="chip.key"
        class="filter-chip"
      >
        <span>{{ chip.label }}</span>
        <button
          type="button"
          class="chip-remove-btn"
          @click="chip.clear()"
          :title="`Remove ${chip.label}`"
        >
          &times;
        </button>
      </div>
      <button
        type="button"
        class="btn-clear-all-link"
        @click="$emit('reset')"
      >
        Reset All
      </button>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  categories: {
    type: Array,
    required: true,
  },
  sellers: {
    type: Array,
    default: () => [],
  },
  selectedCategory: {
    type: String,
    default: 'all',
  },
  selectedSeller: {
    type: [String, Number],
    default: 'all',
  },
  minPrice: {
    type: [String, Number],
    default: '',
  },
  maxPrice: {
    type: [String, Number],
    default: '',
  },
  availability: {
    type: String,
    default: 'all',
  },
  sortBy: {
    type: String,
    default: 'latest',
  },
  totalResults: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits([
  'update:selectedCategory',
  'update:selectedSeller',
  'update:minPrice',
  'update:maxPrice',
  'update:availability',
  'update:sortBy',
  'reset',
]);

const categoryList = computed(() => props.categories || []);

const getCategoryName = (cat) => {
  if (typeof cat === 'object' && cat) return cat.name;
  return cat;
};

const isCategoryActive = (cat) => {
  const selected = String(props.selectedCategory || '').toLowerCase();
  if (typeof cat === 'object' && cat) {
    const slug = String(cat.slug || '').toLowerCase();
    const name = String(cat.name || '').toLowerCase();
    const id = String(cat.id || '');
    return selected === slug || selected === name || selected === id;
  }
  return selected === String(cat).toLowerCase();
};

const selectCategory = (cat) => {
  if (cat === 'all') {
    emit('update:selectedCategory', 'all');
  } else if (typeof cat === 'object' && cat) {
    emit('update:selectedCategory', cat.slug || cat.name);
  } else {
    emit('update:selectedCategory', cat);
  }
};

const hasActiveFilters = computed(() => {
  return (
    (props.selectedCategory && props.selectedCategory !== 'all') ||
    (props.selectedSeller && props.selectedSeller !== 'all') ||
    (props.availability && props.availability !== 'all') ||
    props.minPrice !== '' ||
    props.maxPrice !== ''
  );
});

const activeFilterChips = computed(() => {
  const chips = [];

  if (props.selectedCategory && props.selectedCategory !== 'all') {
    const found = props.categories.find(c => {
      if (typeof c === 'object') {
        return c.slug === props.selectedCategory || c.name === props.selectedCategory || String(c.id) === String(props.selectedCategory);
      }
      return c === props.selectedCategory;
    });
    const label = found ? (found.name || found) : props.selectedCategory;
    chips.push({
      key: 'category',
      label: `Category: ${label}`,
      clear: () => emit('update:selectedCategory', 'all'),
    });
  }

  if (props.selectedSeller && props.selectedSeller !== 'all') {
    const s = props.sellers.find(sel => String(sel.id) === String(props.selectedSeller));
    const sName = s ? (s.shop_name || s.name) : `Artisan #${props.selectedSeller}`;
    chips.push({
      key: 'seller',
      label: `Artisan: ${sName}`,
      clear: () => emit('update:selectedSeller', 'all'),
    });
  }

  if (props.availability && props.availability !== 'all') {
    chips.push({
      key: 'availability',
      label: props.availability === 'in_stock' ? 'In Stock Only' : 'Sold Out',
      clear: () => emit('update:availability', 'all'),
    });
  }

  if (props.minPrice !== '' && props.maxPrice !== '') {
    chips.push({
      key: 'price',
      label: `₱${props.minPrice} – ₱${props.maxPrice}`,
      clear: () => {
        emit('update:minPrice', '');
        emit('update:maxPrice', '');
      },
    });
  } else if (props.minPrice !== '') {
    chips.push({
      key: 'minPrice',
      label: `Min ₱${props.minPrice}`,
      clear: () => emit('update:minPrice', ''),
    });
  } else if (props.maxPrice !== '') {
    chips.push({
      key: 'maxPrice',
      label: `Max ₱${props.maxPrice}`,
      clear: () => emit('update:maxPrice', ''),
    });
  }

  return chips;
});
</script>

<style scoped>
.category-filter-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

/* 1. Category Quick-Pills Scroll */
.category-filters-scroll {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 6px;
  scrollbar-width: thin;
}

.cat-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid #ddd3c8;
  background: #ffffff;
  color: #5a4f48;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cat-filter-btn:hover {
  border-color: #3e5943;
  color: #3e5943;
  background: #edf4ee;
}

.cat-filter-btn.active {
  background: #3e5943;
  color: #ffffff;
  border-color: #3e5943;
}

.cat-count-pill {
  font-size: 0.72rem;
  background: rgba(0, 0, 0, 0.08);
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 700;
}

.cat-filter-btn.active .cat-count-pill {
  background: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

/* 2. Advanced Filters Bar */
.advanced-filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  padding: 14px 18px;
  background: #fdfbf7;
  border: 1px solid #efe8de;
  border-radius: 12px;
}

.filters-inputs-group {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
}

.filter-control-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #7a6a60;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0;
}

.filter-select {
  padding: 6px 12px;
  border: 1px solid #ddd3c8;
  border-radius: 6px;
  font-size: 0.85rem;
  background: #ffffff;
  color: #2d2420;
  font-family: inherit;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #3e5943;
}

.price-inputs-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-input-price {
  width: 72px;
  padding: 6px 8px;
  border: 1px solid #ddd3c8;
  border-radius: 6px;
  font-size: 0.85rem;
  background: #ffffff;
  color: #2d2420;
  font-family: inherit;
}

.filter-input-price:focus {
  outline: none;
  border-color: #3e5943;
}

.price-sep {
  color: #9e8f85;
}

.btn-clear-filters {
  background: none;
  border: none;
  color: #c25e3e;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  transition: opacity 0.2s;
}

.btn-clear-filters:hover {
  opacity: 0.75;
  text-decoration: underline;
}

.sort-selector-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #7a6a60;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0;
}

.sort-select {
  padding: 6px 12px;
  border: 1px solid #ddd3c8;
  border-radius: 6px;
  font-size: 0.85rem;
  background: #ffffff;
  color: #2d2420;
  font-family: inherit;
  cursor: pointer;
}

.sort-select:focus {
  outline: none;
  border-color: #3e5943;
}

/* 3. Active Chips Row */
.active-chips-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 0.82rem;
}

.chips-label {
  color: #7a6a60;
  font-weight: 600;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 14px;
  background: #f0e8df;
  color: #4a3f38;
  font-size: 0.8rem;
  font-weight: 500;
}

.chip-remove-btn {
  background: none;
  border: none;
  color: #7a6a60;
  font-size: 1rem;
  line-height: 1;
  cursor: pointer;
  padding: 0;
}

.chip-remove-btn:hover {
  color: #c25e3e;
}

.btn-clear-all-link {
  background: none;
  border: none;
  color: #c25e3e;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  padding: 2px 6px;
}
</style>
