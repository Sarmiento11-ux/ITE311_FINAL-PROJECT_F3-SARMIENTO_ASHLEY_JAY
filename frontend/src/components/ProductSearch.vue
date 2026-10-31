<template>
  <div class="catalog-search-wrapper">
    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input 
      :value="modelValue" 
      @input="$emit('update:modelValue', $event.target.value)"
      type="text" 
      :placeholder="placeholder || 'Search by product name or artisan...'" 
      aria-label="Search handmade products"
    >
    <button 
      v-if="modelValue" 
      @click="$emit('update:modelValue', '')" 
      class="clear-search-btn" 
      title="Clear search" 
      aria-label="Clear search input"
    >
      &times;
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);
</script>
