<template>
  <main>
    <!-- 3. HERO SECTION -->
    <section class="hero-section">
      <div class="container hero-grid">
        
        <div class="hero-content">
          <div class="hero-tag">
            <span class="tag-indicator"></span>
            Authentic Local Craftsmanship
          </div>

          <h1 class="hero-title">
            Every handmade piece has a <span class="highlight-serif">story to tell.</span>
          </h1>

          <p class="hero-description">
            CraftMarket connects local independent artisans, woodcarvers, weavers, and potters directly with conscious shoppers. Discover small-batch treasures crafted with patience, passion, and natural materials.
          </p>

          <div class="hero-cta-group">
            <router-link to="/products" class="btn btn-primary">
              Explore Collection
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
            
            <!-- Floating Artisan Profile Card -->
            <div class="floating-artisan-card">
              <img 
                src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&q=80" 
                alt="Maria Santos - Featured Artisan"
                class="artisan-avatar"
              >
              <div class="artisan-info">
                <span class="badge-featured">Featured Maker</span>
                <h4 class="artisan-name">Maria Santos</h4>
                <p class="artisan-craft">Studio Clayworks • Laguna, PH</p>
              </div>
            </div>

            <!-- Floating Eco-Friendly Pill -->
            <div class="floating-eco-pill">
              <span class="eco-icon">🌿</span>
              <span>100% Sustainable & Handmade</span>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- 4. BUSINESS OVERVIEW SECTION -->
    <section class="business-overview" id="how-it-works">
      <div class="container">
        
        <div class="section-header text-center">
          <span class="section-eyebrow">OUR BUSINESS MODEL</span>
          <h2 class="section-title">How CraftMarket Powers the Artisan Economy</h2>
          <p class="section-subtitle">
            A sustainable, fair-trade marketplace ecosystem that supports independent creators while ensuring high product authenticity for buyers.
          </p>
        </div>

        <div class="overview-grid">
          
          <div class="overview-card">
            <div class="card-step-badge">01</div>
            <div class="overview-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
              </svg>
            </div>
            <h3 class="overview-card-title">Artisan Registration & Curation</h3>
            <p class="overview-card-text">
              Local sellers register their micro-enterprise and list products. Our team verifies handmade authenticity so buyers get true artisan goods, not mass-produced factory items.
            </p>
            <div class="overview-metric">Verified Maker Badges</div>
          </div>

          <div class="overview-card highlight-card">
            <div class="card-step-badge">02</div>
            <div class="overview-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
              </svg>
            </div>
            <h3 class="overview-card-title">Transparent Orders & Payments</h3>
            <p class="overview-card-text">
              Customers browse curated catalogs and checkout seamlessly. The platform securely holds payment in escrow until the artisan ships the verified order with QR confirmation.
            </p>
            <div class="overview-metric">Integrated Payment Escrow</div>
          </div>

          <div class="overview-card">
            <div class="card-step-badge">03</div>
            <div class="overview-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
            </div>
            <h3 class="overview-card-title">Sustainable Revenue Sharing</h3>
            <p class="overview-card-text">
              CraftMarket sustains its operations via a modest 8% transaction commission and optional featured promotion slots, allowing 92% of earnings to go directly into makers' pockets.
            </p>
            <div class="overview-metric">92% Direct Maker Payout</div>
          </div>

        </div>

      </div>
    </section>

    <!-- 5. MARKETPLACE BENEFITS -->
    <section class="benefits-section">
      <div class="container">
        <div class="benefits-grid">

          <div class="benefit-box">
            <div class="benefit-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              </svg>
            </div>
            <div>
              <h4 class="benefit-title">100% Genuine Handmade</h4>
              <p class="benefit-desc">Every piece is handcrafted by local creators using regional materials.</p>
            </div>
          </div>

          <div class="benefit-box">
            <div class="benefit-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </div>
            <div>
              <h4 class="benefit-title">Secure Order Protection</h4>
              <p class="benefit-desc">Integrated payment protection and QR verification for each transaction.</p>
            </div>
          </div>

          <div class="benefit-box">
            <div class="benefit-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </div>
            <div>
              <h4 class="benefit-title">Empowering Local Artisans</h4>
              <p class="benefit-desc">Support livelihood programs and cultural heritage preservation in rural towns.</p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- 6. PRODUCT CATEGORIES -->
    <section class="categories-section" id="categories">
      <div class="container">
        
        <div class="section-header-split">
          <div>
            <span class="section-eyebrow">CURATED COLLECTIONS</span>
            <h2 class="section-title">Shop by Craft Discipline</h2>
            <p class="section-subtitle">Explore distinct traditions, textures, and handmade techniques.</p>
          </div>
          <router-link to="/products" class="view-all-link">
            View All Categories
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </router-link>
        </div>

        <div class="category-grid">

          <router-link to="/products?category=Ceramics+%26+Pottery" class="category-card">
            <div class="category-media">
              <img 
                src="https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=600&q=80" 
                alt="Handcrafted pottery ceramics" 
                loading="lazy"
              >
              <span class="category-badge">42 Items</span>
              <span class="category-action-icon">↗</span>
            </div>
            <div class="category-meta">
              <h3 class="category-title">Ceramics & Pottery</h3>
              <p class="category-subtitle">Wheel-thrown mugs, planters & dining ware</p>
            </div>
          </router-link>

          <router-link to="/products?category=Woven+Goods+%26+Bags" class="category-card">
            <div class="category-media">
              <img 
                src="https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=600&q=80" 
                alt="Handwoven rattan and abaca goods" 
                loading="lazy"
              >
              <span class="category-badge">38 Items</span>
              <span class="category-action-icon">↗</span>
            </div>
            <div class="category-meta">
              <h3 class="category-title">Woven Goods & Bags</h3>
              <p class="category-subtitle">Indigenous weaves, abaca baskets & totes</p>
            </div>
          </router-link>

          <router-link to="/products?category=Artisan+Jewelry" class="category-card">
            <div class="category-media">
              <img 
                src="https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=600&q=80" 
                alt="Artisan jewelry and accessories" 
                loading="lazy"
              >
              <span class="category-badge">56 Items</span>
              <span class="category-action-icon">↗</span>
            </div>
            <div class="category-meta">
              <h3 class="category-title">Artisan Jewelry</h3>
              <p class="category-subtitle">Beaded necklaces, brass rings & stones</p>
            </div>
          </router-link>

          <router-link to="/products?category=Home+Decor+%26+Candles" class="category-card">
            <div class="category-media">
              <img 
                src="https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=600&q=80" 
                alt="Home decor and botanical crafts" 
                loading="lazy"
              >
              <span class="category-badge">29 Items</span>
              <span class="category-action-icon">↗</span>
            </div>
            <div class="category-meta">
              <h3 class="category-title">Home Decor & Candles</h3>
              <p class="category-subtitle">Soy wax candles, woodcrafts & accents</p>
            </div>
          </router-link>

        </div>

      </div>
    </section>

    <!-- 7. FEATURED HANDMADE PRODUCTS -->
    <section class="products-section" id="featured">
      <div class="container">
        
        <div class="section-header-split">
          <div>
            <span class="section-eyebrow">HANDPICKED THIS WEEK</span>
            <h2 class="section-title">Featured Artisan Creations</h2>
            <p class="section-subtitle">Individually produced in limited batches by registered sellers.</p>
          </div>

          <div class="product-filters">
            <button 
              class="filter-tab" 
              :class="{ active: currentTab === 'all' }"
              @click="currentTab = 'all'"
            >
              All Pieces
            </button>
            <button 
              class="filter-tab" 
              :class="{ active: currentTab === 'top-rated' }"
              @click="currentTab = 'top-rated'"
            >
              Top Rated
            </button>
            <button 
              class="filter-tab" 
              :class="{ active: currentTab === 'under-500' }"
              @click="currentTab = 'under-500'"
            >
              Under ₱500
            </button>
          </div>
        </div>

        <div class="product-grid">
          <ProductCard 
            v-for="item in filteredFeatured" 
            :key="item.id" 
            :product="item" 
          />
        </div>

        <div class="products-cta-banner">
          <div class="banner-text">
            <h3>Are you a local creator or craftsman?</h3>
            <p>Open your CraftMarket stall today. No setup fees, low commission, direct client reach.</p>
          </div>
          <router-link to="/register?role=seller" class="btn btn-primary" id="sellers">
            Start Selling on CraftMarket
          </router-link>
        </div>

      </div>
    </section>

    <!-- 8. ABOUT SECTION -->
    <section class="about-section" id="about">
      <div class="container about-grid">
        
        <div class="about-media-stack">
          <img 
            src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80" 
            alt="Potter shaping clay on a wheel" 
            class="about-img-main"
            loading="lazy"
          >
          <div class="about-badge-card">
            <span class="badge-icon">🏛</span>
            <div>
              <strong>Preserving Heritage</strong>
              <p>Supporting generations of Filipino craftsmen</p>
            </div>
          </div>
        </div>

        <div class="about-text-content">
          <span class="section-eyebrow">ABOUT CRAFTMARKET</span>
          <h2 class="section-title">Rooted in community. Built for genuine makers.</h2>
          <p class="about-lead">
            In an age of fast consumerism and mechanized mass production, CraftMarket was founded to reconnect people with the warmth of real human craft.
          </p>
          <p class="about-body">
            We provide micro-sellers, provincial weavers, and independent artisans with the modern digital marketplace tools they need—including order tracking, fair payment security, and direct customer communication—without taking heavy corporate cuts.
          </p>

          <div class="about-pillars">
            <div class="pillar-item">
              <span class="pillar-check">✓</span>
              <div>
                <strong>Fair Compensation</strong>
                <p>Makers set their own pricing and retain up to 92% of sales proceeds.</p>
              </div>
            </div>
            <div class="pillar-item">
              <span class="pillar-check">✓</span>
              <div>
                <strong>Traceable Origins</strong>
                <p>Know who made your product, where the raw materials were harvested, and their craft technique.</p>
              </div>
            </div>
          </div>

          <router-link to="/products" class="btn btn-secondary">
            Discover Artisan Stories ↗
          </router-link>
        </div>

      </div>
    </section>

  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ProductCard from '../components/ProductCard.vue';
import { productService } from '../services/productService.js';

const currentTab = ref('all');
const featuredProducts = ref([]);

onMounted(async () => {
  featuredProducts.value = await productService.getFeatured();
});

const filteredFeatured = computed(() => {
  if (currentTab.value === 'top-rated') {
    return featuredProducts.value.filter(p => p.rating >= 4.9);
  }
  if (currentTab.value === 'under-500') {
    return featuredProducts.value.filter(p => p.price <= 500);
  }
  return featuredProducts.value;
});
</script>
