<template>
  <div class="inquiry-modal-backdrop" @click.self="$emit('close')">
    <div class="inquiry-modal-card" role="dialog" aria-modal="true" aria-labelledby="inquiry-title">
      <!-- Modal Header -->
      <div class="inquiry-header">
        <div class="inquiry-header-left">
          <span class="inquiry-tag">🎨 Bespoke Craft Request</span>
          <h3 id="inquiry-title" class="inquiry-title">Inquire for Custom Craft</h3>
          <p class="inquiry-subtitle">
            Request made-to-order sizing, tailored materials, or custom engravings directly from 
            <strong>{{ product?.seller || 'the Artisan Maker' }}</strong>.
          </p>
        </div>
        <button type="button" class="btn-close-inquiry" @click="$emit('close')" aria-label="Close dialog">×</button>
      </div>

      <!-- Item Preview Bar -->
      <div class="inquiry-item-preview" v-if="product">
        <img :src="product.image" :alt="product.name" class="inquiry-thumb" />
        <div class="inquiry-thumb-meta">
          <strong>{{ product.name }}</strong>
          <span class="inquiry-category">{{ product.category }} • Base: ₱{{ Number(product.price).toFixed(2) }}</span>
        </div>
      </div>

      <!-- Form Body -->
      <form @submit.prevent="handleSubmit" class="inquiry-form">
        <!-- Success State Alert -->
        <div v-if="successMessage" class="inquiry-alert alert-success">
          <span class="alert-icon">✓</span>
          <div>
            <strong>Custom inquiry submitted!</strong>
            <p>{{ successMessage }}</p>
          </div>
        </div>

        <!-- Error State Alert -->
        <div v-if="errorMessage" class="inquiry-alert alert-error">
          <span class="alert-icon">⚠️</span>
          <div>
            <strong>Unable to submit request:</strong>
            <p>{{ errorMessage }}</p>
          </div>
        </div>

        <template v-if="!successMessage">
          <!-- Row 1: Patron Contact -->
          <div class="form-row form-row-2">
            <div class="form-group">
              <label for="customerName" class="form-label">Your Name <span class="req">*</span></label>
              <input 
                id="customerName" 
                v-model.trim="form.customer_name" 
                type="text" 
                required 
                placeholder="Juan Dela Cruz"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label for="customerEmail" class="form-label">Email Address <span class="req">*</span></label>
              <input 
                id="customerEmail" 
                v-model.trim="form.customer_email" 
                type="email" 
                required 
                placeholder="juan@example.ph"
                class="form-control"
              />
            </div>
          </div>

          <!-- Row 2: Phone & Contact Preference -->
          <div class="form-row form-row-2">
            <div class="form-group">
              <label for="customerPhone" class="form-label">Phone / Mobile (Optional)</label>
              <input 
                id="customerPhone" 
                v-model.trim="form.customer_phone" 
                type="tel" 
                placeholder="0917-XXX-XXXX"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label for="contactPref" class="form-label">Preferred Contact Channel</label>
              <select id="contactPref" v-model="form.contact_preference" class="form-control">
                <option value="email">Email</option>
                <option value="phone">Phone Call</option>
                <option value="sms">SMS Text</option>
                <option value="viber">Viber</option>
              </select>
            </div>
          </div>

          <!-- Row 3: Desired Quantity & Dimensions -->
          <div class="form-row form-row-2">
            <div class="form-group">
              <label for="desiredQty" class="form-label">Desired Quantity <span class="req">*</span></label>
              <input 
                id="desiredQty" 
                v-model.number="form.desired_quantity" 
                type="number" 
                min="1" 
                max="1000"
                required 
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label for="preferredSpecs" class="form-label">Preferred Sizing / Dimensions</label>
              <input 
                id="preferredSpecs" 
                v-model.trim="form.preferred_specs" 
                type="text" 
                placeholder="e.g. 12 x 18 inches, Natural Rattan finish"
                class="form-control"
              />
            </div>
          </div>

          <!-- Row 4: Customization Details -->
          <div class="form-group">
            <label for="customDesc" class="form-label">Customization Specifications & Instructions <span class="req">*</span></label>
            <textarea 
              id="customDesc" 
              v-model.trim="form.custom_description" 
              rows="4" 
              required 
              minlength="10"
              maxlength="2000"
              placeholder="Describe your design vision, specific patterns, color palettes, engraving texts, or timeline deadlines needed for your bespoke order..."
              class="form-control"
            ></textarea>
            <span class="field-hint">Minimum 10 characters. Provide as much detail as possible to help the artisan quote accurately.</span>
          </div>

          <!-- Action Footer -->
          <div class="inquiry-actions">
            <button type="button" class="btn btn-secondary" @click="$emit('close')" :disabled="isSubmitting">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              <span v-if="isSubmitting">Sending Request...</span>
              <span v-else>Send Custom Inquiry</span>
            </button>
          </div>
        </template>

        <template v-else>
          <div class="inquiry-actions" style="justify-content: flex-end;">
            <button type="button" class="btn btn-primary" @click="$emit('close')">
              Done
            </button>
          </div>
        </template>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { authService } from '../services/authService.js';
