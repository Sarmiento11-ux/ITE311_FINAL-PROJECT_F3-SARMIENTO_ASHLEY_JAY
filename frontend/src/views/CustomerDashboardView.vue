<template>
  <main class="dashboard-page-wrapper">
    <div class="container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">CUSTOMER PORTAL</span>
          <h1>Welcome back, {{ customer.fullName }}! </h1>
          <p>Track your handcrafted orders, browse independent artisans, and manage your delivery details.</p>
        </div>

        <div class="dashboard-user-badge">
          <span> Verified Conscious Shopper</span>
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
            
            <span>Dashboard</span>
          </button>

          <router-link to="/orders" class="dashboard-nav-item">
            <span>My Orders ({{ orders.length }})</span>
          </router-link>

          <router-link to="/wishlist" class="dashboard-nav-item">
            <span>My Wishlist ({{ wishlist.totalCount }})</span>
          </router-link>

          <router-link to="/products" class="dashboard-nav-item">
            
            <span>Browse Products</span>
          </router-link>

          <router-link to="/profile" class="dashboard-nav-item">
            
            <span>My Profile</span>
          </router-link>
        </aside>

        <!-- Main Content Area -->
        <section class="dashboard-main-area">
          
          <!-- Metrics Overview Cards -->
          <div class="dashboard-stats-grid">
            <DashboardStatCard 
              title="Total Orders" 
              :value="isLoading ? '…' : totalOrdersCount" 
              icon="" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Pending Orders" 
              :value="isLoading ? '…' : pendingOrdersCount" 
              icon="" 
              bgClass="amber-bg" 
            />
            <DashboardStatCard 
              title="Completed Orders" 
              :value="isLoading ? '…' : completedOrdersCount" 
              icon="" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Total Spent" 
              :value="isLoading ? '…' : `₱${totalSpent.toFixed(2)}`" 
              icon="" 
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
                  <span v-if="customer.address">{{ customer.address }}</span>
                  <span v-else style="color: #7A6F68; font-style: italic;">
                    No address set.
                    <router-link to="/profile" style="color: #3E5943; font-weight: 600; text-decoration: underline; margin-left: 4px;">
                      Add delivery address ↗
                    </router-link>
                  </span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Member Since:</span>
                  <span>{{ memberSince }}</span>
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
                <router-link to="/orders" class="btn btn-secondary btn-sm">
                  View All Orders →
                </router-link>
              </div>

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading your orders and purchase history…</p>
              </div>
              <CustomerOrdersTable v-else :orders="orders.slice(0, 3)" @order-updated="loadOrders" />
            </div>

            <!-- Browse CTA Banner -->
            <div class="products-cta-banner" style="margin-top: 10px;">
              <div class="banner-text">
                <h3>Looking for more authentic creations?</h3>
                <p>Support independent local artisans, weavers, and craftmakers across the Philippines.</p>
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

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading complete order history…</p>
              </div>
              <CustomerOrdersTable v-else :orders="orders" @order-updated="loadOrders" />
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
import { wishlist } from '../data/wishlist.js';

const currentTab = ref('overview');
const orders = ref([]);
const isLoading = ref(true);

const customer = computed(() => {
  return auth.user.value || {
    id: null,
    fullName: "CraftMarket Member",
    email: "—",
    address: "No shipping address provided"
  };
});

const memberSince = computed(() => {
  const dateStr = customer.value?.created_at;
  if (!dateStr) return '2026';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '2026';
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  } catch {
    return '2026';
  }
});

const loadOrders = async () => {
  isLoading.value = true;
  try {
    const customerId = auth.user.value?.id || null;
    orders.value = await orderService.getCustomerOrders(customerId);
  } catch (err) {
    console.warn('Could not load orders via API, using fallback:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadOrders();
  wishlist.loadWishlist(true);
});

const totalOrdersCount = computed(() => orders.value.length);

const pendingOrdersCount = computed(() => {
  // Counts all active in-progress orders (pending, confirmed, processing, shipped/transit)
  const activeStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'transit'];
  return orders.value.filter(o => {
    const s = (o.status || '').toLowerCase();
    return activeStatuses.some(status => s.includes(status));
  }).length;
});

const completedOrdersCount = computed(() => {
  return orders.value.filter(o => {
    const s = (o.status || '').toLowerCase();
    return s.includes('delivered') || s.includes('completed');
  }).length;
});

const totalSpent = computed(() => {
  return orders.value
    .filter(o => !['cancelled', 'refunded'].includes(o.status?.toLowerCase()))
    .reduce((acc, curr) => acc + curr.totalAmount, 0);
});
</script>

<style scoped>
.dashboard-loading-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #7A6F68;
}
.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #E8DFD5;
  border-top-color: #3E5943;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
