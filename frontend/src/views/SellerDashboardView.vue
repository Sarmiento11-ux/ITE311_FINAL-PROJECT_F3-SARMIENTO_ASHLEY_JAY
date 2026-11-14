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



                  <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px; flex-wrap: wrap;">



                    <span class="status-pill" :class="sellerVerificationStatus === 'approved' ? 'status-delivered' : (sellerVerificationStatus === 'rejected' ? 'status-cancelled' : 'status-pending')">



                      {{ sellerVerificationStatus }}



                    </span>



                    <button



                      v-if="sellerVerificationStatus !== 'approved'"



                      type="button"



                      class="btn btn-secondary btn-sm"



                      style="font-size: 0.72rem; padding: 2px 8px;"



                      @click="showVerificationModal = true"



                    >



                      {{ sellerVerificationStatus === 'rejected' ? 'Re-submit for Review' : 'Submit for Review' }}



                    </button>



                  </div>



                </div>



              </div>



            </div>







            <!-- Revenue Analytics -->
            <div class="dashboard-panel-card analytics-panel">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Revenue Analytics</h3>
                  <p class="panel-subheading">Six-month sales trend, top crafts, and month-over-month growth.</p>
                </div>
                <span
                  class="status-pill"
                  :class="revenueGrowth >= 0 ? 'status-delivered' : 'status-cancelled'"
                >
                  {{ revenueGrowth >= 0 ? '▲' : '▼' }} {{ Math.abs(revenueGrowth).toFixed(1) }}% vs last month
                </span>
              </div>
              <div class="analytics-summary-grid">
                <div class="analytics-summary-item">
                  <span>This Month</span>
                  <strong>₱{{ Number(liveStats?.revenue_this_month ?? 0).toFixed(2) }}</strong>
                </div>
                <div class="analytics-summary-item">
                  <span>Last Month</span>
                  <strong>₱{{ Number(liveStats?.revenue_last_month ?? 0).toFixed(2) }}</strong>
                </div>
                <div class="analytics-summary-item">
                  <span>Average Order Value</span>
                  <strong>₱{{ averageOrderValue.toFixed(2) }}</strong>
                </div>
                <div class="analytics-summary-item">
                  <span>Commission Rate</span>
                  <strong>{{ Number(liveStats?.commission_rate ?? (subscriptionData?.is_pro ? 3 : 8)).toFixed(0) }}%</strong>
                </div>
              </div>
              <div class="analytics-two-col">
                <div>
                  <h4 class="analytics-subheading">Monthly Sales Trend</h4>
                  <div class="sales-trend-chart" role="img" aria-label="Bar chart of the last six months of artisan sales">
                    <div v-for="month in salesTrend" :key="month.month" class="trend-column">
                      <div class="trend-bar-wrap">
                        <div
                          class="trend-bar"
                          :style="{ height: trendBarHeight(month.sales) + '%' }"
                          :title="`${month.label}: ₱${Number(month.sales).toFixed(2)} (${month.orders} orders)`"
                        ></div>
                      </div>
                      <span class="trend-value">₱{{ formatCompact(month.sales) }}</span>
                      <span class="trend-label">{{ month.label }}</span>
                    </div>
                    <p v-if="!salesTrend.length" class="analytics-empty">No sales recorded yet.</p>
                  </div>
                </div>
                <div>
                  <h4 class="analytics-subheading">Top-Selling Crafts</h4>
                  <ul class="top-products-list">
                    <li v-for="(item, index) in topProducts" :key="item.product_id">
                      <span class="top-rank">{{ index + 1 }}</span>
                      <span class="top-product-name">{{ item.name }}</span>
                      <span class="top-product-meta">{{ item.quantity }} sold</span>
                      <strong class="top-product-revenue">₱{{ Number(item.revenue).toFixed(2) }}</strong>
                    </li>
                    <li v-if="!topProducts.length" class="analytics-empty">No completed craft sales yet.</li>
                  </ul>
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
                :is-pro="subscriptionData?.is_pro || ['pro', 'premium'].includes(subscriptionData?.plan)"
                @edit="openEditModal" 
                @delete="deleteProduct" 
                @add-product="openAddModal"
                @toggle-featured="handleToggleFeatured"
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
                :is-pro="subscriptionData?.is_pro || ['pro', 'premium'].includes(subscriptionData?.plan)"
                @edit="openEditModal" 
                @delete="deleteProduct" 
                @add-product="openAddModal"
                @toggle-featured="handleToggleFeatured"
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
                <button 
                  v-if="orders && orders.length > 0"
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  style="display: inline-flex; align-items: center; gap: 6px;"
                  @click="exportOrdersToCsv"
                >
                  <span>📥 Export Sales (CSV)</span>
                </button>
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
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                  <button 
                    v-if="payoutsData?.requests && payoutsData.requests.length > 0"
                    type="button" 
                    class="btn btn-secondary btn-sm"
                    style="display: inline-flex; align-items: center; gap: 6px;"
                    @click="exportPayoutsToCsv"
                  >
                    <span>📥 Export Payouts (CSV)</span>
                  </button>
                  <span class="status-pill status-published">Escrow Protected</span>
                </div>
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
                  <h3 class="panel-heading">Artisan Membership & Tiered Subscription</h3>
                  <p class="panel-subheading">Choose the right tier to scale your craft workshop with lower commission and premium marketplace tools.</p>
                </div>
                <span class="status-pill" :class="subscriptionData?.is_pro ? 'status-delivered' : (subscriptionData?.plan === 'basic' ? 'status-confirmed' : 'status-transit')">
                  {{ subscriptionData?.is_pro ? '⭐ Premium Pro Active' : (subscriptionData?.plan === 'basic' ? '✓ Basic Maker Active' : 'Free Artisan Starter') }}
                </span>
              </div>

              <!-- Billing Cycle Toggle -->
              <div style="display: flex; justify-content: center; align-items: center; gap: 14px; margin: 24px 0 16px 0; background: #FAF8F5; border: 1px solid #E8DFD5; border-radius: 12px; padding: 12px 20px;">
                <span style="font-size: 0.92rem; font-weight: 600; cursor: pointer;" :style="{ color: selectedBillingCycle === 'monthly' ? '#2F4533' : '#7A6F68' }" @click="selectedBillingCycle = 'monthly'">
                  Monthly Billing
                </span>
                <button 
                  type="button" 
                  @click="selectedBillingCycle = selectedBillingCycle === 'monthly' ? 'annual' : 'monthly'"
                  style="background: #2F4533; border: none; border-radius: 20px; width: 48px; height: 26px; padding: 2px; cursor: pointer; display: flex; align-items: center; transition: all 0.2s;"
                  aria-label="Toggle Billing Cycle"
                >
                  <span 
                    style="display: block; width: 22px; height: 22px; border-radius: 50%; background: #FFF; transition: transform 0.2s;"
                    :style="{ transform: selectedBillingCycle === 'annual' ? 'translateX(22px)' : 'translateX(0)' }"
                  ></span>
                </button>
                <span style="font-size: 0.92rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;" :style="{ color: selectedBillingCycle === 'annual' ? '#2F4533' : '#7A6F68' }" @click="selectedBillingCycle = 'annual'">
                  Annual Billing
                  <span style="background: #E8F5E9; color: #2E7D32; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: 700;">Save 17% (2 Months Free)</span>
                </span>
              </div>

              <!-- Plan Comparison Grid (3 Tiers: Free, Basic, Premium) -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 16px;">
                
                <!-- Tier 1: Free Artisan Starter -->
                <div style="border: 1px solid #E8DFD5; border-radius: 12px; padding: 24px; background: #FAF8F5; display: flex; flex-direction: column; justify-content: space-between;">
                  <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #7A6F68; font-weight: 700;">STARTER</span>
                    <h4 style="font-size: 1.3rem; margin: 8px 0; color: #2F4533;">Free Artisan Starter</h4>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #2D2420; margin-bottom: 4px;">₱0</div>
                    <div style="font-size: 0.82rem; color: #7A6F68; margin-bottom: 14px;">Forever Free • No credit card required</div>
                    <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.6; margin-bottom: 20px;">Ideal for emerging crafts makers starting to showcase handmade items online.</p>
                    
                    <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; font-size: 0.86rem; color: #423B36; line-height: 2;">
                      <li>✓ <strong>8%</strong> Marketplace Commission</li>
                      <li>✓ Up to <strong>5 Active</strong> Craft Listings</li>
                      <li>✓ Standard Marketplace Search Placement</li>
                      <li>✓ Escrow Payouts upon Order Delivery</li>
                      <li>✓ Standard Email Support</li>
                    </ul>
                  </div>

                  <div>
                    <div v-if="!subscriptionData?.is_pro && subscriptionData?.plan !== 'basic'" style="padding: 10px; text-align: center; font-size: 0.85rem; color: #7A6F68; background: #EFE8DE; border-radius: 6px; font-weight: 600;">
                      ✓ Current Active Plan
                    </div>
                    <button 
                      v-else
                      type="button" 
                      class="btn btn-secondary" 
                      style="width: 100%; justify-content: center;"
                      @click="handleCancelSubscription"
                      :disabled="isSubmittingSubscription"
                    >
                      {{ isSubmittingSubscription ? 'Switching...' : 'Switch to Free Tier' }}
                    </button>
                  </div>
                </div>

                <!-- Tier 2: Basic Artisan Maker -->
                <div style="border: 2px solid #5B8C51; border-radius: 12px; padding: 24px; background: #FFF; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                  <span style="position: absolute; top: -12px; right: 20px; background: #5B8C51; color: #FFF; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; text-transform: uppercase;">ARTISAN MAKER</span>
                  <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #5B8C51; font-weight: 700;">GROWTH</span>
                    <h4 style="font-size: 1.3rem; margin: 8px 0; color: #2D2420;">Basic Artisan Maker</h4>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #5B8C51; margin-bottom: 4px;">
                      {{ selectedBillingCycle === 'annual' ? '₱1,490' : '₱149' }}
                      <span style="font-size: 0.85rem; font-weight: 400; color: #7A6F68;">/ {{ selectedBillingCycle === 'annual' ? 'year' : 'month' }}</span>
                    </div>
                    <div style="font-size: 0.82rem; color: #2E7D32; font-weight: 600; margin-bottom: 14px;">
                      {{ selectedBillingCycle === 'annual' ? '₱124/mo equivalent • Save ₱298/yr' : 'Billed monthly • Cancel anytime' }}
                    </div>
                    <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.6; margin-bottom: 20px;">Designed for growing handmade crafters ready to expand their catalog and reduce fees.</p>

                    <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; font-size: 0.86rem; color: #423B36; line-height: 2;">
                      <li>✓ <strong>5%</strong> Reduced Commission (Save 3% on every sale!)</li>
                      <li>✓ Up to <strong>25 Active</strong> Craft Listings</li>
                      <li>✓ Verified Artisan Maker Storefront Badge</li>
                      <li>✓ Priority Catalog Search Indexing</li>
                      <li>✓ Flexible Monthly & Annual Billing</li>
                    </ul>
                  </div>

                  <div>
                    <div v-if="subscriptionData?.plan === 'basic' && !subscriptionData?.is_pro" style="padding: 10px; text-align: center; font-size: 0.85rem; color: #2E7D32; background: #E8F0EA; border-radius: 6px; font-weight: 700;">
                      ✓ Basic Maker Active
                    </div>
                    <button 
                      v-else
                      type="button" 
                      class="btn btn-primary" 
                      style="width: 100%; justify-content: center; background: #5B8C51; border-color: #5B8C51;"
                      @click="handleUpgradeSubscription('basic', selectedBillingCycle)"
                      :disabled="isSubmittingSubscription"
                    >
                      {{ isSubmittingSubscription ? 'Processing...' : (subscriptionData?.is_pro ? 'Switch to Basic Maker' : `Upgrade to Basic Maker (${selectedBillingCycle === 'annual' ? '₱1,490/yr' : '₱149/mo'})`) }}
                    </button>
                  </div>
                </div>

                <!-- Tier 3: Premium Artisan Pro -->
                <div style="border: 2px solid #C25E3E; border-radius: 12px; padding: 24px; background: #FFF; box-shadow: 0 8px 24px rgba(194, 94, 62, 0.08); display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                  <span style="position: absolute; top: -12px; right: 20px; background: #C25E3E; color: #FFF; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 12px; text-transform: uppercase;">RECOMMENDED</span>
                  <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #C25E3E; font-weight: 700;">PRO SCALE</span>
                    <h4 style="font-size: 1.3rem; margin: 8px 0; color: #2D2420;">Premium Artisan Pro ⭐</h4>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #C25E3E; margin-bottom: 4px;">
                      {{ selectedBillingCycle === 'annual' ? '₱2,990' : '₱299' }}
                      <span style="font-size: 0.85rem; font-weight: 400; color: #7A6F68;">/ {{ selectedBillingCycle === 'annual' ? 'year' : 'month' }}</span>
                    </div>
                    <div style="font-size: 0.82rem; color: #C25E3E; font-weight: 600; margin-bottom: 14px;">
                      {{ selectedBillingCycle === 'annual' ? '₱249/mo equivalent • Save ₱598/yr' : 'Billed monthly • Cancel anytime' }}
                    </div>
                    <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.6; margin-bottom: 20px;">For established master artisans who want maximum payouts, unlimited listings, and priority exposure.</p>

                    <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; font-size: 0.86rem; color: #423B36; line-height: 2;">
                      <li>✓ <strong>3%</strong> Ultra-Low Commission (Keep 97% Payout!)</li>
                      <li>✓ <strong>Unlimited</strong> Craft Listings & Workshops</li>
                      <li>✓ ⭐ Featured Placement on Marketplace Homepage</li>
                      <li>✓ ⭐ Pro Artisan Badge on Storefront & Products</li>
                      <li>✓ Real-Time Analytics & Escrow Payout Priority</li>
                    </ul>
                  </div>

                  <div>
                    <div v-if="subscriptionData?.is_pro" style="padding: 10px; text-align: center; font-size: 0.85rem; color: #2E7D32; background: #E8F0EA; border-radius: 6px; font-weight: 700;">
                      ⭐ Premium Pro Active
                    </div>
                    <button 
                      v-else
                      type="button" 
                      class="btn btn-primary" 
                      style="width: 100%; justify-content: center; background: #C25E3E; border-color: #C25E3E;"
                      @click="handleUpgradeSubscription('pro', selectedBillingCycle)"
                      :disabled="isSubmittingSubscription"
                    >
                      {{ isSubmittingSubscription ? 'Processing...' : `Upgrade to Artisan Pro (${selectedBillingCycle === 'annual' ? '₱2,990/yr' : '₱299/mo'})` }}
                    </button>
                  </div>
                </div>

              </div>







              </div>







            </div>







        </section>







      </div>







    </div>







    <!-- Global In-App Toast -->

    <div v-if="toastMsg" style="position: fixed; top: 24px; right: 24px; z-index: 1100; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.15); font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;" :style="toastIsError ? { background: '#FDF2F0', color: '#C25E3E', border: '1px solid #F3C5BA' } : { background: '#E8F5E9', color: '#2E7D32', border: '1px solid #C8E6C9' }">

      <span>{{ toastIsError ? '⚠️' : '✓' }}</span>

      <span>{{ toastMsg }}</span>

    </div>



    <!-- Global In-App Confirm Dialog -->

    <div v-if="confirmModal.show" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1090; padding: 16px;">

      <div style="background: white; border-radius: 12px; width: 100%; max-width: 440px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.18);">

        <h3 style="font-size: 1.15rem; font-weight: 700; color: #2C221E; margin-bottom: 8px;">{{ confirmModal.title || 'Please Confirm' }}</h3>

        <p style="font-size: 0.88rem; color: #5C524B; line-height: 1.5; margin-bottom: 20px;">{{ confirmModal.message }}</p>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">

          <button type="button" class="btn btn-secondary btn-sm" @click="confirmModal.onCancel">

            {{ confirmModal.cancelText || 'Cancel' }}

          </button>

          <button type="button" class="btn btn-primary btn-sm" @click="confirmModal.onConfirm">

            {{ confirmModal.confirmText || 'Confirm' }}

          </button>

        </div>

      </div>

    </div>



    <!-- Seller Verification Modal Dialog -->



    <div v-if="showVerificationModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; padding: 16px;">



      <div style="background: white; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">



        <h3 style="font-size: 1.2rem; font-weight: 700; color: #2C221E; margin-bottom: 8px;">Artisan Storefront Verification</h3>



        <p style="font-size: 0.85rem; color: #7A6F68; margin-bottom: 16px;">



          Submit your workshop credentials, DTI registration, or craft background for administrator review to earn the Verified Local Artisan Maker badge.



        </p>







        <div v-if="verificationFeedback" class="status-pill" style="display: block; margin-bottom: 12px; padding: 8px 12px;" :class="verificationIsError ? 'status-cancelled' : 'status-delivered'">



          {{ verificationFeedback }}



        </div>







        <div style="margin-bottom: 16px;">



          <label style="font-size: 0.82rem; font-weight: 600; color: #2C221E; display: block; margin-bottom: 6px;">Credentials / Verification Notes</label>



          <textarea



            v-model="verificationNotes"



            rows="4"



            style="width: 100%; border: 1px solid #D6C8B8; border-radius: 6px; padding: 8px 12px; font-family: inherit; font-size: 0.88rem; box-sizing: border-box;"



            placeholder="e.g. DTI Certificate #12345, Provincial Guild Member, or website/portfolio links"



          ></textarea>



        </div>







        <div style="display: flex; justify-content: flex-end; gap: 10px;">



          <button type="button" class="btn btn-secondary btn-sm" @click="showVerificationModal = false" :disabled="isSubmittingVerification">



            Cancel



          </button>



          <button type="button" class="btn btn-primary btn-sm" @click="submitVerificationRequest" :disabled="isSubmittingVerification">



            {{ isSubmittingVerification ? 'Submitting...' : 'Send for Review' }}



          </button>



        </div>



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
import { productService } from '../services/productService.js';



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

