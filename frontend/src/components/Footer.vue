<template>
  <footer class="site-footer">
    <div class="container">
      
      <div class="footer-top-grid">
        
        <!-- Brand Column -->
        <div class="footer-col brand-col">
          <router-link to="/" class="brand-logo footer-logo">
            <span class="brand-mark">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
              </svg>
            </span>
            <span class="brand-name">CraftMarket<span class="brand-dot">.</span></span>
          </router-link>
          <p class="footer-tagline">
            The trusted local handmade marketplace. Connecting thoughtful shoppers with passionate artisans across the Philippines.
          </p>
          <div class="footer-badges">
            <span class="footer-pill">Proudly Local</span>
            <span class="footer-pill"> Escrow Protected</span>
          </div>
        </div>

        <!-- Navigation Column 1: Marketplace -->
        <div class="footer-col">
          <h4 class="footer-heading">Marketplace</h4>
          <ul class="footer-links">
            <li><router-link to="/products">All Categories</router-link></li>
            <li><router-link to="/products">Featured Crafts</router-link></li>
            <li><router-link to="/products?category=Ceramics+%26+Pottery">Ceramics & Tableware</router-link></li>
            <li><router-link to="/products?category=Woven+Goods+%26+Bags">Woven Textiles & Bags</router-link></li>
            <li><router-link to="/products?category=Artisan+Jewelry">Handcrafted Jewelry</router-link></li>
          </ul>
        </div>

        <!-- Navigation Column 2: For Artisans & Admin -->
        <div class="footer-col">
          <h4 class="footer-heading">For Artisans</h4>
          <ul class="footer-links">
            <li><router-link to="/login">Seller Central</router-link></li>
            <li><router-link to="/register?role=seller">Open a Shop</router-link></li>
            <li><a href="/#how-it-works">Fee Structure (8%)</a></li>
            <li><a href="/#about">Handmade Standards</a></li>
            <li><router-link to="/login">Administrator Panel</router-link></li>
          </ul>
        </div>

        <!-- Newsletter / Updates Column -->
        <div class="footer-col newsletter-col">
          <h4 class="footer-heading">Craft Newsletter</h4>
          <p class="newsletter-desc">Subscribe to discover new artisan arrivals and weekly maker highlights.</p>
          <form class="newsletter-form" @submit.prevent="handleSubscribe">
            <input type="email" v-model="newsletterEmail" placeholder="Enter your email address" required aria-label="Email address for newsletter">
            <button type="submit" class="btn-newsletter">Subscribe</button>
          </form>
          <p class="privacy-note">Zero spam. Only authentic craft news.</p>
        </div>

      </div>

      <div class="footer-bottom-bar">
        <div class="copyright-info">
          <p>&copy; 2026 CraftMarket Philippines. All rights reserved.</p>
          <p class="student-credit">Student Project Portfolio • Enterprise Web Application Prototype</p>
        </div>
        <div class="footer-legal-links">
          <a href="#privacy">Privacy Policy</a>
          <a href="#terms">Terms of Service</a>
          <a href="#security">Payment Security</a>
          <a href="#sitemap">Sitemap</a>
        </div>
      </div>

    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue';

const newsletterEmail = ref('');

const handleSubscribe = () => {
  if (newsletterEmail.value) {
    alert('Thank you for subscribing to CraftMarket updates!');
    newsletterEmail.value = '';
  }
};
</script>
