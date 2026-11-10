<template>
  <main>
    <HeroSection />
    <BenefitsSection />
    <CategorySection />
    <FeaturedProducts />
    <AboutSection />
    <BlogSection />
  </main>
</template>

<script setup>
import { nextTick, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import HeroSection from '../components/HeroSection.vue';
import BenefitsSection from '../components/BenefitsSection.vue';
import CategorySection from '../components/CategorySection.vue';
import FeaturedProducts from '../components/FeaturedProducts.vue';
import AboutSection from '../components/AboutSection.vue';
import BlogSection from '../components/BlogSection.vue';

const route = useRoute();
const SECTION_IDS = ['#categories', '#how-it-works', '#about'];

// Direct visits to /#how-it-works or /#about can land before the sections are
// painted, so the browser never scrolls. Re-run the scroll once mounted.
const scrollToHash = async (hash) => {
  if (!SECTION_IDS.includes(hash)) return;
  await nextTick();
  const target = document.querySelector(hash);
  if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

onMounted(() => {
  if (route.hash) scrollToHash(route.hash);
});

watch(() => route.hash, (hash) => {
  if (hash) scrollToHash(hash);
});
</script>