// ── In-App Toast & Confirmation Modal State ──
const toastMsg = ref('');
const toastIsError = ref(false);
const showToast = (msg, isError = false) => {
  toastMsg.value = msg;
  toastIsError.value = isError;
  setTimeout(() => {
    toastMsg.value = '';
  }, 4000);
};

const confirmModal = reactive({
  show: false,
  title: '',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  onConfirm: () => {},
  onCancel: () => { confirmModal.show = false; },
});

const askConfirm = (title, message, confirmText = 'Confirm') => {
  return new Promise((resolve) => {
    confirmModal.title = title;
    confirmModal.message = message;
    confirmModal.confirmText = confirmText;
    confirmModal.show = true;
    confirmModal.onConfirm = () => {
      confirmModal.show = false;
      resolve(true);
    };
    confirmModal.onCancel = () => {
      confirmModal.show = false;
      resolve(false);
    };
  });
};

// ── Shop Profile & Bio State ──
const shopProfileFeedback = ref('');
const shopProfileIsError = ref(false);
const isSavingShopProfile = ref(false);

const shopProfileForm = reactive({
  name: '',
  shopName: '',
  phone: '',
  address: '',
  bio: '',
});

const initShopProfileForm = () => {
  const u = auth.user.value;
  if (u) {
    shopProfileForm.name = u.name || u.fullName || '';
    shopProfileForm.shopName = u.seller_profile?.shop_name || u.sellerProfile?.shop_name || u.shopName || '';
    shopProfileForm.phone = u.phone || u.contact_number || '';
    shopProfileForm.address = u.address || '';
    shopProfileForm.bio = u.seller_profile?.shop_description || u.sellerProfile?.shop_description || u.bio || '';
  }
};

