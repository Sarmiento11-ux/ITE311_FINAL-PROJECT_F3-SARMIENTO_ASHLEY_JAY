<template>
  <div class="review-list">
    <div v-if="reviews.length === 0" class="no-reviews-state">
      <p>No reviews yet. Be the first to share your experience with this craft!</p>
    </div>

    <div v-else>
      <div
        v-for="review in reviews"
        :key="review.id"
        class="review-item"
      >
        <div class="review-header">
          <div class="reviewer-info">
            <div class="reviewer-avatar">{{ initials(review.user_name) }}</div>
            <div>
              <div class="reviewer-name">{{ review.user_name }}</div>
              <div class="review-date">{{ review.created_at }}</div>
            </div>
          </div>
          <ProductRating :rating="review.rating" :count="0" :show-count="false" size="sm" />
        </div>

        <p v-if="review.comment" class="review-comment">{{ review.comment }}</p>
        <p v-else class="review-no-comment">No written comment.</p>

        <!-- Own review actions -->
        <div v-if="review.user_id === currentUserId || isAdmin" class="review-actions">
          <button class="btn-review-action btn-edit" @click="$emit('edit', review)">
            Edit
          </button>
          <button class="btn-review-action btn-delete" @click="handleDelete(review)">
            Remove
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import ProductRating from './ProductRating.vue';

const props = defineProps({
  reviews:       { type: Array,  default: () => [] },
  currentUserId: { type: Number, default: null },
  isAdmin:       { type: Boolean, default: false },
  productId:     { type: Number, required: true },
});

const emit = defineEmits(['edit', 'delete']);

function initials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
}

function handleDelete(review) {
  if (confirm('Remove this review? This action cannot be undone.')) {
    emit('delete', review);
  }
}
</script>

<style scoped>
.review-list { display: flex; flex-direction: column; gap: 18px; }

.no-reviews-state {
  padding: 32px 20px;
  text-align: center;
  color: #9e8f85;
  background: #faf6f0;
  border-radius: 12px;
  border: 1px dashed #ddd3c8;
  font-size: 0.92rem;
}

.review-item {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 12px;
  padding: 18px 20px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
  flex-wrap: wrap;
  gap: 8px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.reviewer-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f0e8df;
  color: #7a5c42;
  font-weight: 700;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.reviewer-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: #2d2420;
}

.review-date {
  font-size: 0.78rem;
  color: #9e8f85;
}

.review-comment {
  font-size: 0.88rem;
  color: #4a3f38;
  line-height: 1.6;
  margin: 0;
}

.review-no-comment {
  font-size: 0.82rem;
  color: #b5a89e;
  font-style: italic;
  margin: 0;
}

.review-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

.btn-review-action {
  font-size: 0.78rem;
  padding: 4px 12px;
  border-radius: 6px;
  border: 1px solid;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.15s;
}

.btn-edit {
  border-color: #3e5943;
  color: #3e5943;
  background: transparent;
}

.btn-edit:hover { background: #edf4ee; }

.btn-delete {
  border-color: #c25e3e;
  color: #c25e3e;
  background: transparent;
}

.btn-delete:hover { background: #fceee9; }
</style>
