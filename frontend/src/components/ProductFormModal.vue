<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-box">
      <div class="modal-header-row">
        <h3 class="modal-title">{{ isEdit ? 'Edit Product Listing' : 'Add New Handmade Craft' }}</h3>
        <button type="button" class="btn-modal-close" @click="$emit('close')">&times;</button>
      </div>

      <form @submit.prevent="handleSubmit">
        
        <!-- Error Banner -->
        <div v-if="validationError" class="modal-alert-danger">
          {{ validationError }}
        </div>

        <!-- Product Image Management Section -->
        <div class="form-group image-upload-section">
          <label class="form-label">
            Product Photography <span class="required">*</span>
            <small class="label-hint">JPG, PNG, WEBP &middot; Up to 15MB</small>
          </label>

          <div class="image-uploader-card">
            <!-- Preview Box -->
            <div class="image-preview-wrapper">
              <img 
                :src="previewUrl || currentDisplayImage" 
                :alt="formData.name || 'Product preview'" 
                class="image-preview-thumb"
                @error="handlePreviewError"
              >
              <div v-if="isUploading" class="uploading-overlay">
                <div class="mini-spinner"></div>
                <span>Uploading…</span>
              </div>
            </div>

            <!-- Upload Controls -->
            <div class="image-uploader-controls">
              <div class="upload-buttons-row">
                <!-- Hidden file input -->
                <input 
                  type="file" 
                  ref="fileInputRef" 
                  @change="handleFileChange" 
                  accept="image/jpeg,image/png,image/webp" 
                  style="display: none;"
                >

                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  @click="triggerFileInput"
                  :disabled="isUploading"
                >
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                  </svg>
                  {{ (previewUrl || formData.image) ? 'Replace Image' : 'Upload Image' }}
                </button>

                <button 
                  v-if="previewUrl || formData.image"
                  type="button" 
                  class="btn btn-outline-danger btn-sm"
                  @click="handleRemoveImage"
                  :disabled="isUploading"
                  title="Remove image"
                >
                  Remove
                </button>
              </div>

              <!-- File metadata / status notice -->
              <div v-if="selectedFile" class="file-meta-tag">
                Selected: <strong>{{ selectedFile.name }}</strong> ({{ formatFileSize(selectedFile.size) }})
              </div>
              <div v-else-if="formData.image && !isImageRemoved" class="file-meta-tag text-muted">
                Current image active
              </div>
              <div v-else class="file-meta-tag text-muted">
                No image selected &middot; Default craft placeholder will be used
              </div>
            </div>
          </div>
        </div>

        <!-- Product Name -->
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

        <!-- Category & Price -->
        <div class="form-row-2col">
          <div class="form-group">
            <label class="form-label">Category <span class="required">*</span></label>
            <select v-model="formData.category" class="form-input" required>
              <option value="" disabled>Select craft category...</option>
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

        <!-- Stock & Status -->
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
              <option value="Draft">Draft (Hidden)</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>
        </div>

        <!-- Craft Description -->
        <div class="form-group">
          <label class="form-label">Artisan Description</label>
          <textarea 
            v-model="formData.description" 
            class="form-input" 
            rows="3" 
            placeholder="Tell shoppers the story behind this handcrafted creation, materials used, and maker origin…"
          ></textarea>
        </div>

        <!-- Material & Dimensions -->
        <div class="form-row-2col">
          <div class="form-group">
            <label class="form-label">Materials Used</label>
            <input 
              type="text" 
              v-model="formData.material" 
              class="form-input" 
              placeholder="e.g. Natural Terracotta Clay, Food-safe Glaze"
            >
          </div>

          <div class="form-group">
            <label class="form-label">Dimensions</label>
            <input 
              type="text" 
              v-model="formData.dimensions" 
              class="form-input" 
              placeholder="e.g. 10cm x 8cm (350ml)"
            >
          </div>
        </div>

        <div class="modal-footer-actions">
          <button type="button" class="btn btn-secondary" @click="$emit('close')" :disabled="isUploading">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="isUploading">
            <span v-if="isUploading">Saving Product…</span>
            <span v-else>{{ isEdit ? 'Save Changes' : 'Publish Product' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';

const props = defineProps({
  initialData: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  },
  isUploading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['save', 'close']);

const fileInputRef    = ref(null);
const selectedFile    = ref(null);
const previewUrl      = ref('');
const isImageRemoved  = ref(false);
const validationError = ref('');

const formData = reactive({
  id: null,
  name: '',
  category: '',
  price: 450,
  stock: 10,
  status: 'Published',
  image: '',
  description: '',
  material: '',
  dimensions: ''
});

const currentDisplayImage = computed(() => {
  if (isImageRemoved.value) return defaultPlaceholder;
  return formData.image || defaultPlaceholder;
});

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    Object.assign(formData, newVal);
    selectedFile.value = null;
    previewUrl.value = '';
    isImageRemoved.value = false;
    validationError.value = '';
  }
}, { immediate: true });

