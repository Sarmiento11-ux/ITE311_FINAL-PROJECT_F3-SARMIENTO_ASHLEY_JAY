<template>
  <div id="craftmarket-app">
    <Navbar v-if="!isAdminView" />
    <div id="main-content" :class="{ 'admin-fullscreen-layout': isAdminView }">
      <router-view />
    </div>
    <Footer v-if="!isAdminView" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
import { authService } from './services/authService.js';

const route = useRoute();
const isAdminView = computed(() => {
  return route.path.startsWith('/dashboard/admin') || route.path.startsWith('/admin') || authService.getRole() === 'admin';
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
