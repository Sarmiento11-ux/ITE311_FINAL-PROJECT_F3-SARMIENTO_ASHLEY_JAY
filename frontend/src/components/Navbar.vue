<template>
  <!-- 1. ANNOUNCEMENT BAR (Powered by WordPress CMS) -->
  <aside class="announcement-bar" aria-label="Marketplace Announcement">
    <div class="container announcement-content">
      <span class="announcement-pill">{{ isFromCms ? 'CMS Notice' : 'Community Notice' }}</span>
      <p class="announcement-text" v-html="announcementText"></p>
      <router-link to="/register?role=seller" class="announcement-link">Sell with us ↗</router-link>
    </div>
  </aside>

  <!-- 2. NAVIGATION HEADER -->
  <header class="site-header">
    <div class="container header-container">
      
      <!-- Brand Logo -->
      <router-link to="/" class="brand-logo" aria-label="CraftMarket Homepage">
        <span class="brand-mark">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
        </span>
        <span class="brand-name">CraftMarket<span class="brand-dot">.</span></span>
      </router-link>

      <!-- Search Bar -->
      <form @submit.prevent="handleSearch" class="header-search">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search handcrafted pottery, woven bags, jewelry..." 
          aria-label="Search marketplace"
        >
      </form>

      <!-- Navigation Links -->
      <nav class="main-navigation" aria-label="Primary Navigation">
        <router-link to="/" class="nav-link" exact-active-class="active">Home</router-link>
        <a href="/#categories" class="nav-link">Categories</a>
        <router-link to="/products" class="nav-link" active-class="active">Shop</router-link>
        <router-link 
          v-if="auth.isAuthenticated && (auth.user.value?.role === 'Administrator' || auth.user.value?.rawRole === 'admin')" 
          to="/dashboard/admin" 
          class="nav-link"
          active-class="active"
        >
          Admin Panel
        </router-link>
        <router-link 
          v-else-if="auth.isAuthenticated && (auth.user.value?.role === 'Seller' || auth.user.value?.rawRole === 'seller')" 
          to="/dashboard/seller" 
          class="nav-link"
          active-class="active"
        >
          Seller Portal
        </router-link>
        <router-link 
          v-else-if="auth.isAuthenticated" 
          to="/dashboard/customer" 
          class="nav-link"
          active-class="active"
        >
          My Orders
        </router-link>
        <a href="/#how-it-works" class="nav-link">How It Works</a>
        <a href="/#about" class="nav-link">Our Story</a>
      </nav>

      <!-- User Actions -->
      <div class="header-actions">
        <router-link 
          v-if="auth.isAuthenticated && (auth.user.value?.role === 'Administrator' || auth.user.value?.rawRole === 'admin')" 
          to="/dashboard/admin" 
          class="btn-seller-link"
          style="background-color: var(--cm-terracotta, #c25e3e); color: #ffffff;"
        >
          <span>Admin Control</span>
        </router-link>

        <router-link 
          v-else-if="!auth.isAuthenticated || (auth.user.value?.rawRole !== 'seller' && auth.user.value?.role !== 'Seller')" 
          to="/register?role=seller" 
          class="btn-seller-link"
        >
          <span>Become a Seller</span>
        </router-link>
        
        <router-link 
          v-else 
          to="/dashboard/seller" 
          class="btn-seller-link"
          style="background-color: var(--cm-forest, #3E5943); color: #ffffff;"
        >
          <span>Artisan Dashboard</span>
        </router-link>

        <!-- Account / Profile Link -->
        <router-link 
          :to="auth.isAuthenticated ? '/profile' : '/login'" 
          class="nav-icon-link" 
          :title="auth.isAuthenticated ? 'View My Profile' : 'User Account / Sign In'"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span class="icon-text">{{ auth.isAuthenticated ? 'Profile' : 'Account' }}</span>
        </router-link>
        
        <!-- Customer Orders Link -->
        <router-link 
          v-if="auth.isAuthenticated && (auth.user.value?.rawRole === 'customer' || auth.user.value?.role === 'Customer')" 
          to="/orders" 
          class="nav-icon-link" 
          title="My Orders"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
          <span class="icon-text">Orders</span>
        </router-link>

        <!-- Wishlist Link -->
        <router-link to="/wishlist" class="nav-icon-link wishlist-pill" title="My Wishlist">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
          <span v-if="wishlist.totalCount > 0" class="wishlist-count">{{ wishlist.totalCount }}</span>
        </router-link>

        <!-- Cart Link -->
        <router-link to="/cart" class="nav-icon-link cart-pill" title="Shopping Cart">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>
          <span class="cart-count">{{ cart.totalCount }}</span>
        </router-link>
      </div>

    </div>

    <!-- Floating Global Toast Notification -->
    <transition name="fade">
      <div v-if="cart.showToast" class="cart-floating-toast">
        {{ cart.toastMessage }}
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { cart } from '../data/cart.js';
import { wishlist } from '../data/wishlist.js';
import { auth } from '../data/auth.js';
import { cmsService } from '../services/cmsService.js';

const router = useRouter();
const searchQuery = ref('');
const announcementText = ref('Free shipping on local artisan orders over ₱1,500 | <strong>Support 120+ Verified Local Makers</strong>');
const isFromCms = ref(false);

onMounted(async () => {
  try {
    const notice = await cmsService.getLatestAnnouncement();
    if (notice && notice.text) {
      announcementText.value = notice.text;
      isFromCms.value = notice.isFromCms;
    }
  } catch (err) {
    console.warn('[Navbar] Could not load CMS announcement:', err);
  }
});

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({
      path: '/products',
      query: { search: searchQuery.value.trim() }
    });
  } else {
    router.push('/products');
  }
};
</script>

<style scoped>
.cart-floating-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background-color: var(--cm-forest, #3E5943);
  color: #ffffff;
  padding: 14px 22px;
  border-radius: 8px;
  font-size: 0.92rem;
  font-weight: 500;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 10px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

.wishlist-pill {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  color: var(--cm-charcoal, #2C221E);
  text-decoration: none;
  transition: color 0.2s ease;
}

.wishlist-pill:hover {
  color: #c25e3e;
}

.wishlist-count {
  position: absolute;
  top: 0;
  right: 0;
  background-color: #c25e3e;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 700;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
</style>
