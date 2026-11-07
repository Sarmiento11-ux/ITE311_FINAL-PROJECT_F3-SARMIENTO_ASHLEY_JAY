<template>
  <div class="catalog-search-wrapper">
    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input 
      :value="modelValue" 
      @input="onInput"
      @keydown.enter="onEnter"
      type="text" 
      :placeholder="placeholder || 'Search by craft, description, or artisan...'" 
      aria-label="Search handmade products"
    >
    <button 
      v-if="modelValue" 
      type="button"
      @click="onClear" 
      class="clear-search-btn" 
      title="Clear search" 
      aria-label="Clear search input"
    >
      &times;
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

let debounceTimer = null;

const onInput = (e) => {
  const val = e.target.value;
  emit('update:modelValue', val);
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    emit('search', val);
  }, 350);
};

const onClear = () => {
  clearTimeout(debounceTimer);
  emit('update:modelValue', '');
  emit('clear');
};

const onEnter = () => {
  clearTimeout(debounceTimer);
  emit('search', props.modelValue);
};

</script>

<style scoped>
.catalog-search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #9e8f85;
  pointer-events: none;
}

.catalog-search-wrapper input {
  width: 100%;
  padding: 12px 40px 12px 42px;
  border: 1px solid #ddd3c8;
  border-radius: 8px;
  font-size: 0.92rem;
  background: #ffffff;
  color: #2d2420;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.catalog-search-wrapper input:focus {
  outline: none;
  border-color: #3e5943;
  box-shadow: 0 0 0 3px rgba(62, 89, 67, 0.12);
}

.clear-search-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #9e8f85;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
  transition: color 0.15s;
}

.clear-search-btn:hover {
  color: #c25e3e;
}
</style>
