<template>
  <div>
    <!-- Category Filter Buttons / Tabs -->
    <div class="category-filters-scroll" role="tablist" aria-label="Filter products by category">
      <button 
        class="cat-filter-btn" 
        :class="{ active: selectedCategory === 'all' }"
        @click="$emit('update:selectedCategory', 'all')"
      >
        All Crafts
      </button>
      
      <button 
        v-for="cat in categories" 
        :key="cat"
        class="cat-filter-btn" 
        :class="{ active: selectedCategory.toLowerCase() === cat.toLowerCase() }"
        @click="$emit('update:selectedCategory', cat)"
      >
        {{ cat }}
      </button>
    </div>

    <!-- Sorting and Results Count Bar -->
    <div class="catalog-meta-bar">
      <div class="results-count-text">
        Showing <strong>{{ totalResults }}</strong> handmade products
      </div>

      <div class="sort-selector-wrapper">
        <label for="sortSelect" class="sort-label">Sort by:</label>
        <select 
          id="sortSelect" 
          :value="sortBy" 
          @change="$emit('update:sortBy', $event.target.value)"
          class="sort-select" 
          aria-label="Sort products"
        >
          <option value="featured">Featured Picks</option>
          <option value="price-low-high">Price: Low to High</option>
          <option value="price-high-low">Price: High to Low</option>
          <option value="rating-high">Highest Rated</option>
          <option value="name-a-z">Name: A to Z</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    required: true
  },
  selectedCategory: {
    type: String,
    default: 'all'
  },
  sortBy: {
    type: String,
    default: 'featured'
  },
  totalResults: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:selectedCategory', 'update:sortBy']);
</script>
