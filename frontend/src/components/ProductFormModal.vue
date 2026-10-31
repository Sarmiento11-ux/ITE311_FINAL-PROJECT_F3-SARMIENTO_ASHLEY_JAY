<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-box">
      <div class="modal-header-row">
        <h3 class="modal-title">{{ isEdit ? 'Edit Product Listing' : 'Add New Handmade Craft' }}</h3>
        <button type="button" class="btn-modal-close" @click="$emit('close')">&times;</button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">Product Name <span class="required">*</span></label>
          <input 
            type="text" 
            v-model="formData.name" 
            class="form-input" 
            placeholder="e.g. Earthy Terracotta Coffee Mug" 
            required
          >
        </div>

        <div class="form-row-2col">
          <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <select v-model="formData.category" class="form-input" required>
              <option value="Ceramics & Pottery">Ceramics & Pottery</option>
              <option value="Woven Goods & Bags">Woven Goods & Bags</option>
              <option value="Artisan Jewelry">Artisan Jewelry</option>
              <option value="Home Decor & Candles">Home Decor & Candles</option>
              <option value="Wooden Crafts">Wooden Crafts</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Price (₱) <span class="required">*</span></label>
            <input 
              type="number" 
              v-model.number="formData.price" 
              step="0.01" 
              min="1" 
              class="form-input" 
              placeholder="0.00" 
              required
            >
          </div>
        </div>

        <div class="form-row-2col">
          <div class="form-group">
            <label class="form-label">Stock Inventory <span class="required">*</span></label>
            <input 
              type="number" 
              v-model.number="formData.stock" 
              min="0" 
              class="form-input" 
              placeholder="Available units" 
              required
            >
          </div>

          <div class="form-group">
            <label class="form-label">Listing Status <span class="required">*</span></label>
            <select v-model="formData.status" class="form-input" required>
              <option value="Published">Published (Active)</option>
              <option value="Low Stock">Low Stock</option>
              <option value="Draft">Draft (Hidden)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Image URL</label>
          <input 
            type="url" 
            v-model="formData.image" 
            class="form-input" 
            placeholder="https://images.unsplash.com/..."
          >
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8DFD5; display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn-primary">{{ isEdit ? 'Save Changes' : 'Publish Product' }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  initialData: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['save', 'close']);

const formData = reactive({
  id: null,
  name: '',
  category: 'Ceramics & Pottery',
  price: 450,
  stock: 10,
  status: 'Published',
  image: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80'
});

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    Object.assign(formData, newVal);
  }
}, { immediate: true });

const handleSubmit = () => {
  emit('save', { ...formData });
};
</script>
