<template>
  <div>
    <div class="table-responsive-wrapper">
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
            </td>
            <td>
              <div class="action-btn-group">
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
  </div>
</template>

<script setup>
defineProps({
  products: {
    type: Array,
    required: true
  }
});

defineEmits(['edit', 'delete']);

const getStatusClass = (status) => {
  const s = status.toLowerCase();
  if (s.includes('published')) return 'status-published';
  if (s.includes('low')) return 'status-low-stock';
  return 'status-draft';
};
</script>