const handleSaveShopProfile = async () => {
  isSavingShopProfile.value = true;
  shopProfileFeedback.value = '';
  shopProfileIsError.value = false;
  try {
    await auth.updateProfile({
      name: shopProfileForm.name,
      fullName: shopProfileForm.name,
      shopName: shopProfileForm.shopName,
      phone: shopProfileForm.phone,
      address: shopProfileForm.address,
      shop_description: shopProfileForm.bio,
    });
    shopProfileFeedback.value = 'Workshop profile updated successfully!';
    showToast('Workshop profile updated successfully!');
  } catch (err) {
    shopProfileIsError.value = true;
    shopProfileFeedback.value = err.response?.data?.message || err.message || 'Failed to update shop profile.';
    showToast('Failed to update shop profile.', true);
  } finally {
    isSavingShopProfile.value = false;
  }
};



const showVerificationModal = ref(false);



const verificationNotes = ref('');



const isSubmittingVerification = ref(false);



const verificationFeedback = ref('');



const verificationIsError = ref(false);







const submitVerificationRequest = async () => {



  isSubmittingVerification.value = true;



  verificationFeedback.value = '';



  verificationIsError.value = false;



  try {



    const res = await sellerService.requestVerification(verificationNotes.value);



    verificationFeedback.value = res.message || 'Verification request submitted!';



    if (seller.value?.seller_profile) {



      seller.value.seller_profile.verification_status = 'pending';



    } else if (seller.value?.sellerProfile) {



      seller.value.sellerProfile.verification_status = 'pending';



    }



    setTimeout(() => {



      showVerificationModal.value = false;



      verificationFeedback.value = '';



    }, 1500);



  } catch (err) {



    verificationIsError.value = true;



    verificationFeedback.value = err.response?.data?.message || err.message || 'Failed to submit verification request.';



  } finally {



    isSubmittingVerification.value = false;



  }



};



