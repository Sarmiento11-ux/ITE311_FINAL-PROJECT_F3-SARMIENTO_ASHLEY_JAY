<template>
  <!-- 1. ANNOUNCEMENT BAR (Powered by WordPress CMS) -->
  <aside class="announcement-bar" aria-label="Marketplace Announcement">
    <div class="container announcement-content">
      <span class="announcement-pill">{{ isFromCms ? 'CMS Notice' : 'Community Notice' }}</span>
      <p class="announcement-text" v-html="safeAnnouncementText"></p>
      <router-link v-if="!auth.isAuthenticated" to="/register?role=seller" class="announcement-link">Sell with us ↗</router-link>
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

      <!-- Search Bar (hidden on mobile, shown via drawer) -->
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

      <!-- Desktop Navigation Links -->
      <nav class="main-navigation" aria-label="Primary Navigation">
        <router-link 
            to="/" 
            class="nav-link"
            :class="{ active: route.path === '/' && route.hash === '' }" 
            >
            Home
          </router-link>
        <router-link
            to="/#categories"
            class="nav-link"
            :class="{ active: route.path === '/' && route.hash === '#categories' }"
            @click="handleSectionLink('#categories')"
          >
            Categories
          </router-link>
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
            to="/#how-it-works"
            class="nav-link"
            :class="{ active: route.path === '/' && route.hash === '#how-it-works' }"
            @click="handleSectionLink('#how-it-works')"
          >
            How It Works
          </router-link>
        <router-link
            to="/#about"
            class="nav-link"
            :class="{ active: route.path === '/' && route.hash === '#about' }"
            @click="handleSectionLink('#about')"
          >
            Our Story
          </router-link>
      </nav>

      <!-- User Actions -->
      <div class="header-actions">
        <!-- Primary Auth / Dashboard links -->
        <router-link 
          v-if="!auth.isAuthenticated" 
          to="/login" 
          class="btn-seller-link desktop-only-action"
          style="background-color: var(--cm-forest, #2D4A3E); color: #ffffff;"
        >
          <span>Log In</span>
        </router-link>

        <router-link 
          v-else-if="auth.user.value?.role === 'Administrator' || auth.user.value?.rawRole === 'admin'" 
          to="/dashboard/admin" 
          class="btn-seller-link desktop-only-action"
          style="background-color: var(--cm-terracotta, #c25e3e); color: #ffffff;"
        >
          <span>Admin Control</span>
        </router-link>

        <router-link 
          v-else-if="auth.user.value?.rawRole === 'seller' || auth.user.value?.role === 'Seller'" 
          to="/dashboard/seller" 
          class="btn-seller-link desktop-only-action"
          style="background-color: var(--cm-forest, #3E5943); color: #ffffff;"
        >
          <span>Artisan Dashboard</span>
        </router-link>

        <router-link 
          v-else-if="auth.isAuthenticated" 
          to="/dashboard/customer" 
          class="btn-seller-link desktop-only-action"
          style="background-color: var(--cm-forest, #3E5943); color: #ffffff;"
        >
          <span>Customer Dashboard</span>
        </router-link>

        <!-- Mobile Hamburger Toggle -->
        <button 
          type="button" 
          class="mobile-menu-toggle" 
          @click="toggleMobileMenu" 
          :aria-expanded="isMobileMenuOpen.toString()" 
          aria-label="Toggle navigation menu"
        >
          <svg v-if="!isMobileMenuOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

    </div>

    <!-- Floating Global Toast Notification -->
    <transition name="fade">
      <div v-if="cart.showToast" class="cart-floating-toast">
        {{ cart.toastMessage }}
      </div>
    </transition>

    <!-- Mobile Drawer Overlay -->
    <transition name="drawer-fade">
      <div v-if="isMobileMenuOpen" class="mobile-drawer-backdrop" @click="closeMobileMenu">
        <nav class="mobile-drawer-sheet" @click.stop aria-label="Mobile Navigation">

          <!-- Primary Pages -->
          <router-link to="/" class="mobile-nav-item" exact-active-class="active" @click="closeMobileMenu">Home</router-link>
          <router-link to="/products" class="mobile-nav-item" active-class="active" @click="closeMobileMenu">Shop All Crafts</router-link>
          <router-link to="/#categories" class="mobile-nav-item" :class="{ active: route.path === '/' && route.hash === '#categories' }" @click="closeMobileMenu">Categories</router-link>
          <router-link
            to="/#how-it-works"
            class="mobile-nav-item"
            :class="{ active: route.path === '/' && route.hash === '#how-it-works' }"
            @click="handleSectionLink('#how-it-works')"
          >
            How It Works
          </router-link>
          <router-link
            to="/#about"
            class="mobile-nav-item"
            :class="{ active: route.path === '/' && route.hash === '#about' }"
            @click="handleSectionLink('#about')"
          >
            Our Story
          </router-link>

          <div class="mobile-nav-divider"></div>

          <!-- Role-Specific -->
          <router-link 
            v-if="auth.isAuthenticated && (auth.user.value?.role === 'Administrator' || auth.user.value?.rawRole === 'admin')" 
            to="/dashboard/admin" 
            class="mobile-nav-item highlight-role"
            @click="closeMobileMenu"
          >Admin Control Panel</router-link>
          <router-link 
            v-else-if="auth.isAuthenticated && (auth.user.value?.role === 'Seller' || auth.user.value?.rawRole === 'seller')" 
            to="/dashboard/seller" 
            class="mobile-nav-item highlight-role"
            @click="closeMobileMenu"
          >Artisan Dashboard</router-link>
          <router-link 
            v-else-if="auth.isAuthenticated" 
            to="/dashboard/customer" 
            class="mobile-nav-item highlight-role"
            @click="closeMobileMenu"
          >Customer Dashboard</router-link>

          <div class="mobile-nav-divider"></div>

          <!-- CTA Buttons -->
          <div class="mobile-drawer-cta">
            <router-link 
              v-if="!auth.isAuthenticated" 
              to="/login" 
              class="btn btn-primary"
              style="width: 100%; justify-content: center;"
              @click="closeMobileMenu"
            >Log In →</router-link>
            <router-link 
              v-if="!auth.isAuthenticated" 
              to="/register" 
              class="btn btn-secondary"
              style="width: 100%; justify-content: center;"
              @click="closeMobileMenu"
            >Create Account</router-link>
            <button 
              v-else 
              type="button"
              class="btn btn-secondary"
              style="width: 100%; justify-content: center;"
              @click="() => { handleLogout(); closeMobileMenu(); }"
            >Sign Out</button>
          </div>

        </nav>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import DOMPurify from 'dompurify';
