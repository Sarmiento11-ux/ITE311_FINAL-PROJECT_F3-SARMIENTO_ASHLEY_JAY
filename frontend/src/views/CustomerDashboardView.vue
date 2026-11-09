<template>
  <main class="dashboard-page-wrapper">
    <div class="container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">CUSTOMER PORTAL</span>
          <h1>Welcome back, {{ customer.fullName }}!</h1>
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

          <button
            type="button"
            class="dashboard-nav-item"
            :class="{ active: currentTab === 'orders' }"
            @click="currentTab = 'orders'"
          >
            <span>My Orders ({{ orders.length }})</span>
          </button>

          <button
            type="button"
            class="dashboard-nav-item"
            :class="{ active: currentTab === 'wishlist' }"
            @click="currentTab = 'wishlist'"
          >
            <span>My Wishlist ({{ wishlist.totalCount }})</span>
          </button>

          <router-link to="/profile" class="dashboard-nav-item">
            <span>My Profile</span>
          </router-link>

          <router-link to="/products" class="dashboard-nav-item" style="opacity: 0.85;">
            <span>↗ Browse Marketplace</span>
          </router-link>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            @click="handleLogout"
            style="color: #dc2626; margin-top: 14px;"
          >
            <span>Sign Out</span>
          </button>
        </aside>

        <!-- Main Content Area -->
        <section class="dashboard-main-area">
          
          <!-- Metrics Overview Cards -->
          <div class="dashboard-stats-grid">
            <DashboardStatCard 
              title="Total Orders" 
              :value="isLoading ? '…' : totalOrdersCount" 
              icon="orders" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Pending Orders" 
              :value="isLoading ? '…' : pendingOrdersCount" 
              icon="pending" 
              bgClass="amber-bg" 
            />
            <DashboardStatCard 
              title="Completed Orders" 
              :value="isLoading ? '…' : completedOrdersCount" 
              icon="completed" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Total Spent" 
              :value="isLoading ? '…' : `₱${totalSpent.toFixed(2)}`" 
              icon="money" 
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
                <button type="button" @click="currentTab = 'orders'" class="btn btn-secondary btn-sm">
                  View All Orders →
                </button>
              </div>

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading your orders and purchase history…</p>
              </div>
              <CustomerOrdersTable v-else :orders="orders.slice(0, 3)" @order-updated="loadOrders" />
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

          <!-- Tab 3: Wishlist Preview -->
          <div v-else-if="currentTab === 'wishlist'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">My Wishlist</h3>
                  <p class="panel-subheading">Saved handcrafted pieces from local artisans you love.</p>
                </div>
                <router-link to="/wishlist" class="btn btn-secondary btn-sm">View Full Wishlist ↗</router-link>
              </div>

              <div v-if="wishlist.items.length === 0" style="text-align: center; padding: 2.5rem 1rem; color: #7A6F68;">
                <div style="font-size: 2rem; margin-bottom: 10px;">🤍</div>
                <p style="margin-bottom: 14px;">No saved crafts yet. Browse the marketplace and tap the heart icon to save pieces here.</p>
                <router-link to="/products" class="btn btn-primary btn-sm">Browse Marketplace →</router-link>
              </div>

              <div v-else style="display: grid; gap: 10px; margin-top: 8px;">
                <div
                  v-for="item in wishlist.items.slice(0, 6)"
                  :key="item.id || item.productId"
                  style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; background: #FAF6F0; border-radius: 8px; border: 1px solid #E8DFD5;"
                >
                  <img
                    :src="item.image || 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=80&q=80'"
                    :alt="item.title || item.name"
                    style="width: 54px; height: 54px; object-fit: cover; border-radius: 6px; flex-shrink: 0;"
                    @error="$event.target.src='https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=80&q=80'"
                  >
                  <div style="flex: 1; min-width: 0;">
                    <strong style="font-size: 0.9rem; display: block; color: #2C221E; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ item.title || item.name }}</strong>
                    <span style="font-size: 0.8rem; color: #7A6F68;">By {{ item.seller || 'Artisan Maker' }}</span>
                  </div>
                  <strong style="color: #3E5943; white-space: nowrap;">₱{{ Number(item.price || 0).toFixed(2) }}</strong>
                </div>
                <p v-if="wishlist.items.length > 6" style="text-align: center; font-size: 0.85rem; color: #7A6F68; margin-top: 4px;">
                  +{{ wishlist.items.length - 6 }} more saved crafts —
                  <router-link to="/wishlist" style="color: #3E5943; font-weight: 600;">View all in Wishlist ↗</router-link>
                </p>
              </div>
            </div>
          </div>

        </section>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import CustomerOrdersTable from '../components/CustomerOrdersTable.vue';
import { orderService } from '../services/orderService.js';
import { auth } from '../data/auth.js';
import { wishlist } from '../data/wishlist.js';

const router = useRouter();
const { logout } = useAuth();

const handleLogout = async () => {
  await logout();
  router.push('/login');
};

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