const isLoading = ref(true);







const showProductModal = ref(false);



const editingProduct = ref(null);



const isEditMode = ref(false);



const subscriptionData = ref(null);
const isSubmittingSubscription = ref(false);
const selectedBillingCycle = ref('monthly');



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



  await initShopProfileForm();
  loadSellerData();



  if (route.query.payment === 'success') {



    try {



      const vRes = await subscriptionService.verifySession(route.query.session_id);



      if (vRes?.success) {



        showToast(vRes.message || 'Payment confirmed! Welcome to Artisan Pro!');



        await initShopProfileForm();
  loadSellerData();



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







const salesTrend = computed(() => liveStats.value?.sales_trend || []);
const topProducts = computed(() => liveStats.value?.top_products || []);
const revenueGrowth = computed(() => Number(liveStats.value?.revenue_growth_percent ?? 0));
const averageOrderValue = computed(() => Number(liveStats.value?.average_order_value ?? 0));
const maxTrendSales = computed(() =>
  Math.max(...salesTrend.value.map((month) => Number(month.sales) || 0), 1)
);
const trendBarHeight = (sales) => {
  const ratio = (Number(sales) || 0) / maxTrendSales.value;
  return Math.max(4, Math.round(ratio * 100));
};
const formatCompact = (value) => {
  const amount = Number(value) || 0;
  return amount >= 1000 ? `${(amount / 1000).toFixed(1)}k` : amount.toFixed(0);
};

// Product Actions



const openAddModal = async () => {
  const plan = subscriptionData.value?.plan || (subscriptionData.value?.is_pro ? 'pro' : 'free');
  const isPro = ['pro', 'premium'].includes(plan);
  const defaultLimit = plan === 'basic' ? 25 : 5;
  const listingLimit = subscriptionData.value?.listing_limit ?? defaultLimit;
  if (!isPro && products.value.length >= listingLimit) {
    const planName = plan === 'basic' ? 'Basic Artisan Maker' : 'Free Artisan Starter';
    const upgradeTarget = plan === 'basic' ? 'Premium Artisan Pro' : 'Basic Maker or Artisan Pro';
    const wantsUpgrade = await askConfirm(
      "Listing Limit Reached",
      `You have reached the ${listingLimit}-craft limit on your ${planName} plan.\n\nWould you like to upgrade to ${upgradeTarget} to unlock more craft listings?`,
      "Upgrade Plan"
    );
    if (wantsUpgrade) {
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



    showToast('Please sign in to list or modify products in your workshop.', true);



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



    await initShopProfileForm();
  loadSellerData();



    showToast(`Product "${productData.name}" saved successfully!`);



    closeProductModal();



  } catch (err) {



    console.error('API save error:', err);



    showToast(err.response?.data?.message || err.message || 'Error saving product.', true);



  } finally {



    isUploadingProduct.value = false;



  }



};







const deleteProduct = async (productId) => {



  const confirmed = await askConfirm("Delete Craft Listing", "Are you sure you want to remove this craft listing from your workshop?", "Delete Listing");
  if (confirmed) {



    try {



      await sellerService.deleteProduct(productId);



      products.value = products.value.filter(p => p.id !== productId);



      showToast('Product listing removed successfully.');



      await initShopProfileForm();
  loadSellerData();



    } catch (err) {



      console.error('API delete error:', err);



      const errMsg = err.response?.data?.message || err.message || 'Unable to delete product listing. Please try again.';
      showToast(`Failed to delete product: ${errMsg}`, true);
    }
  }
};

const handleToggleFeatured = async (product) => {
  try {
    const res = await productService.toggleFeatured(product.id);
    if (res && res.success) {
      product.is_featured = res.is_featured;
      showToast(res.message || 'Craft spotlight status updated successfully.');
    } else {
      showToast(res?.message || 'Failed to update craft featured status.', true);
    }
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Unable to update craft spotlight status.';
    showToast(msg, true);
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



  const confirmed = await askConfirm("Cancel Payout Request", `Cancel this pending payout request of ₱${Number(payout.amount).toFixed(2)}?`, "Yes, Cancel");
  if (!confirmed) return;







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







const handleUpgradeSubscription = async (targetPlan = 'pro', targetCycle = null) => {
  const cycle = targetCycle || selectedBillingCycle.value;
  const isAnnual = cycle === 'annual';
  const planName = targetPlan === 'basic' ? 'Basic Artisan Maker' : 'Premium Artisan Pro';
  const priceText = targetPlan === 'basic'
    ? (isAnnual ? '₱1,490/year (₱124/mo equivalent • Save 17%)' : '₱149/month')
    : (isAnnual ? '₱2,990/year (₱249/mo equivalent • Save 17%)' : '₱299/month');
  const commissionText = targetPlan === 'basic' ? '5%' : '3%';
  const listingText = targetPlan === 'basic' ? 'up to 25 craft listings' : 'unlimited craft listings';

  const confirmed = await askConfirm(
    `Confirm ${planName} Upgrade`,
    `Upgrade to ${planName} for ${priceText}? You will unlock ${commissionText} platform commission and ${listingText}.`,
    `Upgrade to ${planName}`
  );
  if (!confirmed) return;

  isSubmittingSubscription.value = true;
  try {
    const res = await subscriptionService.upgradeToPro(targetPlan, cycle, 'PayMongo Online');
    if (res && res.checkout_url) {
      window.location.href = res.checkout_url;
      return;
    }
    showToast(res.message || `Upgraded to ${planName} successfully!`);
    await initShopProfileForm();
    loadSellerData();
  } catch (err) {
    showToast(err.response?.data?.message || 'Error processing upgrade.', true);
  } finally {
    isSubmittingSubscription.value = false;
  }
};

const handleCancelSubscription = async () => {
  const confirmed = await askConfirm(
    "Switch to Free Starter Plan",
    "Are you sure you want to switch back to the Free Artisan Starter tier? Your marketplace commission rate will return to 8% and craft listings will be capped at 5.",
    "Switch to Free Tier"
  );
  if (!confirmed) return;

  isSubmittingSubscription.value = true;
  try {
    const res = await subscriptionService.cancelSubscription();
    showToast(res.message || 'Switched back to Free Artisan Starter tier.');
    await initShopProfileForm();
    loadSellerData();
  } catch (err) {
    showToast(err.response?.data?.message || 'Error updating subscription.', true);
  } finally {
    isSubmittingSubscription.value = false;
  }
};

const exportOrdersToCsv = () => {
  if (!orders.value || orders.value.length === 0) {
    showToast('No orders available to export.', true);
    return;
  }
  const headers = [
    'Order ID',
    'Order Number',
    'Date',
    'Customer Name',
    'Product',
    'Quantity',
    'Gross Sale (PHP)',
    'Commission Rate (%)',
    'Platform Commission (PHP)',
    'Seller Payout (PHP)',
    'Escrow Status',
    'Fulfillment Status',
    'Tracking Number'
  ];

  const escapeCsv = (str) => {
    if (str == null) return '""';
    const s = String(str).replace(/"/g, '""');
    return `"${s}"`;
  };

  const rows = orders.value.map(o => [
    escapeCsv(o.id),
    escapeCsv(o.orderNumber || o.id),
    escapeCsv(o.orderDate || o.date),
    escapeCsv(o.customerName || 'Customer'),
    escapeCsv(o.product || o.productName),
    o.quantity || 1,
    Number(o.grossAmount ?? o.totalAmount ?? 0).toFixed(2),
    Number(o.commissionRate ?? 8).toFixed(1),
    Number(o.platformFee ?? 0).toFixed(2),
    Number(o.sellerPayout ?? o.totalAmount ?? 0).toFixed(2),
    escapeCsv(o.payoutStatus || o.escrowStatus || 'Held in Escrow'),
    escapeCsv(o.status || 'pending'),
    escapeCsv(o.trackingNumber || '')
  ].join(','));

  const csvContent = [headers.join(','), ...rows].join('\r\n');
  const filename = `craftmarket_seller_sales_${new Date().toISOString().slice(0, 10)}.csv`;
  const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast('Sales ledger exported successfully!');
};

const exportPayoutsToCsv = () => {
  const reqs = payoutsData.value?.requests || [];
  if (reqs.length === 0) {
    showToast('No payout requests to export.', true);
    return;
  }
  const headers = [
    'Request ID',
    'Amount (PHP)',
    'Status',
    'Payment Method',
    'Account Details',
    'Requested Date',
    'Approved Date',
    'Reference Number'
  ];
  const escapeCsv = (str) => {
    if (str == null) return '""';
    const s = String(str).replace(/"/g, '""');
    return `"${s}"`;
  };
  const rows = reqs.map(r => [
    escapeCsv(r.id),
    Number(r.amount || 0).toFixed(2),
    escapeCsv(r.status),
    escapeCsv(r.payout_method || r.payment_method || 'GCash'),
    escapeCsv(r.account_number || r.account_details || ''),
    escapeCsv(r.requested_at || r.created_at || ''),
    escapeCsv(r.approved_at || r.processed_at || ''),
    escapeCsv(r.reference_number || '')
  ].join(','));
  const csvContent = [headers.join(','), ...rows].join('\r\n');
  const filename = `craftmarket_payouts_ledger_${new Date().toISOString().slice(0, 10)}.csv`;
  const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast('Payouts ledger exported successfully!');
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



/* Revenue analytics */
.analytics-summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-top: 18px;
}
.analytics-summary-item {
  padding: 12px 14px;
  border: 1px solid #E8DFD5;
  border-radius: 10px;
  background: #FAF6F0;
}
.analytics-summary-item span {
  display: block;
  font-size: 0.72rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #7A6F68;
  font-weight: 700;
}
.analytics-summary-item strong {
  display: block;
  margin-top: 4px;
  font-size: 1.2rem;
  color: #2D2420;
}
.analytics-two-col {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
  gap: 22px;
  margin-top: 22px;
}
.analytics-subheading {
  margin: 0 0 12px;
  font-size: 0.9rem;
  color: #2D2420;
}
.sales-trend-chart {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  min-height: 170px;
  padding: 10px 8px 0;
  border-bottom: 1px solid #E8DFD5;
}
.trend-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  min-width: 0;
}
.trend-bar-wrap {
  width: 100%;
  height: 120px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}
.trend-bar {
  width: 70%;
  max-width: 44px;
  border-radius: 6px 6px 2px 2px;
  background: linear-gradient(180deg, #6f9c76 0%, #3E5943 100%);
  transition: height 0.35s ease;
}
.trend-value {
  font-size: 0.68rem;
  font-weight: 700;
  color: #3E5943;
  white-space: nowrap;
}
.trend-label {
  font-size: 0.64rem;
  color: #7A6F68;
  white-space: nowrap;
}
.top-products-list {
  list-style: none;
  margin: 0;
  padding: 0;
}
.top-products-list li {
  display: grid;
  grid-template-columns: 24px minmax(0, 1fr) auto auto;
  align-items: center;
  gap: 8px;
  padding: 10px 0;
  border-bottom: 1px solid #F0E8DF;
  font-size: 0.84rem;
}
.top-rank {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #E8F0EA;
  color: #2F4533;
  font-size: 0.7rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.top-product-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #2D2420;
  font-weight: 600;
}
.top-product-meta {
  color: #7A6F68;
  font-size: 0.72rem;
  white-space: nowrap;
}
.top-product-revenue {
  color: #2E7D32;
  white-space: nowrap;
}
.analytics-empty {
  color: #9E8F85;
  font-size: 0.82rem;
  font-style: italic;
  padding: 12px 0;
}
@media (max-width: 760px) {
  .analytics-two-col {
    grid-template-columns: 1fr;
  }
  .sales-trend-chart {
    gap: 5px;
  }
  .trend-label {
    font-size: 0.58rem;
  }
  .top-products-list li {
    grid-template-columns: 22px minmax(0, 1fr) auto;
  }
  .top-product-meta {
    display: none;
  }
}
</style>



