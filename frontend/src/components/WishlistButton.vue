<template>
  <button
    type="button"
    class="wishlist-btn"
    :class="[
      `variant-${variant}`,
      `size-${size}`,
      { 'is-saved': isSaved, 'is-loading': loading }
    ]"
    :title="isSaved ? 'Remove from favorites' : 'Save to favorites'"
    :aria-label="isSaved ? 'Remove from favorites' : 'Save to favorites'"
    @click.stop.prevent="handleToggle"
    :disabled="loading"
  >
    <!-- Heart Icon SVG -->
    <svg
      class="heart-icon"
      :width="iconSize"
      :height="iconSize"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path
        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
      />
    </svg>

    <!-- Optional Label for 'full' variant -->
    <span v-if="variant === 'full'" class="wishlist-btn-label">
      {{ isSaved ? 'Saved to Favorites' : 'Save to Wishlist' }}
    </span>
  </button>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { wishlist } from '../data/wishlist.js';
import { authService } from '../services/authService.js';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  variant: {
    type: String,
    default: 'icon', // 'icon' | 'full'
  },
  size: {
    type: String,
    default: 'md', // 'sm' | 'md' | 'lg'
  },
});

const router = useRouter();
const loading = ref(false);

const productId = computed(() => props.product?.id || props.product?.productId);
const isSaved = computed(() => wishlist.isWishlisted(productId.value));

const iconSize = computed(() => {
  if (props.size === 'sm') return 16;
  if (props.size === 'lg') return 22;
  return 18;
});

const handleToggle = async () => {
  if (!authService.isAuthenticated()) {
    // Alert guest and redirect to login
    alert('Please log in or create an account to save items to your favorites.');
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } });
    return;
  }

  loading.value = true;
  try {
    await wishlist.toggle(props.product);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.wishlist-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  font-family: inherit;
  border: none;
  background: transparent;
  color: #7a6a60;
  padding: 0;
}

/* ── Icon-only variant (used in Product Cards) ── */
.variant-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.variant-icon.size-sm {
  width: 30px;
  height: 30px;
}

.variant-icon.size-lg {
  width: 44px;
  height: 44px;
}

.variant-icon:hover {
  background: #ffffff;
  color: #c25e3e;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(194, 94, 62, 0.25);
}

.variant-icon:active {
  transform: scale(0.95);
}

/* ── Saved state (filled terracotta heart) ── */
.wishlist-btn.is-saved .heart-icon {
  fill: #c25e3e;
  stroke: #c25e3e;
}

.wishlist-btn.is-saved {
  color: #c25e3e;
}

/* ── Full button variant (used in Product Details) ── */
.variant-full {
  padding: 10px 18px;
  border-radius: 8px;
  border: 1px solid #d4c8be;
  background: #ffffff;
  color: #4a3f38;
  font-size: 0.9rem;
  font-weight: 600;
}

.variant-full:hover {
  border-color: #c25e3e;
  color: #c25e3e;
  background: #fdfaf6;
}

.variant-full.is-saved {
  border-color: #c25e3e;
  background: #fceee9;
  color: #b84f33;
}

.heart-icon {
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), fill 0.2s ease, stroke 0.2s ease;
}

.is-saved .heart-icon {
  animation: heartPulse 0.35s ease-out;
}

@keyframes heartPulse {
  0% { transform: scale(0.8); }
  50% { transform: scale(1.25); }
  100% { transform: scale(1); }
}

.is-loading {
  opacity: 0.6;
  pointer-events: none;
}

/* Ensure wishlist buttons always stay auto width (never stretch from global .btn rule) */
.wishlist-btn {
  width: auto !important;
}

/* Keyboard focus ring */
.wishlist-btn:focus-visible {
  outline: 2px solid var(--cm-forest, #3E5943);
  outline-offset: 3px;
  border-radius: 50%;
}
</style>