const triggerFileInput = () => {
  validationError.value = '';
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const compressImage = (file, maxWidth = 1600, maxHeight = 1600, quality = 0.88) => {
  return new Promise((resolve) => {
    if (file.type === 'image/svg+xml' || file.size < 400 * 1024) {
      return resolve(file);
    }
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target.result;
      img.onload = () => {
        let width = img.width;
        let height = img.height;

        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            height = maxHeight;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        const mime = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        canvas.toBlob((blob) => {
          if (!blob) return resolve(file);
          const optimizedFile = new File([blob], file.name, {
            type: mime,
            lastModified: Date.now()
          });
          resolve(optimizedFile);
        }, mime, quality);
      };
      img.onerror = () => resolve(file);
    };
    reader.onerror = () => resolve(file);
  });
};

const handleFileChange = async (e) => {
  validationError.value = '';
  const file = e.target.files?.[0];
  if (!file) return;

  // 1. Validate file type: JPG, JPEG, PNG, WEBP
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
  if (!allowedTypes.includes(file.type.toLowerCase())) {
    validationError.value = 'Unsupported image format. Please upload a JPG, PNG, or WEBP image.';
    e.target.value = '';
    return;
  }

  // 2. Validate max file size: 15MB
  const maxSize = 15 * 1024 * 1024;
  if (file.size > maxSize) {
    validationError.value = "The selected image is too large (" + formatFileSize(file.size) + "). Maximum allowed size is 15MB.";
    e.target.value = '';
    return;
  }

  // Client-side automatic optimization so large camera photos upload quickly without failing
  const optimizedFile = await compressImage(file);
  selectedFile.value = optimizedFile;
  isImageRemoved.value = false;

  // Create fast local browser preview URL
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
  }
  previewUrl.value = URL.createObjectURL(optimizedFile);
};

const handleRemoveImage = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = '';
  }
  selectedFile.value = null;
  isImageRemoved.value = true;
  formData.image = '';
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const handlePreviewError = (e) => {
  e.target.src = defaultPlaceholder;
};

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const handleSubmit = () => {
  validationError.value = '';

  if (!formData.category) {
    validationError.value = 'Please select a valid craft category for your listing.';
    return;
  }

  // Category ID resolution
  const categoryMap = {
    'Ceramics & Pottery': 1,
    'Woven Goods & Bags': 2,
    'Artisan Jewelry': 3,
    'Home Decor & Candles': 4,
    'Wooden Crafts': 5,
  };

  const categoryId = categoryMap[formData.category] || formData.categoryId || 1;

  emit('save', {
    ...formData,
    categoryId,
    imageFile: selectedFile.value,
    removeImage: isImageRemoved.value,
  });
};
</script>

<style scoped>
.modal-alert-danger {
  background-color: #fdf2f2;
  border: 1px solid #f8b4b4;
  color: #9b1c1c;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.88rem;
  margin-bottom: 16px;
}

.image-upload-section {
  margin-bottom: 18px;
}

.label-hint {
  font-size: 0.76rem;
  color: #7a6f68;
  font-weight: normal;
  margin-left: 6px;
}

.image-uploader-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fcfbf9;
  border: 1px solid #efe8de;
  border-radius: 10px;
  padding: 14px;
}

.image-preview-wrapper {
  position: relative;
  width: 90px;
  height: 90px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2d9cd;
  background: #f5efeb;
  flex-shrink: 0;
}

.image-preview-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.uploading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.85);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 600;
  color: #2f4533;
}

.mini-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid #e2d9cd;
  border-top-color: #2f4533;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 4px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.image-uploader-controls {
  flex: 1;
}

.upload-buttons-row {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 8px;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.82rem;
  display: inline-flex;
  align-items: center;
}

.btn-outline-danger {
  background: transparent;
  border: 1px solid #e57373;
  color: #c62828;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline-danger:hover {
  background: #ffebee;
}

.file-meta-tag {
  font-size: 0.8rem;
  color: #4a3e39;
}

.text-muted {
  color: #8c7e75 !important;
}

.modal-footer-actions {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e8dfd5;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
