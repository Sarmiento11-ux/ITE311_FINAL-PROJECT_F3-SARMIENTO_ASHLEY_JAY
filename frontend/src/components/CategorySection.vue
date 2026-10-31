<template>
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
        <router-link 
          v-for="cat in categories" 
          :key="cat.name"
          :to="`/products?category=${encodeURIComponent(cat.name)}`" 
          class="category-card"
        >
          <div class="category-media">
            <img 
              :src="cat.image" 
              :alt="cat.name" 
              loading="lazy"
            >
            <span class="category-badge">{{ cat.count }}</span>
            <span class="category-action-icon">↗</span>
          </div>
          <div class="category-meta">
            <h3 class="category-title">{{ cat.name }}</h3>
            <p class="category-subtitle">{{ cat.subtitle }}</p>
          </div>
        </router-link>
      </div>

    </div>
  </section>
</template>

<script setup>
const categories = [
  {
    name: 'Ceramics & Pottery',
    subtitle: 'Wheel-thrown mugs, planters & dining ware',
    count: '42 Items',
    image: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=600&q=80'
  },
  {
    name: 'Woven Goods & Bags',
    subtitle: 'Indigenous weaves, abaca baskets & totes',
    count: '38 Items',
    image: 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=600&q=80'
  },
  {
    name: 'Artisan Jewelry',
    subtitle: 'Beaded necklaces, brass rings & stones',
    count: '56 Items',
    image: 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=600&q=80'
  },
  {
    name: 'Home Decor & Candles',
    subtitle: 'Soy wax candles, woodcrafts & accents',
    count: '29 Items',
    image: 'https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=600&q=80'
  }
];
</script>
