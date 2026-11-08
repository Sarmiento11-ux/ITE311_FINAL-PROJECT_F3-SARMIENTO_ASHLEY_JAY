<template>
  <main class="dashboard-page-wrapper">
    <div class="container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">ARTISAN MERCHANT PORTAL</span>
          <h1>Welcome to your Artisan Portal, {{ seller.fullName }}!</h1>
          <p>Manage your handmade craft listings, review customer orders, and track your escrow payouts.</p>
        </div>

        <div class="dashboard-user-badge" style="background-color: #E8F0EA; color: #2F4533;">
          <span>Verified Local Artisan Maker</span>
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
            :class="{ active: currentTab === 'products' }"
            @click="currentTab = 'products'"
          >
            
            <span>My Products ({{ products.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="currentTab = 'orders'"
          >
            
            <span>Manage Orders ({{ orders.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reviews' }"
            @click="currentTab = 'reviews'"
          >
            <span>Customer Reviews ({{ reviews.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'subscription' }"
            @click="currentTab = 'subscription'"
          >
            <span>Artisan Pro Tier ⭐</span>
          </button>

          <router-link to="/profile" class="dashboard-nav-item">
            
            <span>My Profile</span>
          </router-link>
        </aside>

        <!-- Main Content Area -->
        <section class="dashboard-main-area">
          
          <!-- Metrics Overview Cards -->
          <div class="dashboard-stats-grid">
            <DashboardStatCard 
              title="Total Products" 
              :value="isLoading ? '...' : products.length" 
              icon="" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Orders Received" 
              :value="isLoading ? '...' : orders.length" 
              icon="" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Pending Orders" 
              :value="isLoading ? '...' : pendingOrdersCount" 
              icon="" 
              bgClass="amber-bg" 
            />
            <DashboardStatCard 
              title="Completed Orders" 
              :value="isLoading ? '...' : completedOrdersCount" 
              icon="" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Total Revenue" 
              :value="isLoading ? '...' : `₱${totalRevenue.toFixed(2)}`" 
              icon="" 
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
                  <strong>{{ seller.shopName || seller.fullName }}</strong>
                </div>
                                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Workshop Location:</span>
                  <span>{{ seller.address }}</span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Escrow Payout Rate:</span>
                  <span style="color: #2E7D32; font-weight: 600;">{{ subscriptionData?.is_pro ? '97% Direct Maker Payout (Pro Tier)' : '92% Direct Maker Payout (Basic Tier)' }}</span>
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

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading recent customer orders...</p>
              </div>
              <SellerOrdersTable v-else :orders="orders.slice(0, 3)" @order-updated="loadSellerData" />
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

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading active craft inventory...</p>
              </div>
              <SellerProductsTable 
                v-else
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

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading complete product catalog...</p>
              </div>
              <SellerProductsTable 
                v-else
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

              <div v-if="isLoading" class="dashboard-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading order fulfillment list...</p>
              </div>
              <SellerOrdersTable v-else :orders="orders" @order-updated="loadSellerData" />
            </div>
          </div>

          <!-- Tab 4: Customer Reviews (Read-only) -->
          <div v-else-if="currentTab === 'reviews'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Customer Reviews & Ratings</h3>
                  <p class="panel-subheading">View ratings and authentic customer feedback on your craft listings. Sellers cannot modify customer reviews.</p>
                </div>
              </div>

              <div v-if="reviews.length === 0" style="padding: 40px; text-align: center; color: #9e8f85; background: #faf6f0; border-radius: 12px; margin-top: 16px;">
                <p>No customer reviews have been submitted for your products yet.</p>
              </div>

              <div v-else style="margin-top: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                  <thead>
                    <tr style="border-bottom: 2px solid #efe8de; font-size: 0.78rem; color: #7a6a60; text-transform: uppercase;">
                      <th style="padding: 12px 16px;">Product</th>
                      <th style="padding: 12px 16px;">Rating</th>
                      <th style="padding: 12px 16px;">Customer</th>
                      <th style="padding: 12px 16px;">Date</th>
                      <th style="padding: 12px 16px;">Review Comment</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="r in reviews" :key="r.id" style="border-bottom: 1px solid #efe8de; font-size: 0.88rem;">
                      <td style="padding: 14px 16px; font-weight: 600; color: #2d2420;">{{ r.product_name }}</td>
                      <td style="padding: 14px 16px;">
                        <ProductRating :rating="r.rating" :count="0" :show-count="false" size="sm" />
                      </td>
                      <td style="padding: 14px 16px; color: #5a4f48;">{{ r.user_name }}</td>
                      <td style="padding: 14px 16px; color: #9e8f85; font-size: 0.82rem;">{{ r.created_at }}</td>
                      <td style="padding: 14px 16px; color: #4a3f38; max-width: 320px;">{{ r.comment || '—' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        
          <!-- Tab 5: Artisan Subscription & Growth Tier -->
          <div v-else-if="currentTab === 'subscription'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Artisan Membership & Subscription</h3>
                  <p class="panel-subheading">Choose the right tier to scale your craft workshop with lower commission and premium marketplace tools.</p>
                </div>
                <span class="status-pill" :class="subscriptionData?.is_pro ? 'status-delivered' : 'status-transit'">
                  {{ subscriptionData?.is_pro ? '⭐ Artisan Pro Active' : 'Basic Free Tier' }}
                </span>
              </div>

              <!-- Plan Comparison Grid -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 24px;">
                
                <!-- Plan 1: Basic Plan -->
                <div style="border: 1px solid #E8DFD5; border-radius: 12px; padding: 24px; background: #FAF8F5;">
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #7A6F68; font-weight: 700;">STARTER</span>
                  <h4 style="font-size: 1.3rem; margin: 8px 0; color: #2F4533;">Basic Artisan</h4>
                  <div style="font-size: 1.8rem; font-weight: 700; color: #2D2420; margin-bottom: 12px;">₱0 <span style="font-size: 0.85rem; font-weight: 400; color: #7A6F68;">/ month</span></div>
                  <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.6; margin-bottom: 20px;">Ideal for emerging crafts makers starting to showcase handmade items online.</p>
                  
                  <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; font-size: 0.86rem; color: #423B36; line-height: 2;">
                    <li>✓ <strong>8%</strong> Platform Escrow Commission</li>
                    <li>✓ Up to <strong>10 Active</strong> Craft Listings</li>
                    <li>✓ Standard Marketplace Search Placement</li>
                    <li>✓ Standard Community Support</li>
                  </ul>

                  <button 
                    v-if="subscriptionData?.is_pro"
                    type="button" 
                    class="btn btn-secondary" 
                    style="width: 100%; justify-content: center;"
                    @click="handleCancelSubscription"
                    :disabled="isSubmittingSubscription"
                  >
                    {{ isSubmittingSubscription ? 'Switching...' : 'Switch to Basic' }}
                  </button>
                  <div v-else style="padding: 10px; text-align: center; font-size: 0.85rem; color: #7A6F68; background: #EFE8DE; border-radius: 6px; font-weight: 600;">
                    ✓ Current Active Plan
                  </div>
                </div>

                <!-- Plan 2: Artisan Pro -->
                <div style="border: 2px solid #C25E3E; border-radius: 12px; padding: 24px; background: #FFF; box-shadow: 0 8px 24px rgba(194, 94, 62, 0.08); position: relative;">
                  <span style="position: absolute; top: -12px; right: 20px; background: #C25E3E; color: #FFF; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; text-transform: uppercase;">RECOMMENDED</span>
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #C25E3E; font-weight: 700;">PRO SCALE</span>
                  <h4 style="font-size: 1.3rem; margin: 8px 0; color: #2D2420;">Artisan Pro ⭐</h4>
                  <div style="font-size: 1.8rem; font-weight: 700; color: #C25E3E; margin-bottom: 12px;">₱299 <span style="font-size: 0.85rem; font-weight: 400; color: #7A6F68;">/ month</span></div>
                  <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.6; margin-bottom: 20px;">For passionate artisans who want to maximize earnings with minimal commission.</p>

                  <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; font-size: 0.86rem; color: #423B36; line-height: 2;">
                    <li>✓ <strong>3%</strong> Ultra-Low Commission (Keep 97% Payout!)</li>
                    <li>✓ <strong>Unlimited</strong> Craft Listings & Workshops</li>
                    <li>✓ Featured Placement on Marketplace Homepage</li>
                    <li>✓ Real-Time Analytics & Escrow Priority</li>
                  </ul>

                  <button 
                    v-if="!subscriptionData?.is_pro"
                    type="button" 
                    class="btn btn-primary" 
                    style="width: 100%; justify-content: center; background: #C25E3E; border-color: #C25E3E;"
                    @click="handleUpgradeSubscription"
                    :disabled="isSubmittingSubscription"
                  >
                    {{ isSubmittingSubscription ? 'Upgrading...' : 'Upgrade to Artisan Pro (₱299/mo) →' }}
                  </button>
                  <div v-else style="padding: 10px; text-align: center; font-size: 0.85rem; color: #2E7D32; background: #E8F0EA; border-radius: 6px; font-weight: 700;">
                    ⭐ Artisan Pro Active
                  </div>
                </div>

              </div>

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
      :isUploading="isUploadingProduct"
      @save="handleSaveProduct" 
      @close="closeProductModal" 
    />

  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import SellerProductsTable from '../components/SellerProductsTable.vue';
import SellerOrdersTable from '../components/SellerOrdersTable.vue';
import ProductFormModal from '../components/ProductFormModal.vue';
import ProductRating from '../components/ProductRating.vue';
import { sellerService } from '../services/sellerService.js';
import { getSellerReviews } from '../services/reviewService.js';
import { subscriptionService } from '../services/subscriptionService.js';

const route = useRoute();
import { auth } from '../data/auth.js';

const currentTab = ref('overview');
const products = ref([]);
const orders = ref([]);
const liveStats = ref(null);
const reviews = ref([]);
const isLoading = ref(true);

const showProductModal = ref(false);
const editingProduct = ref(null);
const isEditMode = ref(false);
const subscriptionData = ref(null);
const isSubmittingSubscription = ref(false);

const seller = computed(() => {
  return auth.user.value || {
    id: null,
    fullName: "Artisan Maker",
    shopName: "Handcrafted Workshop",
    email: "",
    address: ""
  };
});

const loadSellerData = async () => {
  isLoading.value = true;
  try {
    const sellerId = seller.value?.id || null;
    const [fetchedProducts, fetchedOrders, fetchedStats] = await Promise.all([
      sellerService.getProducts(sellerId),
      sellerService.getOrders(sellerId),
      sellerService.getStats(sellerId)
    ]);
    products.value = fetchedProducts;
    orders.value = fetchedOrders;
    liveStats.value = fetchedStats;

    try {
      const revRes = await getSellerReviews();
      reviews.value = revRes?.data || [];
    } catch {
      reviews.value = [];
    }

    try {
      const subRes = await subscriptionService.getCurrentSubscription();
      subscriptionData.value = subRes?.data || null;
    } catch (subErr) {
      console.warn('Could not load subscription data:', subErr);
      subscriptionData.value = { is_pro: false, commission_rate: 8, listing_limit: 10 };
    }
  } catch (err) {
    console.warn('Could not load seller data from API, using fallback:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await loadSellerData();
  if (route.query.payment === 'success') {
    try {
      const vRes = await subscriptionService.verifySession(route.query.session_id);
      if (vRes?.success) {
        alert(vRes.message || 'Payment confirmed! Welcome to Artisan Pro!');
        await loadSellerData();
      }
    } catch (e) {
      console.warn('Subscription payment verification:', e);
    }
  }
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
  return orders.value
    .filter(o => {
      const s = (o.status || o.rawStatus || '').toLowerCase();
      return !s.includes('cancel') && !s.includes('refund');
    })
    .reduce((acc, curr) => acc + (curr.totalAmount || 0), 0);
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

const isUploadingProduct = ref(false);

const handleSaveProduct = async (productData) => {
  const sellerId = seller.value?.id;
  if (!sellerId) {
    alert('Please sign in to list or modify products in your workshop.');
    return;
  }
  isUploadingProduct.value = true;
  const statusStr = (productData.status || '').toLowerCase();
  const dbStatus = (statusStr.includes('published') || statusStr.includes('active')) 
    ? 'active' 
    : (statusStr.includes('out') ? 'out_of_stock' : 'inactive');

  const payload = {
    seller_id: sellerId,
    category_id: productData.categoryId || 1,
    name: productData.name,
    price: productData.price,
    stock: productData.stock,
    status: dbStatus,
    description: productData.description || 'Authentic handmade creation.',
    material: productData.material || 'Natural sustainable artisan materials',
    dimensions: productData.dimensions || 'Standard handcrafted dimensions',
  };

  try {
    let savedProductId = productData.id;

    if (isEditMode.value) {
      const res = await sellerService.saveProduct({ ...payload, id: productData.id }, true);
      savedProductId = res?.data?.id || productData.id;
    } else {
      const result = await sellerService.saveProduct(payload, false);
      savedProductId = result?.data?.id;
    }

    // Handle image upload if a new file was selected
    if (productData.imageFile && savedProductId) {
      await sellerService.uploadProductImage(savedProductId, productData.imageFile);
    } else if (productData.removeImage && savedProductId) {
      await sellerService.deleteProductImage(savedProductId);
    }

    // Refresh seller products list from API to reflect new image and data
    await loadSellerData();
    alert(`Product "${productData.name}" saved successfully!`);
    closeProductModal();
  } catch (err) {
    console.error('API save error:', err);
    alert(err.response?.data?.message || err.message || 'Error saving product.');
  } finally {
    isUploadingProduct.value = false;
  }
};

const deleteProduct = async (productId) => {
  if (confirm("Are you sure you want to remove this craft listing?")) {
    try {
      await sellerService.deleteProduct(productId);
      products.value = products.value.filter(p => p.id !== productId);
      alert('Product listing removed successfully.');
      await loadSellerData();
    } catch (err) {
      console.error('API delete error:', err);
      const errMsg = err.response?.data?.message || err.message || 'Unable to delete product listing. Please try again.';
      alert(`Failed to delete product: ${errMsg}`);
    }
  }
};

const handleUpgradeSubscription = async () => {
  if (!confirm("Confirm upgrading to Artisan Pro for ₱299/month? You will unlock 3% platform commission and unlimited craft listings.")) {
    return;
  }
  isSubmittingSubscription.value = true;
  try {
    const res = await subscriptionService.upgradeToPro('PayMongo Online');
    if (res && res.checkout_url) {
      window.location.href = res.checkout_url;
      return;
    }
    alert(res.message || 'Upgraded to Artisan Pro successfully!');
    await loadSellerData();
  } catch (err) {
    alert(err.response?.data?.message || 'Error processing upgrade.');
  } finally {
    isSubmittingSubscription.value = false;
  }
};

const handleCancelSubscription = async () => {
  if (!confirm("Are you sure you want to switch back to the Basic Free tier? Your commission rate will return to 8% and listings will be capped at 10.")) {
    return;
  }
  isSubmittingSubscription.value = true;
  try {
    const res = await subscriptionService.cancelSubscription();
    alert(res.message || 'Switched back to Basic Free tier.');
    await loadSellerData();
  } catch (err) {
    alert(err.response?.data?.message || 'Error updating subscription.');
  } finally {
    isSubmittingSubscription.value = false;
  }
};
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
