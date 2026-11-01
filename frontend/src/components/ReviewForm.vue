<template>
  <div class="review-form-card">
    <h4 class="review-form-heading">
      {{ isEditing ? 'Edit Your Review' : 'Write a Review' }}
    </h4>

    <!-- Ineligible state -->
    <div v-if="!isEligible && !isEditing" class="ineligible-notice">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>{{ ineligibleMessage }}</span>
    </div>

    <!-- Review form -->
    <form v-else @submit.prevent="handleSubmit" class="review-form">

      <!-- Star picker -->
      <div class="form-group">
        <label class="form-label">Your Rating <span class="required-mark">*</span></label>
        <div class="star-picker" role="group" aria-label="Rating">
          <button
            v-for="n in 5"
            :key="n"
            type="button"
            class="star-pick-btn"
            :class="{ 'active': n <= hoverRating || n <= form.rating }"
            @click="form.rating = n"
            @mouseenter="hoverRating = n"
            @mouseleave="hoverRating = 0"
            :aria-label="`Rate ${n} star${n > 1 ? 's' : ''}`"
          >
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
              <polygon
                points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                :fill="n <= hoverRating || n <= form.rating ? '#c97d3a' : '#e8ddd4'"
                stroke="none"
              />
            </svg>
          </button>
          <span v-if="form.rating" class="selected-rating-label">{{ ratingLabels[form.rating] }}</span>
        </div>
        <p v-if="errors.rating" class="field-error">{{ errors.rating }}</p>
      </div>

      <!-- Comment -->
      <div class="form-group">
        <label class="form-label" for="reviewComment">Your Review (optional)</label>
        <textarea
          id="reviewComment"
          v-model="form.comment"
          class="form-textarea"
          rows="4"
          maxlength="1000"
          placeholder="Share your experience with this craft..."
        ></textarea>
        <div class="char-count">{{ form.comment.length }}/1000</div>
        <p v-if="errors.comment" class="field-error">{{ errors.comment }}</p>
      </div>

      <!-- Error message -->
      <div v-if="submitError" class="submit-error">{{ submitError }}</div>

      <!-- Buttons -->
      <div class="form-btn-row">
        <button type="submit" class="btn btn-primary btn-submit-review" :disabled="submitting">
          <span v-if="submitting">Submitting...</span>
          <span v-else>{{ isEditing ? 'Save Changes' : 'Submit Review' }}</span>
        </button>
        <button v-if="isEditing" type="button" class="btn btn-outline btn-cancel-edit" @click="$emit('cancel')">
          Cancel
        </button>
      </div>

    </form>

    <!-- Success state -->
    <div v-if="submitted" class="submit-success">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      Review submitted! Thank you for your feedback.
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { submitReview, updateReview } from '../services/reviewService.js';

const props = defineProps({
  productId:    { type: Number, required: true },
  isEligible:   { type: Boolean, default: false },
  ineligibleMessage: { type: String, default: 'Purchase this product from a completed order to leave a review.' },
  existingReview: { type: Object, default: null },
});

const emit = defineEmits(['submitted', 'cancel']);

const isEditing  = computed(() => !!props.existingReview);
const hoverRating = ref(0);
const submitting  = ref(false);
const submitted   = ref(false);
const submitError = ref('');
const errors      = reactive({ rating: '', comment: '' });

const ratingLabels = {
  1: 'Poor',
  2: 'Fair',
  3: 'Good',
  4: 'Very Good',
  5: 'Excellent',
};

const form = reactive({
  rating:  props.existingReview?.rating  ?? 0,
  comment: props.existingReview?.comment ?? '',
});

// Reset form when existingReview changes (e.g. entering edit mode)
watch(() => props.existingReview, (rv) => {
  form.rating  = rv?.rating  ?? 0;
  form.comment = rv?.comment ?? '';
  submitted.value  = false;
  submitError.value = '';
});

function validate() {
  errors.rating  = '';
  errors.comment = '';
  let ok = true;
  if (!form.rating || form.rating < 1 || form.rating > 5) {
    errors.rating = 'Please select a rating between 1 and 5 stars.';
    ok = false;
  }
  if (form.comment && form.comment.length > 1000) {
    errors.comment = 'Review must not exceed 1000 characters.';
    ok = false;
  }
  return ok;
}

async function handleSubmit() {
  if (!validate()) return;
  submitting.value  = true;
  submitError.value = '';

  try {
    if (isEditing.value && props.existingReview) {
      await updateReview(props.productId, props.existingReview.id, {
        rating:  form.rating,
        comment: form.comment,
      });
    } else {
      await submitReview(props.productId, {
        rating:  form.rating,
        comment: form.comment,
      });
    }

    submitted.value = true;
    emit('submitted');
    setTimeout(() => { submitted.value = false; }, 4000);
  } catch (err) {
    submitError.value = err?.response?.data?.message ?? 'Failed to submit review. Please try again.';
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.review-form-card {
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 14px;
  padding: 24px;
}

.review-form-heading {
  font-size: 1rem;
  font-weight: 700;
  color: #2d2420;
  margin: 0 0 18px;
}

.ineligible-notice {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.86rem;
  color: #7a6a60;
  background: #fff;
  border: 1px solid #e0d5cc;
  border-radius: 10px;
  padding: 14px 16px;
}

.review-form { display: flex; flex-direction: column; gap: 18px; }

.form-group { display: flex; flex-direction: column; gap: 6px; }

.form-label {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #7a6a60;
}

.required-mark { color: #c25e3e; }

.star-picker {
  display: flex;
  align-items: center;
  gap: 4px;
}

.star-pick-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  transition: transform 0.1s;
  line-height: 1;
}

.star-pick-btn:hover { transform: scale(1.15); }

.selected-rating-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #c97d3a;
  margin-left: 8px;
}

.form-textarea {
  padding: 12px;
  border: 1px solid #ddd3c8;
  border-radius: 8px;
  font-size: 0.88rem;
  font-family: inherit;
  background: #fff;
  resize: vertical;
  color: #2d2420;
  transition: border-color 0.2s;
  min-height: 100px;
}

.form-textarea:focus {
  outline: none;
  border-color: #3e5943;
}

.char-count {
  font-size: 0.75rem;
  color: #b5a89e;
  text-align: right;
}

.field-error {
  font-size: 0.8rem;
  color: #c25e3e;
  margin: 0;
}

.submit-error {
  background: #fceee9;
  border: 1px solid #f3c9bb;
  color: #b84f33;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.85rem;
}

.form-btn-row {
  display: flex;
  gap: 12px;
  align-items: center;
}

.btn-submit-review {
  padding: 10px 24px;
  font-size: 0.88rem;
}

.btn-submit-review:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel-edit {
  padding: 10px 18px;
  font-size: 0.88rem;
  border: 1px solid #9e8f85;
  color: #6b5e57;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
}

.btn-cancel-edit:hover { background: #f5f0ea; }

.submit-success {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #2e7d32;
  background: #f0f9f0;
  border: 1px solid #b2dab4;
  border-radius: 10px;
  padding: 14px 16px;
  font-size: 0.88rem;
  font-weight: 500;
  margin-top: 12px;
}
</style>
