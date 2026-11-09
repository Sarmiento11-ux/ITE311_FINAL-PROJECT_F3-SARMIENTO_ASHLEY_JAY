<template>
  <section class="blog-section" id="artisan-stories">
    <div class="container">

      <div class="section-header">
        <span class="section-eyebrow">ARTISAN STORIES & BLOG</span>
        <h2 class="section-title">Stories Behind the Craft</h2>
        <p class="section-subtitle">
          Meet the makers, learn the techniques, and discover the culture woven into every handmade piece.
        </p>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="blog-loading">
        <div class="loading-grid">
          <div v-for="n in 3" :key="n" class="blog-card skeleton">
            <div class="card-image-skeleton"></div>
            <div class="card-body">
              <div class="skeleton-pill"></div>
              <div class="skeleton-title"></div>
              <div class="skeleton-text"></div>
              <div class="skeleton-text short"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Blog posts grid -->
      <div v-else-if="posts.length > 0" class="blog-grid">
        <article
          v-for="post in posts"
          :key="post.id"
          class="blog-card"
        >
          <div class="card-image-wrap">
            <img
              :src="post.image"
              :alt="post.title"
              class="card-image"
              loading="lazy"
              @error="handleImgError($event)"
            />
            <span class="card-category-pill">{{ post.category }}</span>
          </div>

          <div class="card-body">
            <div class="card-meta">
              <span class="card-date">{{ post.date }}</span>
              <span v-if="post.readTime" class="card-read-time"> {{ post.readTime }}</span>
            </div>
            <h3 class="card-title">{{ post.title }}</h3>
            <p class="card-excerpt">{{ post.excerpt }}</p>
            <button
              type="button"
              class="card-link"
              style="background: none; border: none; cursor: pointer; padding: 0; font: inherit;"
              @click="openStoryModal(post)"
            >
              Read Story
              <span class="card-link-arrow">→</span>
            </button>
          </div>
        </article>
      </div>

      <!-- Error / empty state -->
      <div v-else class="blog-empty">
        <span class="empty-icon"></span>
        <p>Artisan stories are being gathered. Check back soon!</p>
      </div>

      <!-- CTA row -->
      <div class="blog-cta-row">
        <a href="/products" class="btn btn-primary">Browse Handmade Products →</a>
      </div>

    </div>

    <!-- In-App Story Reader Modal -->
    <div v-if="activeStory" class="story-modal-backdrop" @click.self="activeStory = null">
      <div class="story-modal-box">
        <button type="button" class="btn-close-story" @click="activeStory = null">✕</button>
        <div class="story-modal-header">
          <span class="card-category-pill" style="position: static; margin-bottom: 8px; display: inline-block;">{{ activeStory.category }}</span>
          <h2 style="font-size: 1.35rem; color: #2C221E; margin-bottom: 6px;">{{ activeStory.title }}</h2>
          <div style="font-size: 0.82rem; color: #7A6F68; margin-bottom: 14px;">
            <span>{{ activeStory.date }}</span>
            <span v-if="activeStory.author"> • By {{ activeStory.author }}</span>
            <span v-if="activeStory.readTime"> • {{ activeStory.readTime }}</span>
          </div>
        </div>
        <div class="story-modal-body">
          <img :src="activeStory.image" :alt="activeStory.title" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 8px; margin-bottom: 16px;">
          <p style="font-size: 0.95rem; line-height: 1.7; color: #423B36; white-space: pre-line;">{{ activeStory.content || activeStory.excerpt }}</p>
        </div>
        <div class="story-modal-footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; margin-top: 16px; border-top: 1px solid #efe8de;">
          <a v-if="activeStory.isFromCms && activeStory.link && activeStory.link !== '#'" :href="activeStory.link" target="_blank" rel="noopener noreferrer" style="font-size: 0.85rem; color: #3E5943; text-decoration: underline;">
            View on CMS Source ↗
          </a>
          <router-link v-else :to="getPostCategoryRoute(activeStory.category)" class="btn btn-primary btn-sm" @click="activeStory = null">
            Browse {{ activeStory.category }} Crafts →
          </router-link>
          <button type="button" class="btn btn-secondary btn-sm" @click="activeStory = null">Close</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { cmsService } from '../services/cmsService.js';

