<template>
  <main v-if="product">
    <!-- 1. BREADCRUMBS & NAVIGATION RETURN -->
    <section class="details-top-bar">
      <div class="container">
        <div class="top-nav-row">
          <router-link to="/products" class="back-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Product Catalog</span>
          </router-link>

          <div class="breadcrumb-trail">
            <router-link to="/">Home</router-link>
            <span class="breadcrumb-separator">/</span>
            <router-link to="/products">Catalog</router-link>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ product.title }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. PRODUCT DETAILS SHOWCASE -->
    <section class="product-details-section">
      <div class="container">
        
        <div class="product-details-grid">

          <!-- Visual Column: Large Image & Trust Banner -->
          <div class="details-gallery-col">
            <div class="main-image-frame">
              <img :src="activeImage || product.image || defaultPlaceholder" :alt="product.title" class="main-product-img" @error="handleImgError">
              <span v-if="product.badge" class="product-tag-float">{{ product.badge }}</span>
              <WishlistButton :product="product" variant="icon" size="md" class="details-wishlist-float" />
              <div class="eco-badge-corner">
                <span>🌱 100% Genuine Craft</span>
              </div>
            </div>

            <!-- Thumbnail gallery: supports multiple product photos -->
            <div v-if="galleryImages.length > 1" class="gallery-thumbnails">
              <button
                v-for="(image, index) in galleryImages"
                :key="`${image}-${index}`"
                type="button"
                class="gallery-thumb"
                :class="{ 'gallery-thumb-active': activeImage === image }"
                :aria-label="`View product photo ${index + 1}`"
                @click="activeImage = image"
              >
                <img :src="image" :alt="`${product.title} photo ${index + 1}`" @error="handleImgError">
              </button>
            </div>

            <!-- Artisan Craftsmanship Trust Banner -->
            <div class="artisan-trust-card">
              <div class="trust-icon">🛡️</div>
              <div class="trust-text">
                <h4>Fair Trade & Artisan Direct</h4>
                <p>Over 90% of your payment is directly deposited into the artisan's local workshop account upon confirmed delivery.</p>
              </div>
            </div>
          </div>

          <!-- Information Column -->
          <div class="details-info-col">
            
            <!-- Category & Rating -->
            <div class="info-top-meta">
              <span class="category-pill">{{ product.category }}</span>
              <div class="rating-badge rating-badge-interactive" @click="scrollToReviews" title="Click to view customer reviews">
                <ProductRating :rating="displayRating" :count="displayReviewsCount" :show-count="true" size="sm" />
              </div>
            </div>

            <!-- Product Title -->
            <h1 class="details-product-title">{{ product.title }}</h1>

            <!-- Pricing & Stock Status -->
            <div class="details-price-card">
              <div class="price-display">
                <span class="currency-symbol">₱</span>
                <span class="price-value">{{ Number(product.price).toFixed(2) }}</span>
              </div>
              <div class="stock-indicator">
                <span class="stock-dot" :class="{ 'dot-low': product.stock <= 5 }"></span>
                <span>
                  {{ product.stock > 0 ? (product.stock <= 5 ? `Low Stock (Only ${product.stock} available)` : `In Stock (${product.stock} available)`) : 'Out of Stock' }}
                </span>
              </div>
            </div>

            <!-- Product Description -->
            <div class="details-description-block">
              <h3 class="block-heading">About this Craft</h3>
              <p class="description-text">{{ product.description }}</p>
            </div>

            <!-- Craft Specifications Table -->
            <div class="specs-grid">
              <div class="spec-item">
                <span class="spec-label">Materials:</span>
                <span class="spec-value">{{ product.materials || 'Natural Local Materials' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Dimensions:</span>
                <span class="spec-value">{{ product.dimensions || 'Standard Artisan Craft Sizing' }}</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Lead Time:</span>
                <span class="spec-value">Ships within 24-48 hours</span>
              </div>
              <div class="spec-item">
                <span class="spec-label">Origin:</span>
                <span class="spec-value">{{ product.origin || product.location }}</span>
              </div>
            </div>

            <!-- Purchase Actions & Subtotal Calculation -->
            <div class="purchase-box">
              <!-- Administrator Inspection Mode Banner (Purchasing Disabled) -->
              <div v-if="isAdmin" class="admin-preview-box" style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 8px; padding: 16px;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #B78103; font-size: 0.95rem;">
                  <span>🛡️ Administrator Inspection Mode</span>
                </div>
                <p style="margin: 8px 0 12px; font-size: 0.85rem; color: #6D4C41; line-height: 1.5;">
                  You are inspecting this craft listing as an Administrator. Purchasing and cart operations are disabled for administrator accounts.
                </p>
                <router-link to="/dashboard/admin?tab=products" class="btn btn-secondary btn-sm" style="font-size: 0.82rem;">
                  ← Back to Admin Products Management
                </router-link>
              </div>

              <!-- Customer Purchasing Controls -->
              <template v-else>
                <div class="purchase-top-row">
                  <div class="quantity-control-group">
                    <label for="quantityInput" class="quantity-label">Quantity:</label>
                    <div class="quantity-selector">
                      <button type="button" @click="decreaseQty" class="qty-btn" aria-label="Decrease quantity" :disabled="quantity <= 1">−</button>
                      <input type="number" id="quantityInput" v-model.number="quantity" min="1" :max="product.stock" @change="validateQty">
                      <button type="button" @click="increaseQty" class="qty-btn" aria-label="Increase quantity" :disabled="quantity >= product.stock">+</button>
                    </div>
                  </div>

                  <div class="subtotal-indicator">
                    <span class="subtotal-label">Subtotal:</span>
                    <span class="subtotal-amount">₱{{ calculatedSubtotal }}</span>
                  </div>
                </div>

                <!-- Feedback message -->
                <div v-if="cartFeedback" class="cart-feedback-note" :class="{ 'note-error': feedbackIsError }">
                  {{ cartFeedback }}
                </div>

                <div class="details-cta-group">
                  <button 
                    type="button" 
                    @click="addToCart" 
                    class="btn btn-primary btn-add-cart-large"
                    :disabled="product.stock <= 0"
                  >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                      <line x1="3" y1="6" x2="21" y2="6"></line>
                      <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>Add to Cart ({{ quantity }})</span>
                  </button>

                  <WishlistButton :product="product" variant="full" size="md" class="btn-details-wishlist" />
                </div>

                <!-- Custom Craft Inquiry Button -->
                <div class="custom-inquiry-trigger-wrapper">
                  <button 
                    type="button" 
                    @click="openInquiryModal" 
                    class="btn btn-custom-inquiry"
                  >
                    🎨 Request Custom / Bespoke Craft
                  </button>
                  <span class="custom-inquiry-hint">Have specific dimensions, color preferences, or bulk order requests? Inquire directly with the maker.</span>
                </div>
              </template>
            </div>

            <!-- Artisan Maker Profile Card -->
            <div class="seller-profile-card">
              <div class="seller-avatar-wrapper">
                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&q=80" alt="Artisan Maker" class="seller-avatar">
                <span class="verified-icon" title="Verified Artisan Maker">✓</span>
              </div>
              <div class="seller-details">
                <span class="seller-badge">Verified Local Artisan</span>
                <h4 class="seller-name">{{ product.seller }}</h4>
                <p class="seller-location">📍 {{ product.location || "Laguna, Philippines" }}</p>
                <p class="seller-bio">Dedicated to reviving regional handmade traditions using sustainable and responsibly sourced natural materials.</p>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section>

    <!-- 2.5 PRODUCT REVIEWS & RATINGS SECTION -->
    <section class="product-reviews-section" id="reviews-section">
      <div class="container">
        <div class="section-header-split reviews-section-header">
          <div>
            <span class="section-eyebrow">AUTHENTIC FEEDBACK</span>
            <h2 class="section-title">Customer Reviews & Ratings</h2>
            <p class="reviews-section-subtitle">Real experiences shared by verified CraftMarket patrons.</p>
          </div>
          <div class="reviews-summary-overview" v-if="displayReviewsCount > 0">
            <div class="rating-large-score">{{ displayRating.toFixed(1) }}</div>
            <div class="rating-large-details">
              <ProductRating :rating="displayRating" :count="0" :show-count="false" size="md" />
              <span class="rating-large-subtext">Based on {{ displayReviewsCount }} verified {{ displayReviewsCount === 1 ? 'review' : 'reviews' }}</span>
            </div>
          </div>
        </div>

        <div class="reviews-split-grid">
          <!-- Column 1: Reviews List -->
          <div class="reviews-column-list">
            <div class="reviews-column-header">
              <h3 class="reviews-column-title">Reviews ({{ displayReviewsCount }})</h3>
            </div>
            <ReviewList
              :reviews="reviews"
              :current-user-id="currentUserId"
              :is-admin="isAdmin"
              :product-id="Number(product.id)"
              @edit="handleEditReview"
              @delete="handleDeleteReview"
            />
          </div>

          <!-- Column 2: Review Form / Eligibility -->
          <div class="reviews-column-form">
            <div v-if="!isSeller" class="review-form-wrapper">
              <ReviewForm
                :product-id="Number(product.id)"
                :is-eligible="reviewEligibility.eligible"
                :ineligible-message="reviewEligibility.reason"
                :existing-review="editingReview"
                @submitted="onReviewSubmitted"
                @cancel="editingReview = null"
              />
            </div>
            <div v-else class="seller-review-restricted-card">
              <h4>Artisan / Seller Account</h4>
              <p>Reviews can only be submitted by verified customers who purchased this item in a completed order.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. RELATED PRODUCTS SECTION -->
    <section class="related-products-section">
      <div class="container">
        <div class="section-header-split">
          <div>
            <span class="section-eyebrow">MORE FROM THIS CRAFT</span>
            <h2 class="section-title">You Might Also Like</h2>
          </div>
          <router-link to="/products" class="view-all-link">
            Explore Full Catalog ↗
          </router-link>
        </div>

        <div class="related-grid">
          <ProductCard 
            v-for="rel in relatedProducts" 
            :key="rel.id" 
            :product="rel" 
          />
        </div>
      </div>
    </section>

  </main>
  
  <!-- Loading state indicator -->
  <div v-if="isLoading" class="container" style="padding: 100px 0; text-align: center; color: #7A6F68;">
    <p style="font-size: 1.2rem;">Loading artisan product details from CraftMarket API...</p>
  </div>

  <!-- Fallback if ID is invalid -->
  <div v-else-if="!product" class="container" style="padding: 100px 0; text-align: center;">
    <h2>Product Not Found</h2>
    <p>The craft piece you are looking for might have been moved or is currently sold out.</p>
    <router-link to="/products" class="btn btn-primary" style="margin-top: 20px;">Return to Catalog</router-link>
  </div>

  <!-- Custom Craft Inquiry Modal -->
  <CraftInquiryModal
    v-if="showInquiryModal && product"
    :product="product"
    @close="showInquiryModal = false"
    @inquiry-sent="showInquiryModal = false"
  />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductCard from '../components/ProductCard.vue';
import WishlistButton from '../components/WishlistButton.vue';
import ProductRating from '../components/ProductRating.vue';
import ReviewList from '../components/ReviewList.vue';
import ReviewForm from '../components/ReviewForm.vue';
import CraftInquiryModal from '../components/CraftInquiryModal.vue';
import { productService } from '../services/productService.js';
import { authService } from '../services/authService.js';
import { getProductReviews, checkReviewEligibility, deleteReview } from '../services/reviewService.js';
import { cart } from '../data/cart.js';
import defaultPlaceholder from '../assets/images/craft-placeholder.svg';

const route = useRoute();
const router = useRouter();

const product = ref(null);
const relatedProducts = ref([]);
const activeImage = ref('');
const quantity = ref(1);
const isLoading = ref(true);
const cartFeedback = ref('');
const feedbackIsError = ref(false);
const showInquiryModal = ref(false);

// ─── Review State ─────────────────────────────────────────────────────────────
const reviews = ref([]);
const reviewsAvg = ref(0);
const reviewsCountFromApi = ref(0);
const reviewEligibility = ref({ eligible: false, reason: 'Sign in to review.' });
const editingReview = ref(null);

const currentUserId = computed(() => authService.getUser()?.id || null);
const isAdmin = computed(() => authService.isAdmin());
const isSeller = computed(() => authService.isSeller());

const displayReviewsCount = computed(() => {
  if (reviews.value.length > 0) return reviews.value.length;
  if (reviewsCountFromApi.value > 0) return reviewsCountFromApi.value;
  return product.value?.reviewsCount || 0;
});

const displayRating = computed(() => {
  if (reviews.value.length > 0 && reviewsAvg.value > 0) return reviewsAvg.value;
  return Number(product.value?.rating) || 0;
});

const galleryImages = computed(() => {
  if (!product.value) return [];

  const fromGallery = Array.isArray(product.value.gallery)
    ? product.value.gallery.filter(Boolean)
    : [];

  const fallback = [];
  if (product.value.image) fallback.push(product.value.image);
  if (Array.isArray(product.value.images)) {
    fallback.push(...product.value.images.filter(Boolean));
  }

  return [...new Set(fromGallery.length ? fromGallery : fallback)];
});

const handleImgError = (e) => {
  e.target.src = defaultPlaceholder;
};

const scrollToReviews = () => {
  const el = document.getElementById('reviews-section');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};

const loadReviews = async (productId) => {
  try {
    const res = await getProductReviews(productId);
    if (res && res.success) {
      reviews.value = res.data || [];
      reviewsAvg.value = Number(res.avg_rating) || 0;
      reviewsCountFromApi.value = Number(res.review_count) || 0;
    }
  } catch (err) {
    console.warn('[Review] Could not fetch reviews:', err);
    reviews.value = [];
  }
};

const checkEligibility = async (productId) => {
  if (!authService.isAuthenticated()) {
    reviewEligibility.value = {
      eligible: false,
      reason: 'Sign in to your customer account to leave a review for purchased items.',
    };
    return;
  }

  if (authService.isSeller()) {
    reviewEligibility.value = {
      eligible: false,
      reason: 'Artisans and sellers cannot review products.',
    };
    return;
  }

  try {
    const res = await checkReviewEligibility(productId);
    if (res) {
      if (res.reason === 'already_reviewed') {
        reviewEligibility.value = {
          eligible: false,
          reason: 'You have already submitted a review for this product. You may edit your existing review.',
        };
      } else {
        reviewEligibility.value = {
          eligible: Boolean(res.eligible),
          reason: res.reason || 'Only customers who purchased this item with a completed order can leave a review.',
        };
      }
    }
  } catch (err) {
    reviewEligibility.value = {
      eligible: false,
      reason: 'Could not verify purchase eligibility.',
    };
  }
};

const handleEditReview = (review) => {
  editingReview.value = review;
  scrollToReviews();
};

const handleDeleteReview = async (review) => {
  try {
    await deleteReview(product.value.id, review.id);
    if (editingReview.value?.id === review.id) {
      editingReview.value = null;
    }
    await loadReviews(product.value.id);
    await checkEligibility(product.value.id);
  } catch (err) {
    alert(err?.response?.data?.message || 'Failed to remove review.');
  }
};

const onReviewSubmitted = async () => {
  editingReview.value = null;
  if (product.value) {
    await loadReviews(product.value.id);
    await checkEligibility(product.value.id);
  }
};

const loadProductData = async (id) => {
  isLoading.value = true;
  try {
    const found = await productService.getById(id);
    product.value = found || null;
    activeImage.value = galleryImages.value[0] || '';

    if (found) {
      relatedProducts.value = await productService.getRelated(found.category, found.id);
      await loadReviews(found.id);
      await checkEligibility(found.id);
    }
  } catch (err) {
    console.error('Error loading product details:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadProductData(route.params.id);
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    quantity.value = 1;
    activeImage.value = '';
    cartFeedback.value = '';
    editingReview.value = null;
    loadProductData(newId);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

const calculatedSubtotal = computed(() => {
  if (!product.value) return '0.00';
  return (product.value.price * quantity.value).toFixed(2);
});

const validateQty = () => {
  if (quantity.value < 1) quantity.value = 1;
  if (product.value && quantity.value > product.value.stock) {
    quantity.value = product.value.stock;
    cartFeedback.value = `Maximum available stock is ${product.value.stock}.`;
    feedbackIsError.value = true;
  } else {
    cartFeedback.value = '';
    feedbackIsError.value = false;
  }
};

const decreaseQty = () => {
  if (quantity.value > 1) {
    quantity.value--;
    cartFeedback.value = '';
  }
};

const increaseQty = () => {
  if (product.value && quantity.value < product.value.stock) {
    quantity.value++;
    cartFeedback.value = '';
  }
};

const addToCart = () => {
  if (!product.value) return;
  if (isAdmin.value || authService.isAdmin()) {
    alert('Administrators cannot purchase items. Please log in with a customer account to shop.');
    return;
  }
  if (!authService.isAuthenticated()) {
    alert('Please log in or create an account to add items to your shopping cart.');
    router.push({ name: 'login', query: { redirect: route.fullPath } });
    return;
  }
  const result = cart.addToCart(product.value, quantity.value);
  if (result.success) {
    cartFeedback.value = ` Successfully added ${quantity.value} item(s) to cart!`;
    feedbackIsError.value = false;
    setTimeout(() => { cartFeedback.value = ''; }, 3500);
  } else {
    cartFeedback.value = result.message;
    feedbackIsError.value = true;
  }
};

const openInquiryModal = () => {
  if (isAdmin.value || authService.isAdmin()) {
    alert('Administrators cannot submit inquiries. Please use a customer account.');
    return;
  }
  if (!authService.isAuthenticated()) {
    alert('Please log in with your customer account to submit a custom craft inquiry.');
    router.push({ name: 'login', query: { redirect: route.fullPath } });
    return;
  }
  showInquiryModal.value = true;
};
</script>

<style scoped>
.rating-badge-interactive {
  cursor: pointer;
  transition: opacity 0.2s;
}
.rating-badge-interactive:hover {
  opacity: 0.85;
}

/* ── Reviews Section ── */
.product-reviews-section {
  padding: 60px 0;
  border-top: 1px solid #efe8de;
  background: #fdfbf7;
}

.reviews-section-header {
  margin-bottom: 36px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 20px;
}

.reviews-section-subtitle {
  color: #7a6a60;
  font-size: 0.95rem;
  margin-top: 6px;
}

.reviews-summary-overview {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 12px;
  padding: 14px 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.rating-large-score {
  font-size: 2.2rem;
  font-weight: 800;
  color: #2d2420;
  line-height: 1;
}

.rating-large-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rating-large-subtext {
  font-size: 0.78rem;
  color: #9e8f85;
}

.reviews-split-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 36px;
  align-items: start;
}

@media (max-width: 860px) {
  .reviews-split-grid {
    grid-template-columns: 1fr;
  }
}

.reviews-column-header {
  margin-bottom: 16px;
}

.reviews-column-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2d2420;
}

.seller-review-restricted-card {
  background: #ffffff;
  border: 1px dashed #d6ccc2;
  border-radius: 14px;
  padding: 24px;
  text-align: center;
  color: #7a6a60;
}

.seller-review-restricted-card h4 {
  font-size: 1rem;
  color: #3e5943;
  margin-bottom: 8px;
}

.seller-review-restricted-card p {
  font-size: 0.88rem;
  margin: 0;
}

/* Purchase section styling */
.purchase-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.subtotal-indicator {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.subtotal-label {
  font-size: 0.8rem;
  color: #6b635b;
}

.subtotal-amount {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--cm-charcoal, #2C221E);
}

.cart-feedback-note {
  font-size: 0.85rem;
  font-weight: 500;
  color: #2e7d32;
  margin-bottom: 12px;
}

.cart-feedback-note.note-error {
  color: #c25e3e;
}

.dot-low {
  background-color: #c25e3e !important;
}

.details-wishlist-float {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 4;
}

.details-cta-group {
  display: flex;
  gap: 12px;
  align-items: center;
}

.details-cta-group .btn-add-cart-large {
  flex: 1;
}

@media (max-width: 580px) {
  .details-cta-group {
    flex-direction: column;
    align-items: stretch;
  }
}

/* ── Product Photo Gallery ── */
.gallery-thumbnails {
  display: flex;
  gap: 10px;
  margin-top: 12px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.gallery-thumb {
  flex: 0 0 auto;
  width: 74px;
  height: 74px;
  padding: 0;
  border: 2px solid #efe8de;
  border-radius: 10px;
  background: #ffffff;
  overflow: hidden;
  cursor: pointer;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.gallery-thumb:hover {
  transform: translateY(-2px);
  border-color: #c25e3e;
}

.gallery-thumb-active {
  border-color: #c25e3e;
  box-shadow: 0 0 0 2px rgba(194, 94, 62, 0.18);
}

.gallery-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* ── Custom Craft Inquiry Button ── */
.custom-inquiry-trigger-wrapper {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px dashed #E8DFD5;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.btn-custom-inquiry {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px 18px;
  background: #FAF8F5;
  border: 1.5px solid #2F4533;
  color: #2F4533;
  font-weight: 600;
  font-size: 0.92rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
}

.btn-custom-inquiry:hover {
  background: #2F4533;
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(47, 69, 51, 0.2);
}

.custom-inquiry-hint {
  font-size: 0.76rem;
  color: #7A6F68;
  line-height: 1.35;
  text-align: center;
}
</style>
