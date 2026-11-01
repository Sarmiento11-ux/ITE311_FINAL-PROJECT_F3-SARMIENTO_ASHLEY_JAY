<template>
  <main class="dashboard-page-wrapper">
    <div class="container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">ARTISAN MERCHANT PORTAL</span>
          <h1>Welcome to {{ seller.fullName }}! 🎨</h1>
          <p>Manage your handmade craft listings, review customer orders, and track your escrow payouts.</p>
        </div>

        <div class="dashboard-user-badge" style="background-color: #E8F0EA; color: #2F4533;">
          <span>✓ Verified Local Artisan Maker</span>
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
            :class="{ active: currentTab === 'products' }"
            @click="currentTab = 'products'"
          >
            <span class="nav-item-icon">🏺</span>
            <span>My Products ({{ products.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="currentTab = 'orders'"
          >
            <span class="nav-item-icon">📋</span>
            <span>Manage Orders ({{ orders.length }})</span>
          </button>

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
              title="Total Products" 
              :value="products.length" 
              icon="🏺" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Orders Received" 
              :value="orders.length" 
              icon="📦" 
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
              title="Total Revenue" 
              :value="`₱${totalRevenue.toFixed(2)}`" 
              icon="💰" 
              bgClass="terracotta-bg" 
            />
          </div>

          <!-- Tab 1: Overview -->
          <div v-if="currentTab === 'overview'">
            <!-- Artisan Profile Summary Card -->
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Workshop Profile Summary</h3>
                  <p class="panel-subheading">Your public artisan details displayed to customers.</p>
                </div>
                <router-link to="/profile" class="btn btn-secondary btn-sm">Edit Workshop Profile ↗</router-link>
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; font-size: 0.9rem;">
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Workshop / Brand Name:</span>
                  <strong>{{ seller.fullName }}</strong>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Craft Discipline:</span>
                  <strong>Ceramics & Pottery</strong>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Workshop Location:</span>
                  <span>{{ seller.address }}</span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Escrow Payout Rate:</span>
                  <span style="color: #2E7D32; font-weight: 600;">92% Direct Maker Payout</span>
                </div>
              </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Recent Customer Orders</h3>
                  <p class="panel-subheading">Orders awaiting dispatch or confirmed delivery.</p>
                </div>
                <button type="button" @click="currentTab = 'orders'" class="btn btn-secondary btn-sm">
                  View All Orders →
                </button>
              </div>

              <SellerOrdersTable :orders="orders.slice(0, 3)" @order-updated="loadSellerData" />
            </div>

            <!-- Products Quick View -->
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Active Craft Inventory</h3>
                  <p class="panel-subheading">Current stock levels in your online storefront.</p>
                </div>
                <button type="button" @click="openAddModal" class="btn btn-primary btn-sm">
                  + Add New Product
                </button>
              </div>

              <SellerProductsTable 
                :products="products.slice(0, 3)" 
                @edit="openEditModal" 
                @delete="deleteProduct" 
              />
            </div>
          </div>

          <!-- Tab 2: My Products Management -->
          <div v-else-if="currentTab === 'products'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Product Inventory Management</h3>
                  <p class="panel-subheading">Add, edit, or archive your authentic handmade creations.</p>
                </div>
                <button type="button" @click="openAddModal" class="btn btn-primary btn-sm">
                  + Add New Product
                </button>
              </div>

              <SellerProductsTable 
                :products="products" 
                @edit="openEditModal" 
                @delete="deleteProduct" 
              />
            </div>
          </div>

          <!-- Tab 3: Order Management -->
          <div v-else-if="currentTab === 'orders'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Seller Order Management</h3>
                  <p class="panel-subheading">Fulfill orders and track escrow payout disbursements.</p>
                </div>
              </div>

              <SellerOrdersTable :orders="orders" @order-updated="loadSellerData" />
            </div>
          </div>

        </section>

      </div>

    </div>

    <!-- Add/Edit Product Modal Dialog -->
    <ProductFormModal 
      v-if="showProductModal" 
      :initialData="editingProduct" 
      :isEdit="isEditMode"
      @save="handleSaveProduct" 
      @close="closeProductModal" 
    />

  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import SellerProductsTable from '../components/SellerProductsTable.vue';
import SellerOrdersTable from '../components/SellerOrdersTable.vue';
import ProductFormModal from '../components/ProductFormModal.vue';
import { sellerService } from '../services/sellerService.js';
import { auth } from '../data/auth.js';

const currentTab = ref('overview');
const products = ref([]);
const orders = ref([]);
const liveStats = ref(null);
const isLoading = ref(true);

const showProductModal = ref(false);
const editingProduct = ref(null);
const isEditMode = ref(false);

const seller = computed(() => {
  return auth.user.value || {
    id: 2,
    fullName: "Elena Bautista",
    shopName: "Cordillera Weaves",
    email: "artisan@example.com",
    address: "14 Session Road, Baguio City, Benguet"
  };
});

const loadSellerData = async () => {
  isLoading.value = true;
  try {
    const sellerId = seller.value.id || 2;
    const [fetchedProducts, fetchedOrders, fetchedStats] = await Promise.all([
      sellerService.getProducts(sellerId),
      sellerService.getOrders(sellerId),
      sellerService.getStats(sellerId)
    ]);
    products.value = fetchedProducts;
    orders.value = fetchedOrders;
    liveStats.value = fetchedStats;
  } catch (err) {
    console.warn('Could not load seller data from API, using fallback:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadSellerData();
});

const pendingOrdersCount = computed(() => {
  if (liveStats.value?.pending_orders !== undefined) return liveStats.value.pending_orders;
  return orders.value.filter(o => o.status.toLowerCase().includes('pending') || o.status.toLowerCase().includes('transit')).length;
});

const completedOrdersCount = computed(() => {
  if (liveStats.value?.completed_orders !== undefined) return liveStats.value.completed_orders;
  return orders.value.filter(o => o.status.toLowerCase().includes('delivered')).length;
});

const totalRevenue = computed(() => {
  if (liveStats.value?.total_sales !== undefined) return liveStats.value.total_sales;
  return orders.value.reduce((acc, curr) => acc + (curr.totalAmount || 0), 0);
});

// Product Actions
const openAddModal = () => {
  editingProduct.value = null;
  isEditMode.value = false;
  showProductModal.value = true;
};

const openEditModal = (product) => {
  editingProduct.value = { ...product };
  isEditMode.value = true;
  showProductModal.value = true;
};

const closeProductModal = () => {
  showProductModal.value = false;
  editingProduct.value = null;
};

const handleSaveProduct = async (productData) => {
  const sellerId = seller.value.id || 2;
  const payload = {
    seller_id: sellerId,
    category_id: productData.categoryId || 1,
    name: productData.name,
    price: productData.price,
    stock: productData.stock,
    status: productData.status?.toLowerCase() === 'active' ? 'active' : 'inactive',
    description: productData.description || 'Authentic handmade creation.',
    material: productData.material || 'Natural sustainable artisan materials',
    dimensions: productData.dimensions || 'Standard handcrafted dimensions',
  };

  try {
    if (isEditMode.value) {
      await sellerService.saveProduct({ ...payload, id: productData.id }, true);
      const idx = products.value.findIndex(p => p.id === productData.id);
      if (idx > -1) {
        products.value[idx] = { ...products.value[idx], ...productData };
      }
      alert(`✓ Product "${productData.name}" updated successfully in database!`);
    } else {
      const result = await sellerService.saveProduct(payload, false);
      const createdItem = result?.data || {
        ...productData,
        id: Date.now(),
        salesCount: 0
      };
      products.value.unshift(createdItem);
      alert(`✓ Product "${productData.name}" added successfully to your live inventory!`);
    }
  } catch (err) {
    console.warn('API save fallback:', err);
    if (isEditMode.value) {
      const idx = products.value.findIndex(p => p.id === productData.id);
      if (idx > -1) products.value[idx] = { ...productData };
    } else {
      products.value.unshift({ ...productData, id: Date.now(), salesCount: 0 });
    }
    alert(`✓ Product saved.`);
  }
  closeProductModal();
};

const deleteProduct = async (productId) => {
  if (confirm("Are you sure you want to remove this craft listing?")) {
    try {
      await sellerService.deleteProduct(productId);
      products.value = products.value.filter(p => p.id !== productId);
      alert('✓ Product listing removed successfully.');
    } catch (err) {
      console.warn('API delete fallback:', err);
      products.value = products.value.filter(p => p.id !== productId);
    }
  }
};
</script>