const loading = ref(true);
const posts = ref([]);
const activeStory = ref(null);

function openStoryModal(post) {
  activeStory.value = post;
}

const fallbackImg = 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80';

function getPostCategoryRoute(category) {
  const map = {
    'Ceramic Arts': 'Ceramics & Pottery',
    'Weaving Heritage': 'Woven Goods & Bags',
    'Sustainable Living': 'Home Decor & Candles'
  };
  const catName = map[category] || category;
  return `/products?category=${encodeURIComponent(catName)}`;
}

function handleImgError(event) {
  event.target.src = fallbackImg;
}

onMounted(async () => {
  try {
    const data = await cmsService.getBlogPosts();
    posts.value = data || [];
  } catch {
    posts.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* ─── Section wrapper ─── */
.blog-section {
  padding: 6rem 0;
  background-color: var(--color-cream, #faf6f1);
}

/* ─── Header ─── */
.section-header {
  text-align: center;
  max-width: 640px;
  margin: 0 auto 3.5rem;
}

.section-eyebrow {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--color-terracotta, #c45c3a);
  text-transform: uppercase;
  margin-bottom: 0.6rem;
}

.section-title {
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  font-weight: 700;
  color: var(--color-dark, #1a1a1a);
  margin-bottom: 0.8rem;
}

.section-subtitle {
  color: var(--color-text-muted, #666);
  line-height: 1.7;
  font-size: 1rem;
}

/* ─── Grid ─── */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

/* ─── Card ─── */
.blog-card {
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
}

.blog-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 28px rgba(0,0,0,0.12);
}

/* ─── Image ─── */
.card-image-wrap {
  position: relative;
  overflow: hidden;
  height: 210px;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.blog-card:hover .card-image {
  transform: scale(1.04);
}

.card-category-pill {
  position: absolute;
  top: 12px;
  left: 12px;
  background: var(--color-terracotta, #c45c3a);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  padding: 0.3em 0.8em;
  border-radius: 50px;
  text-transform: uppercase;
}

/* ─── Card body ─── */
.card-body {
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.78rem;
  color: var(--color-text-muted, #888);
  margin-bottom: 0.6rem;
}

.card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-dark, #1a1a1a);
  line-height: 1.45;
  margin-bottom: 0.5rem;
}

.card-excerpt {
  font-size: 0.9rem;
  color: var(--color-text-muted, #666);
  line-height: 1.6;
  flex: 1;
  margin-bottom: 1rem;

  /* Clamp to 3 lines */
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--color-forest, #3a6b4b);
  text-decoration: none;
  transition: gap 0.2s;
}

.card-link:hover {
  gap: 0.6rem;
  color: var(--color-terracotta, #c45c3a);
}

.card-link-arrow {
  transition: transform 0.2s;
}

.card-link:hover .card-link-arrow {
  transform: translateX(4px);
}

/* ─── CTA row ─── */
.blog-cta-row {
  margin-top: 3rem;
  text-align: center;
}

/* ─── Loading skeletons ─── */
.loading-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.skeleton .card-image-skeleton {
  height: 210px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.skeleton .card-body {
  padding: 1.4rem;
}

.skeleton-pill {
  width: 80px;
  height: 22px;
  border-radius: 50px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  margin-bottom: 0.8rem;
}

.skeleton-title {
  height: 20px;
  border-radius: 6px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  margin-bottom: 0.6rem;
}

.skeleton-text {
  height: 14px;
  border-radius: 6px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  margin-bottom: 0.5rem;
}

.skeleton-text.short { width: 65%; }

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ─── Empty state ─── */
.blog-empty {
  text-align: center;
  padding: 4rem 0;
  color: var(--color-text-muted, #888);
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: 1rem;
}

/* ─── In-App Story Reader Modal ─── */
.story-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 16px;
}
.story-modal-box {
  background: #ffffff;
  border-radius: 16px;
  max-width: 620px;
  width: 100%;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 12px 40px rgba(0,0,0,0.25);
  padding: 24px;
  position: relative;
}
.btn-close-story {
  position: absolute;
  top: 18px;
  right: 18px;
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  color: #7A6F68;
}
.btn-close-story:hover {
  background: #efe8de;
  color: #2C221E;
}
</style>
