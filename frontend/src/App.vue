<template>
  <div id="craftmarket-app">
    <Navbar v-if="!isDedicatedDashboard" />
    <div id="main-content" :class="{ 'admin-fullscreen-layout': isDedicatedDashboard }">
      <router-view />
    </div>
    <Footer v-if="!isDedicatedDashboard" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
import { authService } from './services/authService.js';

const route = useRoute();
const isDedicatedDashboard = computed(() => {
  return route.path.startsWith('/dashboard/admin') || 
         route.path.startsWith('/admin') ||
         route.path.startsWith('/dashboard/seller');
});
</script>

<style>
/* Global CSS variables & styles are imported in main.js */
#craftmarket-app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>
