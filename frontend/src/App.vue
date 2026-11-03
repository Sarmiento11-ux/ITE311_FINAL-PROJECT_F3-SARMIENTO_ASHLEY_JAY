<template>
  <div id="craftmarket-app">
    <Navbar />
    <div id="main-content">
      <router-view />
    </div>
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
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