import { cart } from '../data/cart.js';
import { auth } from '../data/auth.js';
import { cmsService } from '../services/cmsService.js';

const router = useRouter();
const isCustomer = computed(() => {
  if (!auth.isAuthenticated || !auth.user.value) return false;
  const role = (auth.user.value.rawRole || auth.user.value.role || '').toLowerCase();
  return role === 'customer';
});

const route = useRoute();
const searchQuery = ref('');
const announcementText = ref('Free shipping on local artisan orders over ₱1,500 | <strong>Support 120+ Verified Local Makers</strong>');
const safeAnnouncementText = computed(() =>
  DOMPurify.sanitize(announcementText.value)
);
const isFromCms = ref(false);
const isMobileMenuOpen = ref(false);

// Close mobile menu on any route or hash change
watch(() => [route.path, route.hash], () => {
  isMobileMenuOpen.value = false;
});

// Same-page hash links are treated by the router as duplicate navigations,
// so they never scroll. Scroll manually in that case.
const handleSectionLink = (hash) => {
  closeMobileMenu();
  if (route.path === '/' && route.hash === hash) {
    nextTick(() => {
      const target = document.querySelector(hash);
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
};

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
    router.push({ path: '/products', query: { search: searchQuery.value.trim() } });
  } else {
    router.push('/products');
  }
  closeMobileMenu();
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const handleLogout = async () => {
  await auth.logout();
  router.push('/login');
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

/* Mobile hamburger button — hidden on desktop */
.mobile-menu-toggle {
  display: none;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  padding: 0;
  background: transparent;
  border: 1px solid var(--border-color, #E8DFD5);
  border-radius: var(--radius-sm, 6px);
  color: var(--cm-charcoal, #2C221E);
  cursor: pointer;
  transition: background-color 0.2s ease;
  flex-shrink: 0;
}

.mobile-menu-toggle:hover {
  background-color: var(--bg-subtle, #F5EDE4);
}

/* Mobile Drawer Backdrop */
.mobile-drawer-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(44, 34, 30, 0.5);
  z-index: 9000;
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
}

/* Drawer Sheet (slides in from right) */
.mobile-drawer-sheet {
  background-color: #ffffff;
  width: 100%;
  max-width: 320px;
  height: 100%;
  overflow-y: auto;
  padding: 20px 0;
  display: flex;
  flex-direction: column;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.12);
}

/* Mobile Nav Items */
.mobile-nav-item {
  display: block;
  padding: 13px 20px;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--cm-charcoal, #2C221E);
  text-decoration: none;
  border-bottom: 1px solid rgba(232, 223, 213, 0.5);
  transition: background-color 0.15s ease, color 0.15s ease;
}

.mobile-nav-item:hover {
  background-color: var(--bg-page, #FBF8F3);
  color: var(--cm-forest, #3E5943);
}

.mobile-nav-item.active {
  color: var(--cm-forest, #3E5943);
  font-weight: 700;
  border-left: 3px solid var(--cm-terracotta, #C25E3E);
  padding-left: 17px;
}

.mobile-nav-item.highlight-role {
  color: var(--cm-forest, #3E5943);
  font-weight: 600;
  background-color: rgba(62, 89, 67, 0.05);
}

.mobile-nav-item.highlight-role:hover {
  background-color: rgba(62, 89, 67, 0.1);
}

.mobile-nav-divider {
  height: 1px;
  background-color: var(--border-color, #E8DFD5);
  margin: 8px 0;
}

.mobile-drawer-cta {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: auto;
}

/* Drawer transition */
.drawer-fade-enter-active,
.drawer-fade-leave-active {
  transition: opacity 0.25s ease;
}

.drawer-fade-enter-active .mobile-drawer-sheet,
.drawer-fade-leave-active .mobile-drawer-sheet {
  transition: transform 0.25s ease;
}

.drawer-fade-enter-from,
.drawer-fade-leave-to {
  opacity: 0;
}

.drawer-fade-enter-from .mobile-drawer-sheet,
.drawer-fade-leave-to .mobile-drawer-sheet {
  transform: translateX(100%);
}

/* ── Responsive: Show hamburger & hide desktop nav at ≤ 1024px ──
   Must match the `.main-navigation { display: none }` breakpoint in
   assets/css/style.css so the header is never left with no navigation. */
@media (max-width: 1024px) {
  .mobile-menu-toggle {
    display: flex;
  }

  .main-navigation {
    display: none;
  }

  .desktop-only-action {
    display: none;
  }
}

@media (max-width: 480px) {
  /* Free up room so the search field and hamburger both fit */
  .brand-name {
    display: none;
  }

  .mobile-drawer-sheet {
    max-width: 100%;
  }
}
</style>
