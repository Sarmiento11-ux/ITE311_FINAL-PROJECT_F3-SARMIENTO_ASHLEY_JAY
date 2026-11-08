<template>
  <section class="hero-section">
    <div class="container hero-grid">
      
      <div class="hero-content">
        <div class="hero-tag">
          <span class="tag-indicator"></span>
          Authentic Local Craftsmanship
        </div>

        <h1 class="hero-title">
          {{ heroContent.title }}
        </h1>

        <p class="hero-description">
          {{ heroContent.description }}
        </p>

        <div class="hero-cta-group">
          <router-link :to="heroContent.ctaLink || '/products'" class="btn btn-primary">
            {{ heroContent.ctaText || 'Explore Collection' }}
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </router-link>
          <a href="#how-it-works" class="btn btn-secondary">
            How It Works
          </a>
        </div>

        <!-- Marketplace Proof Badges -->
        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">120+</span>
            <span class="stat-label">Verified Artisans</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-number">1,800+</span>
            <span class="stat-label">Handcrafted Items</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-number">4.9 ★</span>
            <span class="stat-label">Community Rating</span>
          </div>
        </div>
      </div>

      <!-- Hero Visual Showcase -->
      <div class="hero-visual">
        <div class="hero-image-wrapper">
          <img 
            src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=85" 
            alt="Artisan workshop with handmade pottery and ceramics"
            class="hero-main-img"
          >
          
          <!-- Floating Artisan Profile Card — data loaded from API -->
          <div class="floating-artisan-card" v-if="featuredSeller">
            <img 
              :src="featuredSeller.avatar"
              :alt="featuredSeller.name + ' - Featured Artisan'"
              class="artisan-avatar"
              @error="handleAvatarError"
            >
            <div class="artisan-info">
              <span class="badge-featured">Featured Maker</span>
              <h4 class="artisan-name">{{ featuredSeller.name }}</h4>
              <p class="artisan-craft">{{ featuredSeller.shop }} • {{ featuredSeller.location }}</p>
            </div>
          </div>

          <!-- Floating Eco-Friendly Pill -->
          <div class="floating-eco-pill">
            
            <span>🌱 100% Sustainable & Handmade</span>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { cmsService } from '../services/cmsService.js';
import apiClient from '../services/api.js';

const heroContent = ref({
  eyebrow: 'SUPPORT LOCAL ARTISANS & CULTURAL HERITAGE',
  title: 'Every handmade piece has a story to tell.',
  description: 'CraftMarket connects local independent artisans, woodcarvers, weavers, and potters directly with conscious shoppers. Discover small-batch treasures crafted with patience, passion, and natural materials.',
  ctaText: 'Explore Collection',
  ctaLink: '/products',
  isFromCms: false,
});

// Featured seller pulled from real API data
const featuredSeller = ref(null);

const FALLBACK_AVATAR = 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=120&q=80';

function handleAvatarError(e) {
  e.target.src = FALLBACK_AVATAR;
}

onMounted(async () => {
  // Load CMS hero content
  try {
    const data = await cmsService.getHeroContent();
    if (data) {
      heroContent.value = data;
    }
  } catch (err) {
    console.warn('[HeroSection] Could not load CMS hero content:', err);
  }

  // Load a real featured seller from the API
  try {
    const res = await apiClient.get('/products', { params: { featured: 1, paginate: 0 } });
    if (res.data?.success && res.data.data?.length > 0) {
      const product = res.data.data[0];
      const seller = product.seller;
      if (seller && typeof seller === 'object') {
        featuredSeller.value = {
          name: seller.name || 'Filipino Artisan',
          shop: seller.seller_profile?.shop_name || seller.sellerProfile?.shop_name || 'Independent Studio',
          location: seller.address || 'Philippines',
          avatar: seller.avatar || FALLBACK_AVATAR,
        };
      }
    }
  } catch (err) {
    console.warn('[HeroSection] Could not load featured seller:', err);
  }

  // If API didn't return a seller object (normalized product already has flat seller name), show nothing
  // rather than show fake data — the v-if="featuredSeller" handles this gracefully
});
</script>
