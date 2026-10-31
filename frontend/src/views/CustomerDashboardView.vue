<template>
  <main class="dashboard-page-wrapper">
    <div class="container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">CUSTOMER PORTAL</span>
          <h1>Welcome back, {{ customer.fullName }}! 🛍️</h1>
          <p>Track your handcrafted orders, browse independent artisans, and manage your delivery details.</p>
        </div>

        <div class="dashboard-user-badge">
          <span>🛡️ Verified Conscious Shopper</span>
        </div>
      </div>

      <!-- Main Layout Grid -->
      <div class="dashboard-layout-grid">
        
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar-nav">
          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'overview' }"
            @click="currentTab = 'overview'"
          >
            <span class="nav-item-icon">📊</span>
            <span>Dashboard</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="currentTab = 'orders'"
          >
            <span class="nav-item-icon">📦</span>
            <span>My Orders ({{ orders.length }})</span>
          </button>

          <router-link to="/products" class="dashboard-nav-item">
            <span class="nav-item-icon">🏺</span>
            <span>Browse Products</span>
          </router-link>

          <router-link to="/profile" class="dashboard-nav-item">
            <span class="nav-item-icon">👤</span>
            <span>My Profile</span>
          </router-link>
        </aside>

        <!-- Main Content Area -->
        <section class="dashboard-main-area">
          
          <!-- Metrics Overview Cards -->
          <div class="dashboard-stats-grid">
            <DashboardStatCard 
              title="Total Orders" 
              :value="totalOrdersCount" 
              icon="🛍️" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Pending Orders" 
              :value="pendingOrdersCount" 
              icon="⏳" 
              bgClass="amber-bg" 
            />
            <DashboardStatCard 
              title="Completed Orders" 
              :value="completedOrdersCount" 
              icon="✓" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Total Spent" 
              :value="`₱${totalSpent.toFixed(2)}`" 
              icon="💳" 
              bgClass="terracotta-bg" 
            />
          </div>

          <!-- Tab 1: Overview & Recent Activity -->
          <div v-if="currentTab === 'overview'">
            <!-- Customer Summary Card -->
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Customer Account Summary</h3>
                  <p class="panel-subheading">Your primary delivery address and marketplace membership status.</p>
                </div>
                <router-link to="/profile" class="btn btn-secondary btn-sm">Edit Profile ↗</router-link>
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; font-size: 0.9rem;">
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Full Name:</span>
                  <strong>{{ customer.fullName }}</strong>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Email Address:</span>
                  <strong>{{ customer.email }}</strong>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Default Shipping Address:</span>
                  <span>{{ customer.address }}</span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Member Since:</span>
                  <span>March 2026</span>
                </div>
              </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Recent Orders</h3>
                  <p class="panel-subheading">Track current order progress with fair-trade escrow protection.</p>
                </div>
                <button type="button" @click="currentTab = 'orders'" class="btn btn-secondary btn-sm">
                  View All Orders →
                </button>
              </div>

              <CustomerOrdersTable :orders="orders.slice(0, 3)" />
            </div>

            <!-- Browse CTA Banner -->
            <div class="products-cta-banner" style="margin-top: 10px;">
              <div class="banner-text">
                <h3>Looking for more authentic creations?</h3>
                <p>Support over 120+ local weavers, potters, and woodworkers across the Philippines.</p>
              </div>
              <router-link to="/products" class="btn btn-primary">
                Browse Marketplace Catalog ↗
              </router-link>
            </div>
          </div>

          <!-- Tab 2: Full Order History -->
          <div v-else-if="currentTab === 'orders'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Complete Order History</h3>
                  <p class="panel-subheading">All handcrafted orders placed through CraftMarket with QR verification details.</p>
                </div>
              </div>

              <CustomerOrdersTable :orders="orders" />
            </div>
          </div>

        </section>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import CustomerOrdersTable from '../components/CustomerOrdersTable.vue';
import { orderService } from '../services/orderService.js';
import { auth } from '../data/auth.js';

const currentTab = ref('overview');
const orders = ref([]);
const isLoading = ref(true);

const customer = computed(() => {
  return auth.user.value || {
    id: 1,
    fullName: "Maria Clara Santos",
    email: "maria@example.com",
    address: "Unit 402 Sunshine Towers, Pasig City, Metro Manila"
  };
});

const loadOrders = async () => {
  isLoading.value = true;
  try {
    const customerId = customer.value.id || 1;
    orders.value = await orderService.getCustomerOrders(customerId);
  } catch (err) {
    console.warn('Could not load orders via API, using fallback:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadOrders();
});

const totalOrdersCount = computed(() => orders.value.length);

const pendingOrdersCount = computed(() => {
  return orders.value.filter(o => o.status.toLowerCase().includes('pending') || o.status.toLowerCase().includes('transit')).length;
});

const completedOrdersCount = computed(() => {
  return orders.value.filter(o => o.status.toLowerCase().includes('delivered')).length;
});

const totalSpent = computed(() => {
  return orders.value.reduce((acc, curr) => acc + curr.totalAmount, 0);
});
</script>
