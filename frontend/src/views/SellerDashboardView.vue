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
          <span>{{ sellerVerificationLabel }}</span>
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
            :class="{ active: currentTab === 'payouts' }"
            @click="currentTab = 'payouts'"
          >
            <span>Payouts & Escrow</span>
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

          <router-link to="/products" class="dashboard-nav-item" style="opacity: 0.85;">
            <span>↗ View Marketplace</span>
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
              title="Total Products" 
              :value="isLoading ? '...' : products.length" 
              icon="products" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Orders Received" 
              :value="isLoading ? '...' : orders.length" 
              icon="orders" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Pending Orders" 
              :value="isLoading ? '...' : pendingOrdersCount" 
              icon="pending" 
              bgClass="amber-bg" 
            />
            <DashboardStatCard 
              title="Completed Orders" 
              :value="isLoading ? '...' : completedOrdersCount" 
              icon="completed" 
              bgClass="forest-bg" 
            />
            <DashboardStatCard 
              title="Total Revenue" 
              :value="isLoading ? '...' : `₱${totalRevenue.toFixed(2)}`" 
              icon="money" 
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
                  <span v-if="seller.address">{{ seller.address }}</span>
                  <span v-else style="color: #9E8F85; font-style: italic;">
                    Not set (<router-link to="/profile" style="color: #C25E3E; text-decoration: underline;">Add location</router-link>)
                  </span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Escrow Payout Rate:</span>
                  <span style="color: #2E7D32; font-weight: 600;">{{ subscriptionData?.is_pro ? '97% Direct Maker Payout (Pro Tier)' : '92% Direct Maker Payout (Basic Tier)' }}</span>
                </div>
                <div>
                  <span style="color: #7A6F68; font-size: 0.8rem; display: block;">Storefront Verification:</span>
                  <span class="status-pill" :class="sellerVerificationStatus === 'approved' ? 'status-delivered' : (sellerVerificationStatus === 'rejected' ? 'status-cancelled' : 'status-pending')">
                    {{ sellerVerificationStatus }}
                  </span>
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
                @add-product="openAddModal"
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
                @add-product="openAddModal"
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

          <!-- Tab 4: Payouts & Escrow -->
          <div v-else-if="currentTab === 'payouts'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Payouts & Escrow Ledger</h3>
                  <p class="panel-subheading">Request a cash-out from escrow funds that have already been released by the platform.</p>
                </div>
                <span class="status-pill status-published">Escrow Protected</span>
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-top: 18px;">
                <div style="padding: 16px; border-radius: 10px; background: #E8F0EA; border: 1px solid #C8DCCB;">
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #2F4533; font-weight: 700;">Available to Request</span>
                  <div style="font-size: 1.45rem; font-weight: 700; color: #2E7D32; margin-top: 4px;">₱{{ Number(payoutsData?.available_balance ?? 0).toFixed(2) }}</div>
                </div>
                <div style="padding: 16px; border-radius: 10px; background: #FAF6F0; border: 1px solid #E8DFD5;">
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #7A6F68; font-weight: 700;">Released Earnings</span>
                  <div style="font-size: 1.45rem; font-weight: 700; color: #2D2420; margin-top: 4px;">₱{{ Number(payoutsData?.released_earnings ?? 0).toFixed(2) }}</div>
                </div>
                <div style="padding: 16px; border-radius: 10px; background: #FFF7ED; border: 1px solid #FED7AA;">
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #9A3412; font-weight: 700;">Reserved / Pending</span>
                  <div style="font-size: 1.45rem; font-weight: 700; color: #C2410C; margin-top: 4px;">₱{{ Number(payoutsData?.reserved_amount ?? 0).toFixed(2) }}</div>
                </div>
                <div style="padding: 16px; border-radius: 10px; background: #EFF6FF; border: 1px solid #BFDBFE;">
                  <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #1E40AF; font-weight: 700;">Paid Out</span>
                  <div style="font-size: 1.45rem; font-weight: 700; color: #1D4ED8; margin-top: 4px;">₱{{ Number(payoutsData?.paid_amount ?? 0).toFixed(2) }}</div>
                </div>
              </div>
            </div>

            <div class="dashboard-panel-card" style="margin-top: 18px;">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Request a Payout</h3>
                  <p class="panel-subheading">Enter the amount you want to withdraw from your available released escrow balance.</p>
                </div>
              </div>

              <form @submit.prevent="submitPayoutRequest" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-top: 12px;">
                <div>
                  <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #5C524B; margin-bottom: 5px;">Amount (₱)</label>
                  <input v-model="payoutForm.amount" type="number" min="1" step="0.01" class="form-input" style="padding: 9px;" placeholder="0.00" required />
                </div>
                <div>
                  <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #5C524B; margin-bottom: 5px;">Payout Method</label>
                  <select v-model="payoutForm.payout_method" class="form-input" style="padding: 9px;">
                    <option value="GCash">GCash</option>
                    <option value="Maya">Maya</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Palawan Express">Palawan Express</option>
                  </select>
                </div>
                <div>
                  <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #5C524B; margin-bottom: 5px;">Account Name</label>
                  <input v-model="payoutForm.account_name" type="text" class="form-input" style="padding: 9px;" placeholder="Full account name" />
                </div>
                <div>
                  <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #5C524B; margin-bottom: 5px;">Account Number</label>
                  <input v-model="payoutForm.account_number" type="text" class="form-input" style="padding: 9px;" placeholder="Mobile or bank account number" />
                </div>
                <div style="grid-column: 1 / -1;">
                  <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #5C524B; margin-bottom: 5px;">Notes (optional)</label>
                  <input v-model="payoutForm.notes" type="text" class="form-input" style="padding: 9px;" placeholder="Any instructions for the admin team" />
                </div>
                <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; align-items: center; gap: 12px; flex-wrap: wrap;">
                  <span v-if="payoutFeedback" style="font-size: 0.84rem;" :style="{ color: payoutFeedbackIsError ? '#C25E3E' : '#2E7D32' }">{{ payoutFeedback }}</span>
                  <button type="submit" class="btn btn-primary btn-sm" :disabled="isSubmittingPayout">
                    {{ isSubmittingPayout ? 'Submitting…' : 'Submit Payout Request' }}
                  </button>
                </div>
              </form>
            </div>

            <div class="dashboard-panel-card" style="margin-top: 18px;">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Payout Request History</h3>
                  <p class="panel-subheading">Track every cash-out request and its settlement status.</p>
                </div>
              </div>

              <div v-if="!payoutsData?.requests?.length" style="padding: 28px; text-align: center; color: #9E8F85; background: #FAF6F0; border-radius: 10px; margin-top: 12px;">
                No payout requests yet. Your released escrow earnings will appear above once an order is delivered.
              </div>

              <div v-else style="overflow-x: auto; margin-top: 14px;">
                <table class="dashboard-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                  <thead>
                    <tr style="border-bottom: 2px solid #EFE8DE; font-size: 0.76rem; color: #7A6A60; text-transform: uppercase;">
                      <th style="padding: 10px 12px;">Requested</th>
                      <th style="padding: 10px 12px;">Amount</th>
                      <th style="padding: 10px 12px;">Method</th>
                      <th style="padding: 10px 12px;">Status</th>
                      <th style="padding: 10px 12px;">Admin Notes</th>
                      <th style="padding: 10px 12px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in payoutsData.requests" :key="p.id" style="border-bottom: 1px solid #EFE8DE; font-size: 0.86rem;">
                      <td style="padding: 12px;">{{ formatPayoutDate(p.requested_at || p.created_at) }}</td>
                      <td style="padding: 12px; font-weight: 700;">₱{{ Number(p.amount).toFixed(2) }}</td>
                      <td style="padding: 12px;">{{ p.payout_method || '—' }}</td>
                      <td style="padding: 12px;">
                        <span class="status-pill" :class="payoutStatusClass(p.status)">{{ p.status }}</span>
                      </td>
                      <td style="padding: 12px; color: #7A6F68;">{{ p.admin_notes || '—' }}</td>
                      <td style="padding: 12px;">
                        <button
                          v-if="p.status === 'pending'"
                          type="button"
                          class="btn-table-action btn-action-view"
                          @click="cancelPayoutRequest(p)"
                        >
                          Cancel
                        </button>
                        <span v-else style="color: #9E8F85;">—</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Tab 5: Customer Reviews (Read-only) -->
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
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import SellerProductsTable from '../components/SellerProductsTable.vue';
import SellerOrdersTable from '../components/SellerOrdersTable.vue';
import ProductFormModal from '../components/ProductFormModal.vue';
import ProductRating from '../components/ProductRating.vue';
import { sellerService } from '../services/sellerService.js';
import { getSellerReviews } from '../services/reviewService.js';
import { subscriptionService } from '../services/subscriptionService.js';

