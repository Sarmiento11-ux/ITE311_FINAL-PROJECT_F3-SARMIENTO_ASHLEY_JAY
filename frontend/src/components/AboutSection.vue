<template>
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
        <span class="section-eyebrow">{{ aboutContent.badge }}</span>
        <h2 class="section-title">{{ aboutContent.title }}</h2>
        <p class="about-lead">{{ aboutContent.leadText }}</p>
        <p v-for="(para, i) in aboutContent.paragraphs" :key="i" class="about-body">
          {{ para }}
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

        <a href="#featured" class="btn btn-secondary">
          Discover Artisan Stories ↗
        </a>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { cmsService } from '../services/cmsService.js';

const aboutContent = ref({
  badge: 'ABOUT CRAFTMARKET',
  title: 'Rooted in community. Built for genuine makers.',
  leadText: 'In an age of fast consumerism and mechanized mass production, CraftMarket was founded to reconnect people with the warmth of real human craft.',
  paragraphs: [
    'We provide micro-sellers, provincial weavers, and independent artisans with the modern digital marketplace tools they need—including order tracking, fair payment security, and direct customer communication—without taking heavy corporate cuts.'
  ],
  isFromCms: false
});

onMounted(async () => {
  const data = await cmsService.getAboutContent();
  if (data) {
    aboutContent.value = data;
  }
});
</script>