import { productService } from '../services/productService.js';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'inquiry-sent']);

const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = reactive({
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  desired_quantity: 1,
  preferred_specs: '',
  custom_description: '',
  contact_preference: 'email',
});

onMounted(() => {
  const currentUser = authService.getUser();
  if (currentUser) {
    if (currentUser.name) form.customer_name = currentUser.name;
    if (currentUser.email) form.customer_email = currentUser.email;
    if (currentUser.phone) form.customer_phone = currentUser.phone;
  }
});

const handleSubmit = async () => {
  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    const res = await productService.submitInquiry(props.product.id, {
      customer_name: form.customer_name,
      customer_email: form.customer_email,
      customer_phone: form.customer_phone || null,
      desired_quantity: form.desired_quantity,
      preferred_specs: form.preferred_specs || null,
      custom_description: form.custom_description,
      contact_preference: form.contact_preference,
    });

    if (res && res.success) {
      successMessage.value = res.message || 'Custom inquiry sent successfully to the artisan!';
      emit('inquiry-sent', res.data);
    } else {
      errorMessage.value = res?.message || 'Failed to submit inquiry. Please review your input.';
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || err.message || 'Failed to submit inquiry. Please check your network connection.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.inquiry-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(44, 34, 30, 0.65);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
  padding: 16px;
}

.inquiry-modal-card {
  background: #FFFFFF;
  border-radius: 12px;
  width: 100%;
  max-width: 620px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.2);
  border: 1px solid #E8DFD5;
  display: flex;
  flex-direction: column;
}

.inquiry-header {
  padding: 24px 24px 16px;
  border-bottom: 1px solid #E8DFD5;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  position: relative;
}

.inquiry-tag {
  display: inline-block;
  padding: 3px 8px;
  background: #FAF8F5;
  border: 1px solid #E8DFD5;
  color: #C25E3E;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  border-radius: 4px;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.inquiry-title {
  margin: 0 0 6px;
  font-size: 1.35rem;
  font-weight: 700;
  color: #2F4533;
}

.inquiry-subtitle {
  margin: 0;
  font-size: 0.88rem;
  color: #7A6F68;
  line-height: 1.45;
}

.btn-close-inquiry {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #7A6F68;
  cursor: pointer;
  line-height: 1;
  padding: 0 6px;
  transition: color 0.2s;
}

.btn-close-inquiry:hover {
  color: #2C221E;
}

.inquiry-item-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: #FAF8F5;
  border-bottom: 1px solid #E8DFD5;
}

.inquiry-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #E8DFD5;
}

.inquiry-thumb-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.inquiry-thumb-meta strong {
  font-size: 0.92rem;
  color: #2C221E;
}

.inquiry-category {
  font-size: 0.78rem;
  color: #7A6F68;
}

.inquiry-form {
  padding: 20px 24px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  gap: 14px;
}

.form-row-2 {
  grid-template-columns: 1fr 1fr;
}

@media (max-width: 600px) {
  .form-row-2 {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #2C221E;
}

.req {
  color: #C25E3E;
}

.form-control {
  padding: 9px 12px;
  border: 1px solid #D9CFC4;
  border-radius: 6px;
  font-size: 0.9rem;
  font-family: inherit;
  color: #2C221E;
  background: #FFFFFF;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #2F4533;
  box-shadow: 0 0 0 3px rgba(47, 69, 51, 0.12);
}

.field-hint {
  font-size: 0.75rem;
  color: #7A6F68;
  line-height: 1.35;
}

.inquiry-alert {
  padding: 12px 16px;
  border-radius: 8px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  font-size: 0.88rem;
  line-height: 1.45;
}

.inquiry-alert p {
  margin: 4px 0 0;
}

.alert-success {
  background: #E8F5E9;
  border: 1px solid #C8E6C9;
  color: #2E7D32;
}

.alert-error {
  background: #FDF2F0;
  border: 1px solid #F3C5BA;
  color: #C25E3E;
}

.alert-icon {
  font-size: 1.2rem;
  font-weight: 700;
}

.inquiry-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
  padding-top: 14px;
  border-top: 1px solid #E8DFD5;
}
</style>