const route = useRoute();
const router = useRouter();
const { logout } = useAuth();
import { auth } from '../data/auth.js';

const handleLogout = async () => {
  await logout();
  router.push('/login');
};

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
const payoutsData = ref(null);
const isSubmittingPayout = ref(false);
const payoutFeedback = ref('');
const payoutFeedbackIsError = ref(false);
const payoutForm = reactive({
  amount: '',
  payout_method: 'GCash',
  account_name: '',
  account_number: '',
  notes: '',
});

const seller = computed(() => {
  return auth.user.value || {
    id: null,
    fullName: "Artisan Maker",
    shopName: "Handcrafted Workshop",
    email: "",
    address: ""
  };
});

const sellerVerificationStatus = computed(() => {
  return seller.value?.seller_profile?.verification_status
    || seller.value?.sellerProfile?.verification_status
    || 'pending';
});

const sellerVerificationLabel = computed(() => {
  if (sellerVerificationStatus.value === 'approved') return 'Verified Local Artisan Maker';
  if (sellerVerificationStatus.value === 'rejected') return 'Storefront Needs Revision';
  return 'Storefront Pending Review';
});

const loadSellerData = async () => {
  isLoading.value = true;
  try {
    const sellerId = seller.value?.id || null;
    const [fetchedProducts, fetchedOrders, fetchedStats, fetchedPayouts] = await Promise.all([
      sellerService.getProducts(sellerId),
      sellerService.getOrders(sellerId),
      sellerService.getStats(sellerId),
      sellerService.getPayouts(sellerId)
    ]);
    products.value = fetchedProducts;
    orders.value = fetchedOrders;
    liveStats.value = fetchedStats;
    payoutsData.value = fetchedPayouts;

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
  return orders.value.filter(o => {
    const s = (o.status || o.rawStatus || '').toLowerCase();
    return s.includes('pending') || s.includes('transit') || s.includes('shipped') || s.includes('confirm') || s.includes('process');
  }).length;
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
  const isPro = !!subscriptionData.value?.is_pro;
  const listingLimit = subscriptionData.value?.listing_limit || 10;
  if (!isPro && products.value.length >= listingLimit) {
    if (confirm(`You have reached the ${listingLimit}-product limit for the Basic Artisan plan.\n\nWould you like to explore Artisan Pro to unlock unlimited craft listings?`)) {
      currentTab.value = 'subscription';
    }
    return;
  }
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

const submitPayoutRequest = async () => {
  payoutFeedback.value = '';
  payoutFeedbackIsError.value = false;

  const amount = Number(payoutForm.amount);
  if (!amount || amount <= 0) {
    payoutFeedback.value = 'Please enter a valid payout amount.';
    payoutFeedbackIsError.value = true;
    return;
  }

  isSubmittingPayout.value = true;
  try {
    const res = await sellerService.requestPayout({
      amount,
      payout_method: payoutForm.payout_method,
      account_name: payoutForm.account_name.trim() || null,
      account_number: payoutForm.account_number.trim() || null,
      notes: payoutForm.notes.trim() || null,
    });

    if (res?.success) {
      payoutsData.value = res.data;
      payoutFeedback.value = res.message || 'Payout request submitted.';
      payoutForm.amount = '';
      payoutForm.notes = '';
    } else {
      payoutFeedback.value = res?.message || 'Could not submit payout request.';
      payoutFeedbackIsError.value = true;
    }
  } catch (err) {
    payoutFeedback.value = err.response?.data?.message || 'Could not submit payout request.';
    payoutFeedbackIsError.value = true;
  } finally {
    isSubmittingPayout.value = false;
  }
};

const cancelPayoutRequest = async (payout) => {
  if (!confirm(`Cancel this pending payout request of ₱${Number(payout.amount).toFixed(2)}?`)) return;

  try {
    const res = await sellerService.cancelPayout(payout.id);
    if (res?.success) {
      payoutsData.value = res.data;
      payoutFeedback.value = res.message || 'Payout request cancelled.';
      payoutFeedbackIsError.value = false;
    }
  } catch (err) {
    payoutFeedback.value = err.response?.data?.message || 'Could not cancel payout request.';
    payoutFeedbackIsError.value = true;
  }
};

const payoutStatusClass = (status) => {
  if (status === 'paid') return 'status-delivered';
  if (status === 'approved') return 'status-published';
  if (status === 'rejected') return 'status-cancelled';
  return 'status-pending';
};

const formatPayoutDate = (value) => {
  if (!value) return '—';
  return new Date(value).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
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
