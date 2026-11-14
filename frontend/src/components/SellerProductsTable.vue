<template>
  <div>
    <div v-if="products.length > 0" class="table-responsive-wrapper">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock Available</th>
            <th>Listing Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div class="table-product-cell">
                <img :src="product.image || defaultPlaceholder" :alt="product.name" class="table-product-thumb" @error="handleImgError">
                <div>
                  <strong>{{ product.name }}</strong>
                  <div style="font-size: 0.75rem; color: #7A6F68;">{{ product.salesCount || 0 }} units sold</div>
                </div>
              </div>
            </td>
            <td>{{ product.category }}</td>
            <td><strong>₱{{ Number(product.price).toFixed(2) }}</strong></td>
            <td>
              <span :style="{ color: product.stock <= 3 ? '#C25E3E' : '#2F4533', fontWeight: '600' }">
                {{ product.stock }} units
              </span>
            </td>
            <td>
              <span class="status-pill" :class="getStatusClass(product.status)">
                {{ product.status }}
              </span>
              <span v-if="product.is_featured" class="featured-badge-pill" title="Featured Spotlight Craft">
                ⭐ Featured
              </span>
            </td>
            <td>
              <div class="action-btn-group">
                <!-- Pro Featured Toggle Button -->
                <button 
                  v-if="isPro"
                  type="button" 
                  class="btn-table-action"
                  :class="product.is_featured ? 'btn-action-featured-active' : 'btn-action-featured-inactive'"
                  @click="$emit('toggle-featured', product)"
                  :title="product.is_featured ? 'Click to unfeature from homepage' : 'Promote to homepage spotlight (Pro feature)'"
                >
                  {{ product.is_featured ? '⭐ Featured' : '☆ Feature' }}
                </button>
                <button 
                  type="button" 
                  class="btn-table-action btn-action-edit"
                  @click="$emit('edit', product)"
                  title="Edit product listing"
                >
                   Edit
                </button>
                <button 
                  type="button" 
                  class="btn-table-action btn-action-delete"
                  @click="$emit('delete', product.id)"
                  title="Remove product"
                >
                   Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty Products State -->
    <div v-else class="empty-products-state" style="text-align: center; padding: 3rem 1.5rem; background: #FAF6F0; border-radius: 8px; border: 1px dashed #D9CFC4; margin-top: 8px;">
      <div style="font-size: 2.2rem; margin-bottom: 12px;">🎨</div>
      <h4 style="font-size: 1.15rem; color: #2C221E; margin-bottom: 6px; font-weight: 700;">No craft listings in your workshop yet</h4>
      <p style="color: #7A6F68; font-size: 0.92rem; max-width: 440px; margin: 0 auto 18px; line-height: 1.5;">
        Showcase your handcrafted creations, pottery, textiles, or woodwork to conscious buyers nationwide.
      </p>
      <button type="button" @click="$emit('add-product')" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
        + Add Your First Craft Item
      </button>
    </div>
  </div>
</template>

<script setup>
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';

defineProps({
  products: {
    type: Array,
    required: true
  },
  isPro: {
    type: Boolean,
    default: false
  }
});

defineEmits(['edit', 'delete', 'add-product', 'toggle-featured']);

const handleImgError = (e) => {
  e.target.src = defaultPlaceholder;
};

const getStatusClass = (status) => {
  if (!status) return 'status-draft';
  const s = String(status).toLowerCase();
  if (s.includes('published') || s.includes('active')) return 'status-published';
  if (s.includes('low') || s.includes('out')) return 'status-low-stock';
  return 'status-draft';
};
</script>

<style scoped>
.featured-badge-pill {
  display: inline-block;
  margin-left: 6px;
  padding: 2px 7px;
  background: #FFF8E1;
  color: #F57F17;
  border: 1px solid #FFE082;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 700;
  vertical-align: middle;
}

.btn-action-featured-active {
  background: #FFF8E1 !important;
  color: #F57F17 !important;
  border: 1px solid #FFE082 !important;
  font-weight: 700 !important;
}

.btn-action-featured-inactive {
  background: #FAF8F5 !important;
  color: #7A6F68 !important;
  border: 1px solid #D9CFC4 !important;
}

.btn-action-featured-inactive:hover {
  background: #FFF8E1 !important;
  color: #F57F17 !important;
  border-color: #FFE082 !important;
}
</style>
