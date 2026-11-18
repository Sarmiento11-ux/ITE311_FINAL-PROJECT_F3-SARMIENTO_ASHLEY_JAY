<template>
  <main class="dashboard-page-wrapper admin-dashboard-wrapper">
    <div class="container-fluid admin-container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner admin-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">CENTRAL CONTROL PANEL</span>
          <h1>Marketplace Administration</h1>
          <p>Oversee registered artisans, catalog listings, customer transactions, categories, and system reports.</p>
        </div>

        <div class="admin-profile-pill">
          <div class="admin-avatar">{{ adminInitials }}</div>
          <div>
            <strong>{{ currentAdminName }}</strong>
            <span class="admin-role-badge">System Administrator</span>
          </div>
        </div>
      </div>

      <!-- Main Layout: Sidebar + Content Area -->
      <div class="dashboard-layout-grid admin-layout-grid">
        
        <!-- Admin Navigation Sidebar -->
        <aside class="dashboard-sidebar-nav admin-sidebar">
          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'dashboard' }"
            @click="setTab('dashboard')"
          >
            <span>Dashboard</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'users' }"
            @click="setTab('users')"
          >
            <span>Users ({{ stats?.total_users ?? users.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'sellers' }"
            @click="setTab('sellers')"
          >
            <span>Sellers ({{ stats?.total_sellers ?? sellers.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'products' }"
            @click="setTab('products')"
          >
            <span>Products ({{ stats?.total_products ?? products.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'categories' }"
            @click="setTab('categories')"
          >
            <span>Categories ({{ categories.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="setTab('orders')"
          >
            <span>Orders ({{ stats?.total_orders ?? orders.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'payouts' }"
            @click="setTab('payouts')"
          >
            <span>Payouts</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reports' }"
            @click="setTab('reports')"
          >
            <span>Reports</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'subscriptions' }"
            @click="setTab('subscriptions')"
          >
            <span>Subscriptions</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reviews' }"
            @click="setTab('reviews')"
          >
            <span>Reviews</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'cms' }"
            @click="setTab('cms')"
          >
            <span>WordPress CMS</span>
          </button>

          <hr class="sidebar-divider" />



          <button 
            type="button" 
            class="dashboard-nav-item btn-logout" 
            @click="handleLogout"
          >
            
            <span>Sign Out</span>
          </button>
        </aside>

        <!-- Main Content Canvas -->
        <section class="dashboard-main-area admin-main-area">

          <!-- Global Feedback Message -->
          <div v-if="feedbackMsg" class="admin-alert" :class="feedbackClass">
            <span>{{ feedbackMsg }}</span>
            <button type="button" class="btn-close-alert" @click="feedbackMsg = ''"></button>
          </div>

          <!-- Loading State Spinner -->
          <div v-if="isLoading" class="admin-loading-box">
            <div class="admin-spinner"></div>
            <p>Fetching real-time data from Laravel MySQL database…</p>
          </div>

          <!-- TAB 1: DASHBOARD OVERVIEW -->
          <div v-else-if="currentTab === 'dashboard'" class="tab-pane">
            <!-- 6 Key Performance Metric Cards -->
            <div class="admin-metrics-grid">
              <div class="metric-card metric-primary">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Total Users">Total Users</span>
                  <strong class="metric-value" :title="String(stats?.total_users ?? 0)">{{ stats?.total_users ?? 0 }}</strong>
                  <small class="metric-sub" :title="`${stats?.total_customers ?? 0} Customers · ${stats?.total_sellers ?? 0} Sellers`">{{ stats?.total_customers ?? 0 }} Customers · {{ stats?.total_sellers ?? 0 }} Sellers</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Registered Artisans">Registered Artisans</span>
                  <strong class="metric-value" :title="String(stats?.total_sellers ?? 0)">{{ stats?.total_sellers ?? 0 }}</strong>
                  <small class="metric-sub" title="Local craft workshops">Local craft workshops</small>
                </div>
              </div>

              <div class="metric-card metric-terracotta">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Active Customers">Active Customers</span>
                  <strong class="metric-value" :title="String(stats?.total_customers ?? 0)">{{ stats?.total_customers ?? 0 }}</strong>
                  <small class="metric-sub" title="Registered buyers">Registered buyers</small>
                </div>
              </div>

              <div class="metric-card metric-sand">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Catalog Products">Catalog Products</span>
                  <strong class="metric-value" :title="String(stats?.total_products ?? 0)">{{ stats?.total_products ?? 0 }}</strong>
                  <small class="metric-sub" title="Handmade listings">Handmade listings</small>
                </div>
              </div>

              <div class="metric-card metric-gold">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Total Sales Volume">Total Sales Volume</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(stats?.total_sales ?? 0)">₱{{ formatMoney(stats?.total_sales ?? 0) }}</strong>
                  <small class="metric-sub" :title="`${stats?.orders_paid ?? 0} confirmed transactions`">{{ stats?.orders_paid ?? 0 }} confirmed transactions</small>
                </div>
              </div>

              <!-- CraftMarket Monetization Cards -->
              <div class="metric-card metric-forest">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                    <line x1="12" y1="6" x2="12" y2="8"></line>
                    <line x1="12" y1="16" x2="12" y2="18"></line>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Platform Commission">Platform Commission</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(stats?.commission_earned ?? 0)">₱{{ formatMoney(stats?.commission_earned ?? 0) }}</strong>
                  <small class="metric-sub" title="8% Basic · 3% Pro marketplace fee">8% Basic / 3% Pro fee</small>
                </div>
              </div>

              <div class="metric-card metric-primary">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Subscription Revenue">Subscription Revenue</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(stats?.subscription_revenue ?? 0)">₱{{ formatMoney(stats?.subscription_revenue ?? 0) }}</strong>
                  <small class="metric-sub" :title="`${stats?.pro_sellers_count ?? 0} active Pro artisan(s)`">{{ stats?.pro_sellers_count ?? 0 }} active Pro artisan(s)</small>
                </div>
              </div>

              <div class="metric-card metric-gold" style="border-left-color: #2F4533; background: #FAFDF9;">
                <div class="metric-icon" style="background: #E8F5E9; color: #1E3A2F;">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" style="color: #1E3A2F; font-weight: 700;" title="Total Platform Net Earnings">Net Platform Revenue</span>
                  <strong class="metric-value" style="color: #1E3A2F;" :title="'₱' + formatMoney(stats?.total_platform_revenue ?? ((stats?.commission_earned ?? 0) + (stats?.subscription_revenue ?? 0)))">₱{{ formatMoney(stats?.total_platform_revenue ?? ((stats?.commission_earned ?? 0) + (stats?.subscription_revenue ?? 0))) }}</strong>
                  <small class="metric-sub" style="color: #2E7D32;" title="Commission + Subscriptions">Commission + Subscriptions</small>
                </div>
              </div>
            </div>

            <!-- Two-column split: Recent Orders & Recently Registered Users -->
            <div class="admin-dashboard-two-col">
              <!-- Left: Recent Orders -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Recent Customer Orders</h3>
                    <p class="admin-card-sub">Latest customer transactions across all sellers</p>
                  </div>
                  <button type="button" class="btn-text-action" @click="setTab('orders')">View All Orders →</button>
                </div>

                <div class="table-responsive">
                  <table class="admin-table">
                    <thead>
                      <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="order in recentActivity?.recent_orders || []" :key="order.id">
                        <td><strong>{{ order.order_number }}</strong></td>
                        <td>{{ order.user?.name || 'Customer' }}</td>
                        <td><strong>₱{{ formatMoney(order.total_amount) }}</strong></td>
                        <td>
                          <span class="status-badge" :class="getStatusBadgeClass(order.status)">
                            {{ order.status }}
                          </span>
                        </td>
                        <td class="text-muted">{{ formatDate(order.created_at) }}</td>
                      </tr>
                      <tr v-if="!recentActivity?.recent_orders?.length">
                        <td colspan="5" class="empty-cell">No recent orders placed yet.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Right: Recently Registered Users -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Recently Registered Users</h3>
                    <p class="admin-card-sub">New platform members & makers</p>
                  </div>
                  <button type="button" class="btn-text-action" @click="setTab('users')">Manage Users →</button>
                </div>

                <div class="recent-users-list">
                  <div v-for="u in recentActivity?.recent_users || []" :key="u.id" class="recent-user-item">
                    <div class="user-avatar-circle" :class="'role-' + u.role">{{ u.name.charAt(0).toUpperCase() }}</div>
                    <div class="recent-user-info">
                      <strong>{{ u.name }}</strong>
                      <span class="user-email-text">{{ u.email }}</span>
                    </div>
                    <div class="recent-user-meta">
                      <span class="role-badge" :class="'role-' + u.role">{{ u.role }}</span>
                      <small class="text-muted">{{ formatDate(u.created_at) }}</small>
                    </div>
                  </div>
                  <div v-if="!recentActivity?.recent_users?.length" class="empty-cell">
                    No recent user registrations found.
                  </div>
                </div>
              </div>
            </div>

            <!-- Full Width: Latest Marketplace Activities -->
            <div class="admin-card" style="margin-top: 24px;">
              <div class="admin-card-header">
                <div>
                  <h3>Latest Marketplace Activities</h3>
                  <p class="admin-card-sub">Chronological event stream of transactions, registrations, and craft listings</p>
                </div>
              </div>

              <div class="activities-timeline">
                <div v-for="act in recentActivity?.activities || []" :key="act.id" class="activity-timeline-row">
                  <span class="activity-icon-badge" :class="'act-' + act.type">
                    <svg v-if="act.type === 'order'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                      <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <svg v-else-if="act.type === 'user'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                  </span>
                  <div class="activity-details">
                    <strong class="activity-title">{{ act.title }}</strong>
                    <span class="activity-desc">{{ act.desc }}</span>
                  </div>
                  <div class="activity-time">{{ formatDate(act.created_at) }}</div>
                </div>
                <div v-if="!recentActivity?.activities?.length" class="empty-cell">
                  No marketplace activities logged yet.
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: USER MANAGEMENT -->
          <div v-else-if="currentTab === 'users'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>User Account Management</h3>
                  <p class="admin-card-sub">Audit registered customer shoppers, artisan makers, and platform accounts.</p>
                </div>
              </div>

              <!-- Filter Toolbar -->
              <div class="admin-toolbar">
                <div class="search-input-box">
                  
                  <input 
                    type="text" 
                    v-model="userFilter.search" 
                    @input="debounceFetchUsers" 
                    placeholder="Search by name, email, or contact number…" 
                    class="form-control"
                  />
                </div>

                <div class="filter-controls-group">
                  <select v-model="userFilter.role" @change="fetchUsers" class="form-select">
                    <option value="">All Roles</option>
                    <option value="customer">Customer</option>
                    <option value="seller">Seller / Artisan</option>
                    <option value="admin">Administrator</option>
                  </select>

                  <select v-model="userFilter.is_active" @change="fetchUsers" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1">Active Only</option>
                    <option value="0">Deactivated Only</option>
                  </select>
                </div>
              </div>

              <!-- Users Table -->
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Orders</th>
                      <th>Products</th>
                      <th>Status</th>
                      <th>Joined</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="u in users" :key="u.id">
                      <td>
                        <div class="user-cell">
                          <div class="user-avatar-small" :class="'role-' + u.role">{{ u.name.charAt(0).toUpperCase() }}</div>
                          <div>
                            <strong>{{ u.name }}</strong>
                            <small v-if="u.seller_profile?.shop_name" class="d-block text-muted">Shop: {{ u.seller_profile.shop_name }}</small>
                          </div>
                        </div>
                      </td>
                      <td>{{ u.email }}</td>
                      <td>
                        <span class="role-badge" :class="'role-' + u.role">{{ u.role }}</span>
                      </td>
                      <td>{{ u.orders_count ?? 0 }}</td>
                      <td>{{ u.products_count ?? 0 }}</td>
                      <td>
                        <span class="status-badge" :class="u.is_active ? 'badge-active' : 'badge-inactive'">
                          {{ u.is_active ? 'Active' : 'Deactivated' }}
                        </span>
                      </td>
                      <td class="text-muted">{{ formatDate(u.created_at) }}</td>
                      <td>
                        <div class="action-buttons-group">
                          <button 
                            type="button" 
                            class="btn-action btn-view" 
                            @click="openUserDetailsModal(u)" 
                            title="View user account audit details"
                          >
                            View
                          </button>
                          <!-- Deactivate and Delete are managed in WordPress CMS -->
                          <span class="text-muted" style="font-size: 12px; align-self: center;">
                            Managed in WordPress CMS
                          </span>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!users.length">
                      <td colspan="8" class="empty-cell">No users match your filter criteria.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 3: SELLER MANAGEMENT -->
          <div v-else-if="currentTab === 'sellers'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>Artisan & Seller Overview</h3>
                  <p class="admin-card-sub">Oversight of registered artisans, workshop storefronts, inventory volume, and performance. Storefront verification decisions are handled in the WordPress CMS.</p>
                </div>
              </div>

              <!-- Search Bar -->
              <div class="admin-toolbar">
                <div class="search-input-box">
                  
                  <input 
                    type="text" 
                    v-model="sellerFilter.search" 
                    @input="debounceFetchSellers" 
                    placeholder="Search by artisan name, email, or workshop shop name…" 
                    class="form-control"
                  />
                </div>
                <div class="filter-controls-group">
                  <select v-model="sellerFilter.is_active" @change="fetchSellers" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1">Active Only</option>
                    <option value="0">Deactivated Only</option>
                  </select>
                  <select v-model="sellerFilter.verification_status" @change="fetchSellers" class="form-select">
                    <option value="">All Verifications</option>
                    <option value="pending">Pending Review</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>

              <!-- Sellers Table -->
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Workshop / Shop</th>
                      <th>Artisan Maker</th>
                      <th>Email / Contact</th>
                      <th>Products</th>
                      <th>Total Sales</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="s in sellers" :key="s.id">
                      <td>
                        <strong>{{ s.shop_name }}</strong>
                        <small v-if="s.bio" class="d-block text-muted text-truncate" style="max-width: 200px;">{{ s.bio }}</small>
                      </td>
                      <td>{{ s.name }}</td>
                      <td>
                        <div>{{ s.email }}</div>
                        <small class="text-muted">{{ s.phone || 'No phone' }}</small>
                      </td>
                      <td><strong>{{ s.products_count }}</strong> crafts</td>
                      <td><strong>₱{{ formatMoney(s.total_sales) }}</strong></td>
                      <td>
                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                          <span class="status-badge" :class="s.is_active ? 'badge-active' : 'badge-inactive'">
                            {{ s.is_active ? 'Active' : 'Deactivated' }}
                          </span>
                          <span class="status-badge" :class="verificationBadgeClass(s.verification_status)">
                            Verification: {{ s.verification_status || 'pending' }}
                          </span>
                        </div>
                      </td>
                      <td>
                        <div class="action-buttons-group">
                          <button type="button" class="btn-action btn-view" @click="viewSellerDetails(s.id)" title="Inspect seller profile and workshop">
                            View Shop
                          </button>

                          <!-- Storefront verification and account status are managed in WordPress CMS. -->
                          <span class="text-muted" style="font-size: 12px; align-self: center;">
                            Managed in WordPress CMS
                          </span>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!sellers.length">
                      <td colspan="7" class="empty-cell">No registered sellers found matching criteria.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 4: PRODUCT MANAGEMENT -->
          <div v-else-if="currentTab === 'products'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>Product Catalog Oversight</h3>
                  <p class="admin-card-sub">Audit listings, enforce marketplace handmade standards, update inventory status, or remove unauthorized items.</p>
                </div>
              </div>

              <!-- Product Toolbar -->
              <div class="admin-toolbar">
                <div class="search-input-box">
                  
                  <input 
                    type="text" 
                    v-model="productFilter.search" 
                    @input="debounceFetchProducts" 
                    placeholder="Search crafts by title, keywords…" 
                    class="form-control"
                  />
                </div>

                <div class="filter-controls-group">
                  <select v-model="productFilter.category" @change="fetchProducts" class="form-select">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>

                  <select v-model="productFilter.status" @change="fetchProducts" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="out_of_stock">Out of Stock</option>
                  </select>
                </div>
              </div>

              <!-- Products Table -->
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Category</th>
                      <th>Maker / Seller</th>
                      <th>Price</th>
                      <th>Stock</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in products" :key="p.id">
                      <td>
                        <div class="product-cell">
                          <img :src="p.image || fallbackImg" :alt="p.name" class="table-product-thumb" @error="handleImgError($event, fallbackImg)" />
                          <div>
                            <strong>{{ p.name }}</strong>
                            <small class="d-block text-muted">ID: #{{ p.id }}</small>
                          </div>
                        </div>
                      </td>
                      <td>{{ p.category?.name || 'Handmade' }}</td>
                      <td>{{ p.seller?.name || 'Artisan Maker' }}</td>
                      <td><strong>₱{{ formatMoney(p.price) }}</strong></td>
                      <td>
                        <span :class="{ 'text-danger': p.stock <= 5, 'text-success': p.stock > 5 }">
                          {{ p.stock }} units
                        </span>
                      </td>
                      <td>
                        <span class="status-badge" :class="getStatusBadgeClass(p.status || 'active')">
                          {{ p.status === 'out_of_stock' ? 'Out of Stock' : (p.status || 'active').charAt(0).toUpperCase() + (p.status || 'active').slice(1) }}
                        </span>
                      </td>
                      <td>
                        <div class="action-buttons-group">
                          <button type="button" class="btn-action btn-view" @click="openProductDetailsModal(p)" title="Inspect product details">
                            View
                          </button>
                          <button type="button" class="btn-action btn-delete" @click="handleDeleteProduct(p)" title="Remove product listing from marketplace">
                            Remove
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!products.length">
                      <td colspan="7" class="empty-cell">No products match your search or filter filters.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 5: CATEGORY MANAGEMENT -->
          <div v-else-if="currentTab === 'categories'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>Category Overview</h3>
                  <p class="admin-card-sub">Read-only catalog taxonomy. Category add, edit, and delete are managed in the WordPress CMS.</p>
                </div>
              </div>

              <!-- Categories Table -->
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Image</th>
                      <th>Category Name</th>
                      <th>Slug</th>
                      <th>Description</th>
                      <th>Associated Crafts</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in categories" :key="c.id">
                      <td>
                        <img :src="c.image || fallbackCatImg" :alt="c.name" class="table-cat-thumb" @error="handleImgError($event, fallbackCatImg)" />
                      </td>
                      <td><strong>{{ c.name }}</strong></td>
                      <td><code>{{ c.slug }}</code></td>
                      <td class="text-muted" style="max-width: 250px;">{{ c.description || 'Artisan handmade category' }}</td>
                      <td>
                        <span class="product-count-badge">{{ c.products_count ?? 0 }} products</span>
                      </td>
                      <td>
                        <span class="status-badge" :class="c.is_active ? 'badge-active' : 'badge-inactive'">
                          {{ c.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                    </tr>
                    <tr v-if="!categories.length">
                      <td colspan="6" class="empty-cell">No categories found in database.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 6: ORDER MANAGEMENT -->
          <div v-else-if="currentTab === 'orders'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>Order Management</h3>
                  <p class="admin-card-sub">Review transactions, verify customer shipping details, view payment method, and safely oversee fulfillment status.</p>
                </div>
              </div>

              <!-- Orders Toolbar -->
              <div class="admin-toolbar">
                <div class="search-input-box">
                  
                  <input 
                    type="text" 
                    v-model="orderFilter.search" 
                    @input="debounceFetchOrders" 
                    placeholder="Search by order number (CM-...), customer name, address…" 
                    class="form-control"
                  />
                </div>

                <div class="filter-controls-group">
                  <select v-model="orderFilter.status" @change="fetchOrders" class="form-select">
                    <option value="">All Order Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                  </select>

                  <select v-model="orderFilter.payment_status" @change="fetchOrders" class="form-select">
                    <option value="">All Payment Statuses</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                  </select>
                </div>
              </div>

              <!-- Orders Table -->
              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Order #</th>
                      <th>Customer</th>
                      <th>Items</th>
                      <th>Grand Total</th>
                      <th>Payment Method</th>
                      <th>Payment</th>
                      <th>Fulfillment</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="o in orders" :key="o.id">
                      <td><strong>{{ o.order_number }}</strong></td>
                      <td>
                        <strong>{{ o.user?.name || 'Customer' }}</strong>
                        <small class="d-block text-muted">{{ o.contact_number }}</small>
                      </td>
                      <td>{{ o.items?.length ?? 1 }} item(s)</td>
                      <td><strong>₱{{ formatMoney(o.total_amount) }}</strong></td>
                      <td>
                        <span class="payment-method-pill-sm">
                          {{ o.payment_method || 'Cash on Delivery' }}
                        </span>
                      </td>
                      <td>
                        <span class="status-badge" :class="o.payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid'">
                          {{ o.payment_status === 'paid' ? ' Paid' : ' Unpaid' }}
                        </span>
                      </td>
                      <td>
                        <span class="status-badge" :class="getStatusBadgeClass(o.status || 'pending')">
                          {{ (o.status || 'pending').charAt(0).toUpperCase() + (o.status || 'pending').slice(1) }}
                        </span>
                      </td>
                      <td class="text-muted">{{ formatDate(o.created_at) }}</td>
                      <td>
                        <button type="button" class="btn-action btn-view" @click="openOrderDetailsModal(o)">
                          Details
                        </button>
                      </td>
                    </tr>
                    <tr v-if="!orders.length">
                      <td colspan="9" class="empty-cell">No orders found matching criteria.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 7: SELLER PAYOUT REQUESTS -->
          <div v-else-if="currentTab === 'payouts'" class="tab-pane">
            <div class="admin-card">
              <div class="admin-card-header">
                <div>
                  <h3>Seller Payout Requests</h3>
                  <p class="admin-card-sub">Audit cash-out requests funded by released escrow, then approve, reject, or mark them paid.</p>
                </div>
                <button 
                  v-if="payoutsData?.requests && payoutsData.requests.length > 0"
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  style="display: inline-flex; align-items: center; gap: 6px;"
                  @click="exportAdminPayoutsToCsv"
                >
                  <span>Export Payouts (CSV)</span>
                </button>
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 18px;">
                <div style="padding: 14px 16px; border-radius: 10px; background: #fff8e1; border: 1px solid #fde68a; min-width: 0;">
                  <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #92400e; font-weight: 700; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Pending">Pending</span>
                  <div :title="'₱' + formatMoney(payoutsData?.summary?.pending_amount ?? 0)" style="font-size: clamp(1.15rem, 1.6vw, 1.35rem); font-weight: 700; color: #b45309; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">₱{{ formatMoney(payoutsData?.summary?.pending_amount ?? 0) }}</div>
                  <small style="color: #92400e; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="`${payoutsData?.summary?.pending_count ?? 0} request(s)`">{{ payoutsData?.summary?.pending_count ?? 0 }} request(s)</small>
                </div>
                <div style="padding: 14px 16px; border-radius: 10px; background: #eff6ff; border: 1px solid #bfdbfe; min-width: 0;">
                  <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #1e40af; font-weight: 700; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Approved / Queued">Approved / Queued</span>
                  <div :title="'₱' + formatMoney(payoutsData?.summary?.approved_amount ?? 0)" style="font-size: clamp(1.15rem, 1.6vw, 1.35rem); font-weight: 700; color: #1d4ed8; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">₱{{ formatMoney(payoutsData?.summary?.approved_amount ?? 0) }}</div>
                  <small style="color: #1e40af; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="`${payoutsData?.summary?.approved_count ?? 0} request(s)`">{{ payoutsData?.summary?.approved_count ?? 0 }} request(s)</small>
                </div>
                <div style="padding: 14px 16px; border-radius: 10px; background: #e8f5e9; border: 1px solid #c8e6c9; min-width: 0;">
                  <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #166534; font-weight: 700; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Paid Out">Paid Out</span>
                  <div :title="'₱' + formatMoney(payoutsData?.summary?.paid_amount ?? 0)" style="font-size: clamp(1.15rem, 1.6vw, 1.35rem); font-weight: 700; color: #2e7d32; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">₱{{ formatMoney(payoutsData?.summary?.paid_amount ?? 0) }}</div>
                  <small style="color: #166534; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="`${payoutsData?.summary?.paid_count ?? 0} request(s)`">{{ payoutsData?.summary?.paid_count ?? 0 }} request(s)</small>
                </div>
                <div style="padding: 14px 16px; border-radius: 10px; background: #ffebee; border: 1px solid #fecaca; min-width: 0;">
                  <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: #991b1b; font-weight: 700; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Rejected">Rejected</span>
                  <div :title="'₱' + formatMoney(payoutsData?.summary?.rejected_amount ?? 0)" style="font-size: clamp(1.15rem, 1.6vw, 1.35rem); font-weight: 700; color: #c62828; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">₱{{ formatMoney(payoutsData?.summary?.rejected_amount ?? 0) }}</div>
                  <small style="color: #991b1b; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="`${payoutsData?.summary?.rejected_count ?? 0} request(s)`">{{ payoutsData?.summary?.rejected_count ?? 0 }} request(s)</small>
                </div>
              </div>

              <div class="admin-toolbar">
                <div class="filter-controls-group">
                  <select v-model="payoutFilter.status" @change="fetchPayouts" class="form-select">
                    <option value="">All Payout Statuses</option>
                    <option value="pending">Pending Review</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>

              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Request</th>
                      <th>Artisan</th>
                      <th>Amount</th>
                      <th>Payout Details</th>
                      <th>Status</th>
                      <th>Requested</th>
                      <th>Admin Notes</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in payoutsData?.requests || []" :key="p.id">
                      <td><strong>#{{ p.id }}</strong></td>
                      <td>
                        <strong>{{ p.seller?.name || 'Artisan' }}</strong>
                        <small class="d-block text-muted">{{ p.seller?.sellerProfile?.shop_name || p.seller?.email }}</small>
                      </td>
                      <td><strong>₱{{ formatMoney(p.amount) }}</strong></td>
                      <td>
                        <div>{{ p.payout_method || 'Not specified' }}</div>
                        <small class="text-muted">{{ p.account_name || '—' }} · {{ p.account_number || '—' }}</small>
                      </td>
                      <td>
                        <span class="status-badge" :class="getStatusBadgeClass(p.status)">{{ p.status }}</span>
                      </td>
                      <td class="text-muted">{{ formatDate(p.requested_at || p.created_at) }}</td>
                      <td class="text-muted" style="max-width: 220px;">{{ p.admin_notes || p.notes || '—' }}</td>
                      <td>
                        <div class="action-buttons-group">
                          <button
                            v-if="p.status === 'pending'"
                            type="button"
                            class="btn-action"
                            style="background: #dcfce7; color: #166534;"
                            @click="handlePayoutAction(p, 'approve')"
                          >
                            Approve
                          </button>
                          <button
                            v-if="p.status === 'approved'"
                            type="button"
                            class="btn-action btn-view"
                            @click="handlePayoutAction(p, 'pay')"
                          >
                            Mark Paid
                          </button>
                          <button
                            v-if="p.status === 'pending' || p.status === 'approved'"
                            type="button"
                            class="btn-action btn-delete"
                            @click="handlePayoutAction(p, 'reject')"
                          >
                            Reject
                          </button>
                          <span v-if="p.status === 'paid' || p.status === 'rejected'" class="text-muted">Settled</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!(payoutsData?.requests || []).length">
                      <td colspan="8" class="empty-cell">No seller payout requests found.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- TAB 8: REPORTS -->
          <div v-else-if="currentTab === 'reports'" class="tab-pane">
            <!-- Reports Period Filter Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; background: #faf6f0; padding: 14px 18px; border-radius: 12px; border: 1px solid #efe8de;">
              <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: #2C221E; font-weight: 700;">Financial & Marketplace Performance Reports</h3>
                <p style="margin: 4px 0 0; font-size: 0.84rem; color: #7A6F68;">Platform sales, average order value, commission splits, and top artisan performers.</p>
              </div>
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <label style="font-size: 0.82rem; font-weight: 600; color: #423B36;">Date Range:</label>
                <select v-model="reportPeriod" @change="fetchReports" class="form-select" style="min-width: 140px; padding: 6px 12px; font-size: 0.85rem;">
                  <option value="all">All Time</option>
                  <option value="month">Last 30 Days</option>
                  <option value="week">Last 7 Days</option>
                  <option value="today">Today</option>
                </select>
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm"
                  style="display: inline-flex; align-items: center; gap: 6px;"
                  @click="exportAdminReportsToCsv"
                >
                  <span>Export Financials (CSV)</span>
                </button>
              </div>
            </div>
            <!-- Reports KPI Header -->
            <div class="admin-metrics-grid">
              <div class="metric-card metric-gold">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Total Realized Revenue">Total Realized Revenue</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(reports?.total_sales ?? 0)">₱{{ formatMoney(reports?.total_sales ?? 0) }}</strong>
                  <small class="metric-sub" title="Paid & delivered artisan orders">Paid & delivered artisan orders</small>
                </div>
              </div>

              <div class="metric-card metric-primary">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="All-time Orders">All-time Orders</span>
                  <strong class="metric-value" :title="String(reports?.total_orders ?? 0)">{{ reports?.total_orders ?? 0 }}</strong>
                  <small class="metric-sub" title="Recorded in MySQL database">Recorded in MySQL database</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label" title="Average Order Value">Average Order Value</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(reports?.average_order_value ?? (reports?.completed_orders_count ? (reports.total_sales / reports.completed_orders_count) : (reports?.total_orders ? (reports.total_sales / reports.total_orders) : 0)))">₱{{ formatMoney(reports?.average_order_value ?? (reports?.completed_orders_count ? (reports.total_sales / reports.completed_orders_count) : (reports?.total_orders ? (reports.total_sales / reports.total_orders) : 0))) }}</strong>
                  <small class="metric-sub" title="Per completed transaction">Per completed transaction</small>
                </div>
              </div>
            </div>

            <!-- Platform Revenue: commission + Artisan Pro + payout settlement -->
            <div class="admin-dashboard-two-col" style="margin-top: 24px;">
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Platform Commission Revenue</h3>
                    <p class="admin-card-sub">8% Basic / 3% Artisan Pro commission captured per craft item</p>
                  </div>
                </div>

                <div class="admin-metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 16px;">
                  <div class="metric-card metric-gold">
                    <div class="metric-icon">₱</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Total Commission">Total Commission</span>
                      <strong class="metric-value" :title="'₱' + formatMoney(reports?.total_commission ?? 0)">₱{{ formatMoney(reports?.total_commission ?? 0) }}</strong>
                      <small class="metric-sub" title="Non-cancelled orders">Non-cancelled orders</small>
                    </div>
                  </div>
                  <div class="metric-card metric-forest">
                    <div class="metric-icon">★</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Artisan Pro MRR">Artisan Pro MRR</span>
                      <strong class="metric-value" :title="'₱' + formatMoney(reports?.subscription_revenue?.mrr ?? 0)">₱{{ formatMoney(reports?.subscription_revenue?.mrr ?? 0) }}</strong>
                      <small class="metric-sub" :title="`${reports?.subscription_revenue?.pro_subscribers_count ?? 0} active Pro artisan(s)`">{{ reports?.subscription_revenue?.pro_subscribers_count ?? 0 }} active Pro artisan(s)</small>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="admin-table admin-table--compact">
                    <thead>
                      <tr>
                        <th>Escrow Status</th>
                        <th>Orders</th>
                        <th>Platform Fees</th>
                        <th>Seller Payouts</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, status) in reports?.commission_ledger || {}" :key="status">
                        <td><span class="status-badge" :class="getStatusBadgeClass(status)">{{ status }}</span></td>
                        <td>{{ row.orders_count }}</td>
                        <td><strong>₱{{ formatMoney(row.platform_fees) }}</strong></td>
                        <td>₱{{ formatMoney(row.seller_payouts) }}</td>
                      </tr>
                      <tr v-if="!Object.keys(reports?.commission_ledger || {}).length">
                        <td colspan="4" class="empty-cell">No escrow ledger entries yet.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Seller Payout Settlement</h3>
                    <p class="admin-card-sub">Cash-out requests funded by released escrow</p>
                  </div>
                </div>

                <div class="admin-metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
                  <div class="metric-card metric-amber">
                    <div class="metric-icon">⏳</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Pending Payouts">Pending Payouts</span>
                      <strong class="metric-value" :title="'₱' + formatMoney(reports?.payout_summary?.pending_amount ?? 0)">₱{{ formatMoney(reports?.payout_summary?.pending_amount ?? 0) }}</strong>
                      <small class="metric-sub" :title="`${reports?.payout_summary?.pending_count ?? 0} request(s)`">{{ reports?.payout_summary?.pending_count ?? 0 }} request(s)</small>
                    </div>
                  </div>
                  <div class="metric-card metric-primary">
                    <div class="metric-icon">✓</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Approved / Queued">Approved / Queued</span>
                      <strong class="metric-value" :title="'₱' + formatMoney(reports?.payout_summary?.approved_amount ?? 0)">₱{{ formatMoney(reports?.payout_summary?.approved_amount ?? 0) }}</strong>
                      <small class="metric-sub" :title="`${reports?.payout_summary?.approved_count ?? 0} request(s)`">{{ reports?.payout_summary?.approved_count ?? 0 }} request(s)</small>
                    </div>
                  </div>
                  <div class="metric-card metric-forest">
                    <div class="metric-icon">₱</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Paid Out">Paid Out</span>
                      <strong class="metric-value" :title="'₱' + formatMoney(reports?.payout_summary?.paid_amount ?? 0)">₱{{ formatMoney(reports?.payout_summary?.paid_amount ?? 0) }}</strong>
                      <small class="metric-sub" :title="`${reports?.payout_summary?.paid_count ?? 0} request(s)`">{{ reports?.payout_summary?.paid_count ?? 0 }} request(s)</small>
                    </div>
                  </div>
                  <div class="metric-card metric-danger">
                    <div class="metric-icon">✕</div>
                    <div class="metric-info">
                      <span class="metric-label" title="Rejected Payouts">Rejected Payouts</span>
                      <strong class="metric-value" :title="`${reports?.payout_summary?.rejected_count ?? 0} request(s)`">{{ reports?.payout_summary?.rejected_count ?? 0 }}</strong>
                      <small class="metric-sub" title="Denied requests">denied request(s)</small>
                    </div>
                  </div>
                </div>

                <p style="margin: 16px 0 0; color: #7A6F68; font-size: 0.82rem; line-height: 1.55;">
                  Total payout value requested: <strong>₱{{ formatMoney(reports?.payout_summary?.total_requested ?? 0) }}</strong>.
                  All settlements are funded only by escrow already released to the artisan.
                </p>
              </div>
            </div>

            <!-- Two Columns: Best Sellers & Active Makers -->
            <div class="admin-dashboard-two-col" style="margin-top: 24px;">
              <!-- Best-Selling Products -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Top Handcrafted Products</h3>
                    <p class="admin-card-sub">Ranked by actual cumulative units sold</p>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="admin-table admin-table--compact">
                    <thead>
                      <tr>
                        <th>Craft</th>
                        <th>Category</th>
                        <th>Units Sold</th>
                        <th>Total Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="bp in reports?.best_selling_products || []" :key="bp.product_id">
                        <td><strong>{{ bp.name }}</strong></td>
                        <td>{{ bp.category }}</td>
                        <td><span class="badge-units">{{ bp.total_sold }} sold</span></td>
                        <td><strong>₱{{ formatMoney(bp.total_revenue) }}</strong></td>
                      </tr>
                      <tr v-if="!reports?.best_selling_products?.length">
                        <td colspan="4" class="empty-cell">No order items recorded yet to compute best sellers.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Most Active Sellers -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h3>Most Active Artisan Workshops</h3>
                    <p class="admin-card-sub">Ranked by total artisan gross sales</p>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="admin-table admin-table--compact">
                    <thead>
                      <tr>
                        <th>Artisan / Workshop</th>
                        <th>Listings</th>
                        <th>Gross Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="as in reports?.most_active_sellers || []" :key="as.id">
                        <td>
                          <strong>{{ as.shop_name }}</strong>
                          <small class="d-block text-muted">{{ as.name }}</small>
                        </td>
                        <td>{{ as.products_count }} crafts</td>
                        <td><strong>₱{{ formatMoney(as.total_sales) }}</strong></td>
                      </tr>
                      <tr v-if="!reports?.most_active_sellers?.length">
                        <td colspan="3" class="empty-cell">No seller sales history available.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Full Width: Order Status Summary Breakdown -->
            <div class="admin-card" style="margin-top: 24px;">
              <div class="admin-card-header">
                <div>
                  <h3>Order Fulfillment Status Breakdown</h3>
                  <p class="admin-card-sub">Live tally of orders across all lifecycle states</p>
                </div>
              </div>

              <div class="status-summary-grid">
                <div v-for="(count, st) in reports?.order_status_summary || {}" :key="st" class="status-summary-card">
                  <span class="status-badge" :class="getStatusBadgeClass(st)">{{ st }}</span>
                  <strong class="status-count-val">{{ count }}</strong>
                  <span class="status-count-lbl">orders</span>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 8: REVIEWS MODERATION -->

          <!-- TAB 9: SUBSCRIPTIONS & ARTISAN TIERED REVENUE -->
          <div v-else-if="currentTab === 'subscriptions'" class="tab-pane">
            <div class="admin-metrics-grid">
              <div class="metric-card metric-gold">
                <div class="metric-icon">₱</div>
                <div class="metric-info">
                  <span class="metric-label" title="Monthly Recurring Revenue (MRR)">Monthly Recurring Revenue (MRR)</span>
                  <strong class="metric-value" :title="'₱' + formatMoney(subscriptionMetrics?.mrr ?? 0)">₱{{ formatMoney(subscriptionMetrics?.mrr ?? 0) }}</strong>
                  <small class="metric-sub" title="Combined Basic + Premium subscribers">Combined Basic + Premium subscribers</small>
                </div>
              </div>

              <div class="metric-card metric-primary">
                <div class="metric-icon">★</div>
                <div class="metric-info">
                  <span class="metric-label" title="Premium Artisan Pro">Premium Artisan Pro</span>
                  <strong class="metric-value" :title="String(subscriptionMetrics?.pro_subscribers_count ?? subscriptionMetrics?.premium_subscribers_count ?? 0)">{{ subscriptionMetrics?.pro_subscribers_count ?? subscriptionMetrics?.premium_subscribers_count ?? 0 }}</strong>
                  <small class="metric-sub" title="On 3% commission (₱299/mo or ₱2,990/yr)">On 3% commission (₱299/mo or ₱2,990/yr)</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">✓</div>
                <div class="metric-info">
                  <span class="metric-label" title="Basic Artisan Makers">Basic Artisan Makers</span>
                  <strong class="metric-value" :title="String(subscriptionMetrics?.basic_subscribers_count ?? subscriptionMetrics?.basic_sellers_count ?? 0)">{{ subscriptionMetrics?.basic_subscribers_count ?? subscriptionMetrics?.basic_sellers_count ?? 0 }}</strong>
                  <small class="metric-sub" title="On 5% commission (₱149/mo or ₱1,490/yr)">On 5% commission (₱149/mo or ₱1,490/yr)</small>
                </div>
              </div>

              <div class="metric-card metric-amber">
                <div class="metric-icon">○</div>
                <div class="metric-info">
                  <span class="metric-label" title="Free Artisan Starters">Free Artisan Starters</span>
                  <strong class="metric-value" :title="String(subscriptionMetrics?.free_sellers_count ?? 0)">{{ subscriptionMetrics?.free_sellers_count ?? 0 }}</strong>
                  <small class="metric-sub" :title="`On standard 8% commission • ${subscriptionMetrics?.conversion_rate ?? 0}% paid conversion`">On standard 8% commission • {{ subscriptionMetrics?.conversion_rate ?? 0 }}% paid conversion</small>
                </div>
              </div>
            </div>

            <!-- All-time revenue summary row -->
            <div style="display: flex; gap: 16px; margin-top: 16px; flex-wrap: wrap;">
              <div class="admin-card" style="flex: 1; min-width: 200px;">
                <div style="text-align: center; padding: 12px 0;">
                  <div style="font-size: 0.8rem; color: #7A6F68; margin-bottom: 4px;">All-time Subscription Revenue</div>
                  <strong style="font-size: 1.5rem; color: #2F4533;">₱{{ formatMoney(subscriptionMetrics?.all_time_revenue ?? 0) }}</strong>
                </div>
              </div>
              <div class="admin-card" style="flex: 1; min-width: 200px;">
                <div style="text-align: center; padding: 12px 0;">
                  <div style="font-size: 0.8rem; color: #7A6F68; margin-bottom: 4px;">Paid Subscriber Conversion Rate</div>
                  <strong style="font-size: 1.5rem; color: #C25E3E;">{{ subscriptionMetrics?.conversion_rate ?? 0 }}%</strong>
                </div>
              </div>
            </div>

            <!-- Subscribers Records Table -->
            <div class="admin-card" style="margin-top: 24px;">
              <div class="admin-card-header">
                <div>
                  <h3 class="panel-heading">Artisan Pro Billing & Subscription Records</h3>
                  <p class="admin-card-sub">Recent billing cycles and membership status for verified artisan workshops.</p>
                </div>
              </div>

              <div class="table-responsive">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Artisan / Workshop</th>
                      <th>Plan Tier</th>
                      <th>Billing Fee</th>
                      <th>Payment Channel</th>
                      <th>Start Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="sub in (subscriptionMetrics?.recent_records || [])" :key="sub.id">
                      <td>
                        <strong>{{ sub.user?.name || 'Artisan Maker' }}</strong>
                        <small class="d-block text-muted">{{ sub.user?.email }}</small>
                      </td>
                      <td>
                        <span class="status-badge badge-active">
                          {{ (sub.plan_name || '').toLowerCase().includes('premium') || (sub.plan_name || '').toLowerCase().includes('pro') ? '⭐ Premium Pro' : ((sub.plan_name || '').toLowerCase().includes('basic') ? '✓ Basic Maker' : sub.plan_name || 'Artisan Pro') }}
                        </span>
                      </td>
                      <td><strong>₱{{ formatMoney(sub.amount) }}</strong></td>
                      <td>{{ sub.payment_method || 'GCash / Online Wallet' }}</td>
                      <td class="text-muted">{{ formatDate(sub.starts_at || sub.created_at) }}</td>
                      <td>
                        <span class="status-badge badge-paid">Active</span>
                      </td>
                    </tr>
                    <tr v-if="!(subscriptionMetrics?.recent_records?.length)">
                      <td colspan="6" class="empty-cell">No subscription billing transactions recorded yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

                    <div v-else-if="currentTab === 'reviews'" class="tab-pane">
            <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
              <div>
                <h3 class="panel-heading">Product Reviews & Content Moderation</h3>
                <p class="panel-subheading">Oversee customer feedback across all artisan products. Remove inappropriate or violating reviews following marketplace policy.</p>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 0.82rem; font-weight: 600; color: #423B36;">Filter Rating:</label>
                <select v-model="reviewRatingFilter" @change="fetchReviews" class="form-select" style="min-width: 170px; padding: 6px 12px; font-size: 0.85rem;">
                  <option value="all">All Ratings</option>
                  <option value="5">★★★★★ (5 Stars)</option>
                  <option value="4">★★★★ (4 Stars)</option>
                  <option value="3">★★★ (3 Stars)</option>
                  <option value="low">★ / ★★ (Low Ratings / Review Needed)</option>
                </select>
              </div>
            </div>

            <div v-if="reviews.length === 0" class="empty-state-card" style="padding: 40px; text-align: center; color: #9e8f85; background: #faf6f0; border-radius: 12px; margin-top: 16px;">
              <p>No customer reviews currently exist in the database.</p>
            </div>

            <div v-else class="admin-table-wrapper" style="margin-top: 16px;">
              <table class="admin-data-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Date</th>
                    <th>Feedback / Comment</th>
                    <th>Moderation</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in reviews" :key="r.id">
                    <td class="font-medium text-charcoal">{{ r.product_name || `Product #${r.product_id}` }}</td>
                    <td>{{ r.user_name || 'Anonymous' }}</td>
                    <td>
                      <span style="color: #c97d3a; font-weight: 700;">★ {{ r.rating }}/5</span>
                    </td>
                    <td class="text-muted">{{ r.created_at }}</td>
                    <td style="max-width: 320px; color: #4a3f38;">{{ r.comment || '—' }}</td>
                    <td>
                      <button
                        class="btn-table-action btn-danger-soft"
                        @click="handleAdminDeleteReview(r)"
                        title="Remove violating review"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        
          <!-- TAB 10: WORDPRESS HEADLESS CMS HUB -->
          <div v-else-if="currentTab === 'cms'" class="tab-pane">
            <div class="panel-header-row">
              <div>
                <h3 class="panel-heading">Headless WordPress CMS Management</h3>
                <p class="panel-subheading">Centralized editorial content, announcements, and REST API bridge for CraftMarket storefront.</p>
              </div>
              <a :href="wpAdminUrl" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                Open WordPress Admin ↗
              </a>
            </div>

            <!-- Connection Status Strip -->
            <div style="background: #FFF; border: 1px solid #E8DFD5; border-radius: 10px; padding: 18px 24px; margin-top: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
              <div>
                <span style="font-size: 0.78rem; text-transform: uppercase; color: #7A6F68; font-weight: 700; letter-spacing: 0.05em;">REST API ENDPOINT</span>
                <div style="font-family: monospace; font-size: 0.92rem; color: #2F4533; font-weight: 600; margin-top: 4px;">
                  {{ wpEndpoint }}
                </div>
              </div>
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                  <span 
                    style="display: inline-block; width: 10px; height: 10px; border-radius: 50%;"
                    :style="{ background: cmsStatus.online ? '#2E7D32' : '#D97706' }"
                  ></span>
                  <span 
                    style="font-size: 0.88rem; font-weight: 600;"
                    :style="{ color: cmsStatus.online ? '#2E7D32' : '#D97706' }"
                  >
                    {{ cmsStatus.checking ? 'Checking Status...' : (cmsStatus.online ? 'CMS Integration Active (Connected)' : 'CMS Offline / Standby (Built-in Mode)') }}
                  </span>
                </div>
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm" 
                  style="padding: 4px 10px; font-size: 0.78rem;"
                  @click="checkCmsConnection"
                  :disabled="cmsStatus.checking"
                >
                  {{ cmsStatus.checking ? 'Testing...' : 'Test Connection' }}
                </button>
              </div>
            </div>

            <!-- 5 Required CMS Functions Grid (Aligned with Course & SRS Specifications) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 24px;">
              
              <!-- 1. Required CMS Function: Announcements -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>1. Announcements</h4>
                    <p class="admin-card-sub">Top bar announcement visible across the storefront</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #C25E3E;">
                  <em>"{{ cmsAnnouncement?.text || 'Free nationwide shipping on handcrafted orders over ₱1,500 • Direct Maker Support' }}"</em>
                  <div v-if="cmsAnnouncement?.isFromCms" style="margin-top: 6px; font-size: 0.75rem; color: #16a34a; font-weight: 600;">
                    ✓ Live from WordPress Announcement Post
                  </div>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Managed via WordPress Posts tagged <code>announcement</code>.</small>
                  <a :href="`${wpAdminUrl}/edit.php`" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    Manage Announcements in WordPress ↗
                  </a>
                </div>
              </div>

              <!-- 2. Required CMS Function: Website Content -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>2. Website Content</h4>
                    <p class="admin-card-sub">Homepage narrative, About Us, & cultural blogs</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #2F4533;">
                  <strong>Homepage Hero, About Us & Heritage Stories</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">
                    <span v-if="cmsArticles.length > 0">{{ cmsArticles.length }} published cultural articles retrieved dynamically from WordPress headless REST API.</span>
                    <span v-else>Cultural storytelling pages published in WordPress and rendered headless on Vue.js storefront.</span>
                  </p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Managed via WordPress Pages (<code>homepage</code>, <code>about-us</code>) and Posts.</small>
                  <a :href="`${wpAdminUrl}/edit.php?post_type=page`" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    Edit Website Content in WordPress ↗
                  </a>
                </div>
              </div>

              <!-- 3. Required CMS Function: Products / Services -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>3. Products & Services</h4>
                    <p class="admin-card-sub">Artisan catalog listings & inventory synced to CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #3B82F6;">
                  <strong>{{ stats?.total_products ?? products.length }} Active Craft Listings</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">All marketplace products, stock levels, and category taxonomies synced with WordPress CMS bridge.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-products</code> plugin hook.</small>
                  <a :href="`${wpAdminUrl}/admin.php?page=craftmarket-products`" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    View Products in WordPress CMS ↗
                  </a>
                </div>
              </div>

              <!-- 4. Required CMS Function: User Data -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>4. User Data</h4>
                    <p class="admin-card-sub">Artisan makers & customer directory synced to CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #8B5CF6;">
                  <strong>{{ stats?.total_users ?? users.length }} Registered Members</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">{{ stats?.total_sellers ?? sellers.length }} artisan workshops and {{ stats?.total_customers ?? 0 }} buyers managed and accessible from CMS.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-users</code> plugin hook.</small>
                  <a :href="`${wpAdminUrl}/admin.php?page=craftmarket-users`" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    View Users in WordPress CMS ↗
                  </a>
                </div>
              </div>

              <!-- 5. Required CMS Function: Reports -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>5. Financial & Sales Reports</h4>
                    <p class="admin-card-sub">Order summaries & escrow payouts mirrored in CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #10B981;">
                  <strong>₱{{ formatMoney(stats?.total_sales ?? 0) }} Total GMV Volume</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">Financial audit trails, order breakdown, and escrow payouts accessible by CMS administrators.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-reports</code> plugin hook.</small>
                  <a :href="`${wpAdminUrl}/admin.php?page=craftmarket-reports`" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    View Reports in WordPress CMS ↗
                  </a>
                </div>
              </div>

            </div>

            <!-- Requirement Compliance Note -->
            <div style="margin-top: 24px; padding: 20px; background: #E8F0EA; border-radius: 10px; border: 1px solid #C4D9C7;">
              <h4 style="margin: 0 0 8px 0; color: #2F4533; font-size: 0.95rem;">✓ System Integration & Architecture Compliance Note</h4>
              <p style="margin: 0; font-size: 0.84rem; color: #423B36; line-height: 1.6;">
                CraftMarket integrates a <strong>Decoupled Headless CMS</strong>. Non-technical marketing staff use WordPress for editorial content & announcements, while transactional multi-vendor data (users, products, escrow payouts) is secured via Laravel REST API and synced with the <strong>CraftMarket CMS Bridge Plugin</strong>.
              </p>
            </div>
          </div>

        </section>
      </div>

    </div>

    <!-- ─── MODAL 0: SIGN OUT CONFIRMATION MODAL ────────────────────────── -->
    <div v-if="showLogoutModal" class="admin-modal-backdrop" @click.self="showLogoutModal = false">
      <div class="admin-modal-box" style="max-width: 440px;">
        <div class="modal-header">
          <div>
            <h3>Sign Out</h3>
            <p class="modal-sub">End administrator session</p>
          </div>
          <button type="button" class="btn-modal-close" @click="showLogoutModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p style="margin: 0; color: #555555; font-size: 0.95rem; line-height: 1.55;">
            Are you sure you want to sign out from the Admin Panel? You will need to log in again to access administrative controls.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" @click="showLogoutModal = false">
            Cancel
          </button>
          <button 
            type="button" 
            class="btn btn-sm" 
            style="background: #dc2626; color: #ffffff; border: none; font-weight: 600; padding: 8px 18px; border-radius: 8px; cursor: pointer;"
            @click="confirmLogout"
          >
            Sign Out
          </button>
        </div>
      </div>
    </div>

    <!-- ─── MODAL 1: USER ACCOUNT AUDIT MODAL ─────────────────────────────── -->
    <div v-if="showUserDetailsModal" class="admin-modal-backdrop" @click.self="showUserDetailsModal = false">
      <div class="admin-modal-box">
        <div class="modal-header">
          <div>
            <h3>User Account Audit: {{ activeUser?.name }}</h3>
            <p class="modal-sub">Account ID: #{{ activeUser?.id }} • Registered on {{ formatDate(activeUser?.created_at) }}</p>
          </div>
          <button type="button" class="btn-modal-close" @click="showUserDetailsModal = false"></button>
        </div>
        
        <div class="modal-body">
          <div class="order-info-columns">
            <div class="order-info-card">
              <h4>Account Information</h4>
              <p><strong>Full Name:</strong> {{ activeUser?.name }}</p>
              <p><strong>Email Address:</strong> {{ activeUser?.email }}</p>
              <p>
                <strong>System Role:</strong> 
                <span class="role-badge" :class="'role-' + activeUser?.role">{{ activeUser?.role }}</span>
              </p>
              <div class="role-assignment-box">
                <label for="userRoleSelect"><strong>Assign System Role:</strong></label>
                <div class="role-assignment-controls">
                  <select
                    id="userRoleSelect"
                    v-model="selectedUserRole"
                    class="form-select"
                    :disabled="isSavingUserRole"
                  >
                    <option value="customer">Customer (Shopper)</option>
                    <option value="seller">Seller (Artisan)</option>
                    <option value="admin">Administrator</option>
                  </select>
                  <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    :disabled="isSavingUserRole || selectedUserRole === activeUser?.role"
                    @click="saveUserRole"
                  >
                    {{ isSavingUserRole ? 'Saving…' : 'Save Role' }}
                  </button>
                </div>
                <small class="role-assignment-help">
                  Role changes take effect immediately. The user will be asked to sign in again for the new permissions.
                </small>
              </div>
              <p>
                <strong>Account Status:</strong> 
                <span class="status-badge" :class="activeUser?.is_active ? 'badge-active' : 'badge-inactive'">
                  {{ activeUser?.is_active ? 'Active' : 'Deactivated' }}
                </span>
              </p>
            </div>

            <div class="order-info-card">
              <h4>Contact & Platform Activity</h4>
              <p><strong>Contact Phone:</strong> {{ activeUser?.phone || 'Not provided' }}</p>
              <p><strong>Registered Address:</strong> {{ activeUser?.address || 'Not provided' }}</p>
              <p v-if="activeUser?.role === 'customer'"><strong>Completed Orders:</strong> {{ activeUser?.orders_count ?? 0 }}</p>
              <p v-if="activeUser?.role === 'seller'"><strong>Active Craft Listings:</strong> {{ activeUser?.products_count ?? 0 }}</p>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
          <span class="text-muted" style="font-size: 12px;">
            Account deactivation and deletion are managed in WordPress CMS.
          </span>
          <button type="button" class="btn btn-secondary" @click="showUserDetailsModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- ─── MODAL 2: SELLER DETAILS MODAL ────────────────────────────────── -->
    <div v-if="showSellerDetailsModal" class="admin-modal-backdrop" @click.self="showSellerDetailsModal = false">
      <div class="admin-modal-box admin-modal-lg">
        <div class="modal-header">
          <div>
            <h3>Artisan Workshop: {{ activeSeller?.shop_name }}</h3>
            <p class="modal-sub">Maker: {{ activeSeller?.name }} ({{ activeSeller?.email }})</p>
          </div>
          <button type="button" class="btn-modal-close" @click="showSellerDetailsModal = false"></button>
        </div>

        <div class="modal-body">
          <div class="seller-details-overview-grid">
            <div class="detail-stat">
              <span>Status</span>
              <strong :class="activeSeller?.is_active ? 'text-success' : 'text-danger'">
                {{ activeSeller?.is_active ? 'Active Maker' : 'Deactivated' }}
              </strong>
            </div>
            <div class="detail-stat">
              <span>Storefront Verification</span>
              <strong>
                <span class="status-badge" :class="verificationBadgeClass(activeSeller?.verification_status)">
                  {{ activeSeller?.verification_status || 'pending' }}
                </span>
              </strong>
            </div>
            <div class="detail-stat">
              <span>Listings</span>
              <strong>{{ activeSeller?.products_count }} craft items</strong>
            </div>
            <div class="detail-stat">
              <span>Total Sales</span>
              <strong>₱{{ formatMoney(activeSeller?.total_sales ?? 0) }}</strong>
            </div>
            <div class="detail-stat">
              <span>Contact</span>
              <span>{{ activeSeller?.phone || 'No phone recorded' }}</span>
            </div>
          </div>

          <div v-if="activeSeller?.bio" class="seller-bio-card">
            <strong>Workshop Bio:</strong>
            <p>{{ activeSeller.bio }}</p>
          </div>

          <h4 style="margin-top: 20px; margin-bottom: 12px;">Artisan Products Catalog ({{ activeSeller?.products?.length ?? 0 }})</h4>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in activeSeller?.products || []" :key="p.id">
                  <td><strong>{{ p.name }}</strong></td>
                  <td>{{ p.category?.name || 'Handmade' }}</td>
                  <td>₱{{ formatMoney(p.price) }}</td>
                  <td>{{ p.stock }} units</td>
                  <td>
                    <span class="status-badge" :class="getStatusBadgeClass(p.status)">{{ p.status }}</span>
                  </td>
                </tr>
                <tr v-if="!activeSeller?.products?.length">
                  <td colspan="5" class="empty-cell">No products currently listed by this artisan maker.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <span class="text-muted" style="font-size: 12px;">
              Storefront verification and account lifecycle are managed in WordPress CMS.
            </span>
          </div>
          <button type="button" class="btn btn-secondary" @click="showSellerDetailsModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- ─── MODAL 4: ORDER DETAILS MODAL ─────────────────────────────────── -->
        <!-- Product Details & Inspection Modal -->
    <div v-if="showProductDetailsModal" class="admin-modal-backdrop" @click.self="showProductDetailsModal = false">
      <div class="admin-modal-box admin-modal-lg">
        <div class="modal-header">
          <div>
            <h3>Product Inspection: {{ activeProduct?.name }}</h3>
            <p class="modal-sub">Listing ID: #{{ activeProduct?.id }} • Category: {{ activeProduct?.category?.name || 'Handmade' }}</p>
          </div>
          <button type="button" class="btn-modal-close" @click="showProductDetailsModal = false"></button>
        </div>

        <div class="modal-body">
          <div class="order-info-columns">
            <div class="order-info-card" style="text-align: center;">
              <img 
                :src="activeProduct?.image || fallbackImg" 
                :alt="activeProduct?.name" 
                style="max-width: 100%; max-height: 220px; object-fit: cover; border-radius: 8px; border: 1px solid #E5E7EB;"
                @error="handleImgError($event, fallbackImg)"
              />
              <div style="margin-top: 12px;">
                <span class="status-badge" :class="getStatusBadgeClass(activeProduct?.status || 'active')">
                  {{ activeProduct?.status === 'out_of_stock' ? 'Out of Stock' : (activeProduct?.status || 'active') }}
                </span>
              </div>
            </div>

            <div class="order-info-card">
              <h4>Catalog Specifications</h4>
              <p><strong>Artisan Maker:</strong> {{ activeProduct?.seller?.name || activeProduct?.seller?.shop_name || 'Artisan Workshop' }}</p>
              <p><strong>Unit Price:</strong> ₱{{ formatMoney(activeProduct?.price) }}</p>
              <p><strong>Inventory Stock:</strong> {{ activeProduct?.stock }} units available</p>
              <p><strong>Category Taxonomy:</strong> {{ activeProduct?.category?.name || 'Handmade' }}</p>
              <p v-if="activeProduct?.created_at"><strong>Listed Date:</strong> {{ formatDate(activeProduct?.created_at) }}</p>
            </div>
          </div>

          <div style="margin-top: 20px;">
            <h4>Product Description</h4>
            <p style="color: #4B5563; font-size: 0.9rem; line-height: 1.6; background: #F9FAFB; padding: 14px; border-radius: 6px; border: 1px solid #E5E7EB;">
              {{ activeProduct?.description || 'No detailed product description provided.' }}
            </p>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
          <button 
            type="button" 
            class="btn btn-danger btn-sm" 
            @click="handleDeleteProduct(activeProduct); showProductDetailsModal = false;"
          >
            Remove Listing
          </button>
          <button type="button" class="btn btn-secondary" @click="showProductDetailsModal = false">Close</button>
        </div>
      </div>
    </div>

    <div v-if="showOrderModal" class="admin-modal-backdrop" @click.self="showOrderModal = false">
      <div class="admin-modal-box admin-modal-lg">
        <div class="modal-header">
          <div>
            <h3>Order Details: {{ activeOrder?.order_number }}</h3>
            <p class="modal-sub">Placed on {{ formatDate(activeOrder?.created_at) }}</p>
          </div>
          <button type="button" class="btn-modal-close" @click="showOrderModal = false"></button>
        </div>

        <div class="modal-body">
          <!-- Customer & Delivery info -->
          <div class="order-info-columns">
            <div class="order-info-card">
              <h4>Customer Information</h4>
              <p><strong>Name:</strong> {{ activeOrder?.user?.name }}</p>
              <p><strong>Email:</strong> {{ activeOrder?.user?.email }}</p>
              <p><strong>Contact Rider Phone:</strong> {{ activeOrder?.contact_number }}</p>
              <p><strong>Delivery Destination:</strong> {{ activeOrder?.shipping_address }}</p>
              <p v-if="activeOrder?.notes"><strong>Notes:</strong> {{ activeOrder?.notes }}</p>
            </div>

            <div class="order-info-card">
              <h4>Payment & Settlement</h4>
              <p><strong>Payment Method:</strong> {{ activeOrder?.payment_method || 'Cash on Delivery' }}</p>
              <p>
                <strong>Payment Status:</strong> 
                <span class="status-badge" :class="activeOrder?.payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid'">
                  {{ activeOrder?.payment_status }}
                </span>
              </p>
              <p>
                <strong>Escrow Status:</strong> 
                <span class="status-badge" :class="getStatusBadgeClass(activeOrder?.escrow_status || 'held')">
                  {{ activeOrder?.escrow_status || 'held' }}
                </span>
                <span v-if="activeOrder?.escrow_status === 'released_to_seller'" style="margin-left: 6px; font-size: 0.75rem; color: #16a34a; font-weight: 600;">✓ Funds Released</span>
              </p>
              <p v-if="activeOrder?.latest_payment?.payment_reference">
                <strong>PayMongo Ref:</strong> <code>{{ activeOrder.latest_payment.payment_reference }}</code>
              </p>
              <p><strong>Subtotal:</strong> ₱{{ formatMoney(activeOrder?.subtotal) }}</p>
              <p><strong>Shipping:</strong> ₱{{ formatMoney(activeOrder?.shipping_fee) }}</p>
              <p class="grand-total-line">
                <strong>Grand Total:</strong> 
                <span class="grand-total-val">₱{{ formatMoney(activeOrder?.total_amount) }}</span>
              </p>
            </div>
          </div>

          <h4 style="margin-top: 20px; margin-bottom: 12px;">Ordered Handcrafted Items</h4>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Artisan Maker</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                  <th>Item Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in activeOrder?.items || []" :key="item.id">
                  <td><strong>{{ item.product_name }}</strong></td>
                  <td>{{ item.seller?.name || 'Artisan' }}</td>
                  <td>₱{{ formatMoney(item.unit_price || item.price) }}</td>
                  <td>{{ item.quantity }}</td>
                  <td><strong>₱{{ formatMoney(item.subtotal) }}</strong></td>
                  <td>
                    <span class="status-badge" :class="getStatusBadgeClass(item.status || 'pending')">
                      {{ item.status || 'pending' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <!-- Left: Administrative Dispute & Escrow Governance Controls -->
          <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <label style="font-weight: 600; font-size: 0.82rem; color: #4b5563;">Admin Resolution:</label>
              <select v-model="selectedNewStatus" class="form-select" style="min-width: 160px; padding: 5px 8px; font-size: 0.82rem;">
                <option value="" disabled>Select Resolution</option>
                <option value="cancelled">Cancel Order (Dispute)</option>
                <option value="refunded">Refund Order (Return Funds)</option>
              </select>
              <button 
                type="button" 
                class="btn btn-primary btn-sm" 
                style="padding: 5px 10px; font-size: 0.82rem;"
                :disabled="isUpdatingOrderStatus || !selectedNewStatus || selectedNewStatus === activeOrder?.status"
                @click="handleUpdateOrderStatus(activeOrder, selectedNewStatus)"
              >
                <span v-if="isUpdatingOrderStatus">Updating...</span>
                <span v-else>Apply Resolution</span>
              </button>
            </div>

            <span style="width: 1px; height: 22px; background: #e5e7eb;"></span>

            <!-- Escrow Actions -->
            <button
              type="button"
              class="btn btn-sm"
              style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 0.82rem; padding: 5px 10px;"
              :disabled="activeOrder?.escrow_status === 'released_to_seller' || activeOrder?.payment_status !== 'paid'"
              @click="handleEscrowAction(activeOrder, 'release')"
              title="Manual override to release locked funds to artisan"
            >
              Release Escrow
            </button>
            <button
              type="button"
              class="btn btn-sm"
              style="background: #fff8e1; color: #92400e; border: 1px solid #fde68a; font-size: 0.82rem; padding: 5px 10px;"
              :disabled="activeOrder?.escrow_status === 'held'"
              @click="handleEscrowAction(activeOrder, 'hold')"
              title="Freeze funds in escrow pending dispute investigation"
            >
              Hold Escrow
            </button>
          </div>

          <!-- Right: Close Button -->
          <button type="button" class="btn btn-secondary" @click="showOrderModal = false">Close</button>
        </div>

        <!-- Clarification Banner: Seller Fulfillment Responsibility -->
        <div style="padding: 10px 20px; background: #FAF8F5; border-top: 1px solid #E8DFD5; font-size: 0.78rem; color: #7A6F68; display: flex; align-items: center; gap: 8px;">
          <span>ℹ️</span>
          <span><strong>Note on Fulfillment:</strong> Workshop preparation, packaging, courier dispatch, and delivery tracking are directly managed by the artisan seller in their workshop dashboard.</span>
        </div>
      </div>
    </div>

  </main>
</template>

<script setup>
import { subscriptionService } from '../services/subscriptionService.js';
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminService } from '../services/adminService.js';
import { cmsService } from '../services/cmsService.js';
import { auth } from '../data/auth.js';
import { useAuth } from '../composables/useAuth.js';

const route = useRoute();
const router = useRouter();
const { logout } = useAuth();

// Current active sidebar tab
const currentTab = ref(route.query.tab || 'dashboard');
const cmsStatus = ref({ checked: false, online: false, checking: false });
const wpEndpoint = computed(() => cmsService.getEndpoint());
const wpAdminUrl = computed(() => import.meta.env.VITE_WP_ADMIN_URL || (wpEndpoint.value ? wpEndpoint.value.replace(/\/wp-json\/.*$/, '/wp-admin') : 'http://localhost/craftmarket-cms/wp-admin'));
const cmsAnnouncement = ref(null);
const cmsArticles = ref([]);

const checkCmsConnection = async () => {
  cmsStatus.value.checking = true;
  try {
    const res = await cmsService.checkHealth();
    cmsStatus.value = { checked: true, online: res.online, checking: false };
    if (res.online) {
      const [ann, posts] = await Promise.allSettled([
        cmsService.getLatestAnnouncement(),
        cmsService.getBlogPosts()
      ]);
      if (ann.status === 'fulfilled' && ann.value) {
        cmsAnnouncement.value = ann.value;
      }
      if (posts.status === 'fulfilled' && Array.isArray(posts.value)) {
        cmsArticles.value = posts.value;
      }
    }
  } catch {
    cmsStatus.value = { checked: true, online: false, checking: false };
  }
};


// Loading & Feedback
const isLoading   = ref(false);
const feedbackMsg = ref('');
const feedbackClass = ref('alert-success');

// Fallback images
const fallbackImg = 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=150&q=80';
const fallbackCatImg = 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?auto=format&fit=crop&w=150&q=80';

const handleImgError = (event, fallback = fallbackImg) => {
  event.target.onerror = null;
  event.target.src = fallback;
};

const getStatusBadgeClass = (status) => {
  if (!status) return 'badge-pending';
  const clean = String(status).toLowerCase().trim().replace(/[\s_]+/g, '-');
  return `badge-${clean}`;
};

// Current admin user info
const currentAdminName = computed(() => auth.user.value?.fullName || auth.user.value?.name || 'Administrator');
const adminInitials = computed(() => {
  const n = currentAdminName.value || 'AD';
  return n.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
});

// Data collections
const stats = ref(null);
const recentActivity = ref(null);
const reports = ref(null);
const users = ref([]);
const sellers = ref([]);
const products = ref([]);
const categories = ref([]);
const orders = ref([]);
const reviews = ref([]);
const subscriptionMetrics = ref(null);
const subscribersList = ref([]);
const payoutsData = ref({ summary: {}, requests: [] });

// Filter States
const userFilter = reactive({ search: '', role: '', is_active: '' });
const sellerFilter = reactive({ search: '', is_active: '', verification_status: '' });
const productFilter = reactive({ search: '', category: '', status: '' });
const orderFilter = reactive({ search: '', status: '', payment_status: '' });
const payoutFilter = reactive({ status: '' });

// Modals State
const showUserDetailsModal = ref(false);
const activeUser = ref(null);
const selectedUserRole = ref('customer');
const isSavingUserRole = ref(false);

const showSellerDetailsModal = ref(false);
const activeSeller = ref(null);

const showProductDetailsModal = ref(false);
const activeProduct = ref(null);

const openProductDetailsModal = (p) => {
  activeProduct.value = p;
  showProductDetailsModal.value = true;
};

const showOrderModal = ref(false);
const activeOrder = ref(null);
const selectedNewStatus = ref('pending');
const isUpdatingOrderStatus = ref(false);

// ─── Lifecycle & Tab Switching ───────────────────────────────────────────────
onMounted(async () => {
  await loadCurrentTabData();
});

const setTab = async (tabName) => {
  currentTab.value = tabName;
  router.replace({ query: { ...route.query, tab: tabName } });
  await loadCurrentTabData();
};

const loadCurrentTabData = async () => {
  isLoading.value = true;
  feedbackMsg.value = '';

  try {
    if (currentTab.value === 'dashboard') {
      const [s, ra] = await Promise.all([
        adminService.getStats(),
        adminService.getRecentActivity()
      ]);
      stats.value = s;
      recentActivity.value = ra;
    } else if (currentTab.value === 'users') {
      await fetchUsers();
    } else if (currentTab.value === 'sellers') {
      await fetchSellers();
    } else if (currentTab.value === 'products') {
      await Promise.all([fetchProducts(), fetchCategories()]);
    } else if (currentTab.value === 'categories') {
      await fetchCategories();
    } else if (currentTab.value === 'orders') {
      await fetchOrders();
    } else if (currentTab.value === 'payouts') {
      await fetchPayouts();
    } else if (currentTab.value === 'reports') {
      await fetchReports();
    } else if (currentTab.value === 'reviews') {
      await fetchReviews();
    } else if (currentTab.value === 'subscriptions') {
      await fetchSubscriptionMetrics();
    } else if (currentTab.value === 'cms') {
      await checkCmsConnection();
    }
  } catch (err) {
    showToast(err.message || 'Failed to retrieve data from API.', 'alert-danger');
  } finally {
    isLoading.value = false;
  }
};

// ─── Fetch Methods ───────────────────────────────────────────────────────────
const fetchUsers = async () => {
  users.value = await adminService.getUsers({
    search: userFilter.search.trim() || undefined,
    role: userFilter.role || undefined,
    is_active: userFilter.is_active !== '' ? userFilter.is_active : undefined
  });
};

const fetchSellers = async () => {
  sellers.value = await adminService.getSellers({
    search: sellerFilter.search.trim() || undefined,
    is_active: sellerFilter.is_active !== '' ? sellerFilter.is_active : undefined,
    verification_status: sellerFilter.verification_status || undefined
  });
};

const fetchProducts = async () => {
  products.value = await adminService.getProducts({
    search: productFilter.search.trim() || undefined,
    category: productFilter.category || undefined,
    status: productFilter.status || undefined
  });
};

const fetchCategories = async () => {
  categories.value = await adminService.getCategories();
};

const fetchOrders = async () => {
  orders.value = await adminService.getOrders({
    search: orderFilter.search.trim() || undefined,
    status: orderFilter.status || undefined,
    payment_status: orderFilter.payment_status || undefined
  });
};

const fetchPayouts = async () => {
  payoutsData.value = await adminService.getPayouts({
    status: payoutFilter.status || undefined
  });
};

// Debounced search helpers
let searchTimeout = null;
const debounce = (fn) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fn, 350);
};
const debounceFetchUsers = () => debounce(fetchUsers);
const debounceFetchSellers = () => debounce(fetchSellers);
const debounceFetchProducts = () => debounce(fetchProducts);
const debounceFetchOrders = () => debounce(fetchOrders);

// ─── User Actions ────────────────────────────────────────────────────────────
const openUserDetailsModal = (u) => {
  activeUser.value = u;
  selectedUserRole.value = u.role || 'customer';
  showUserDetailsModal.value = true;
};

const saveUserRole = async () => {
  if (!activeUser.value) return;

  if (selectedUserRole.value === activeUser.value.role) {
    showToast('The selected role is already assigned to this account.', 'alert-success');
    return;
  }

  if (!confirm(`Change ${activeUser.value.name}'s role from "${activeUser.value.role}" to "${selectedUserRole.value}"?`)) {
    selectedUserRole.value = activeUser.value.role;
    return;
  }

  isSavingUserRole.value = true;

  try {
    const res = await adminService.updateUser(activeUser.value.id, {
      role: selectedUserRole.value
    });

    if (res && res.success) {
      const updated = res.data || {};
      activeUser.value = { ...activeUser.value, ...updated, role: updated.role || selectedUserRole.value };
      const idx = users.value.findIndex((u) => u.id === activeUser.value.id);
      if (idx !== -1) users.value[idx] = { ...users.value[idx], ...activeUser.value };
      showToast(res.message || 'User role updated successfully.', 'alert-success');
    }
  } catch (err) {
    selectedUserRole.value = activeUser.value.role;
    showToast(err.response?.data?.message || err.message || 'Failed to update user role.', 'alert-danger');
  } finally {
    isSavingUserRole.value = false;
  }
};

// ─── Seller Actions ──────────────────────────────────────────────────────────
const viewSellerDetails = async (sellerId) => {
  try {
    activeSeller.value = await adminService.getSeller(sellerId);
    showSellerDetailsModal.value = true;
  } catch (err) {
    showToast(err.message || 'Failed to retrieve seller profile details.', 'alert-danger');
  }
};

const verificationBadgeClass = (status) => {
  if (status === 'approved') return 'badge-active';
  if (status === 'rejected') return 'badge-inactive';
  return 'badge-pending';
};

// ─── Product Actions ─────────────────────────────────────────────────────────
const updateProductStatus = async (product, newStatus) => {
  try {
    const res = await adminService.updateProduct(product.id, { status: newStatus });
    if (res && res.success) {
      product.status = newStatus;
      showToast(` Listing status for "${product.name}" set to ${newStatus}.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update product status.', 'alert-danger');
  }
};

const handleDeleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to permanently remove "${product.name}" from the marketplace?`)) return;

  try {
    const res = await adminService.deleteProduct(product.id);
    if (res && res.success) {
      products.value = products.value.filter(p => p.id !== product.id);
      showToast(` Product listing "${product.name}" removed successfully.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to remove product.', 'alert-danger');
  }
};

// ─── Order Actions ───────────────────────────────────────────────────────────
const openOrderDetailsModal = (o) => {
  activeOrder.value = o;
  selectedNewStatus.value = o?.status || 'pending';
  showOrderModal.value = true;
};

const handleEscrowAction = async (order, action) => {
  if (!order) return;

  const labels = {
    release: 'release the escrow to the artisan',
    hold: 'place the escrow back on hold',
    refund: 'refund the escrow to the customer',
    cancel: 'cancel the escrow ledger',
  };

  if (!confirm(`Are you sure you want to ${labels[action] || action} for order ${order.order_number}?`)) return;

  isUpdatingOrderStatus.value = true;
  try {
    const res = await adminService.releaseEscrow(order.id, action);
    if (res?.success) {
      const updated = res.data;
      Object.assign(order, updated);
      if (activeOrder.value?.id === order.id) {
        activeOrder.value = updated;
      }
      const matched = orders.value?.find(item => item.id === order.id);
      if (matched) {
        Object.assign(matched, updated);
      }
      showToast(res.message, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update escrow.', 'alert-danger');
  } finally {
    isUpdatingOrderStatus.value = false;
  }
};

// ─── Payout Actions ──────────────────────────────────────────────────────────
const handlePayoutAction = async (payout, action) => {
  if (!payout) return;

  const labels = {
    approve: 'approve this payout request',
    reject: 'reject this payout request',
    pay: 'mark this payout as paid',
  };

  if (!confirm(`Are you sure you want to ${labels[action] || action} for ${payout.seller?.name || 'this artisan'} (₱${formatMoney(payout.amount)})?`)) return;

  let adminNotes = null;
  if (action === 'reject') {
    adminNotes = prompt('Optional: enter the reason for rejecting this payout.') || null;
  }

  try {
    const res = await adminService.updatePayoutRequest(payout.id, { action, admin_notes: adminNotes });
    if (res?.success) {
      showToast(res.message, 'alert-success');
      await fetchPayouts();
      if (currentTab.value === 'dashboard') {
        stats.value = await adminService.getStats();
      }
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update payout request.', 'alert-danger');
  }
};

const handleUpdateOrderStatus = async (order, newStatus) => {
  if (!order) return;
  isUpdatingOrderStatus.value = true;
  try {
    const res = await adminService.updateOrderStatus(order.id, newStatus);
    if (res && res.success) {
      order.status = newStatus;
      if (newStatus === 'delivered') {
        order.escrow_status = 'released_to_seller';
        order.payment_status = 'paid';
      } else if (newStatus === 'cancelled' || newStatus === 'refunded') {
        order.escrow_status = order.payment_status === 'paid' ? 'refunded' : 'cancelled';
        if (order.payment_status === 'paid') {
          order.payment_status = 'refunded';
        }
      }
      const matched = orders.value?.find(item => item.id === order.id);
      if (matched) {
        matched.status = order.status;
        matched.escrow_status = order.escrow_status;
        matched.payment_status = order.payment_status;
      }
      showToast(`Order ${order.order_number} status updated to ${newStatus}. Customer notification email sent.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update order status.', 'alert-danger');
  } finally {
    isUpdatingOrderStatus.value = false;
  }
};

// ─── Helpers ─────────────────────────────────────────────────────────────────
const showToast = (msg, cls = 'alert-success') => {
  feedbackMsg.value = msg;
  feedbackClass.value = cls;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const formatMoney = (val) => {
  return Number(val || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const showLogoutModal = ref(false);

const handleLogout = () => {
  showLogoutModal.value = true;
};

const confirmLogout = async () => {
  showLogoutModal.value = false;
  await logout();
  router.push('/login');
};
const reportPeriod = ref('all');
const fetchReports = async () => {
  try {
    reports.value = await adminService.getReports({ period: reportPeriod.value });
  } catch (err) {
    showToast(err.message || 'Failed to retrieve reports.', 'alert-danger');
  }
};

const exportAdminReportsToCsv = () => {
  if (!reports.value) {
    showToast('No report data available to export.', 'alert-warning');
    return;
  }
  const r = reports.value;
  const escapeCsv = (str) => {
    if (str == null) return '""';
    const s = String(str).replace(/"/g, '""');
    return `"${s}"`;
  };

  const lines = [
    '=== CRAFTMARKET EXECUTIVE FINANCIAL SUMMARY ===',
    `Report Period,${escapeCsv(r.period || reportPeriod.value)}`,
    `Generated On,${escapeCsv(new Date().toLocaleString('en-PH'))}`,
    `Total Gross Sales (PHP),${Number(r.total_sales || 0).toFixed(2)}`,
    `Total Orders Placed,${r.total_orders || 0}`,
    `Completed Orders,${r.completed_orders_count || 0}`,
    `Average Order Value (PHP),${Number(r.average_order_value || 0).toFixed(2)}`,
    `Platform Commission Revenue (PHP),${Number(r.total_commission || 0).toFixed(2)}`,
    `Subscription MRR (PHP),${Number(r.subscription_revenue?.mrr || 0).toFixed(2)}`,
    `All-Time Subscription Revenue (PHP),${Number(r.subscription_revenue?.all_time_revenue || 0).toFixed(2)}`,
    `Active Pro/Premium Artisans,${r.subscription_revenue?.pro_subscribers_count || 0}`,
    `Active Basic Artisans,${r.subscription_revenue?.basic_subscribers_count || 0}`,
    `Free Starter Artisans,${r.subscription_revenue?.free_sellers_count || 0}`,
    '',
    '=== TOP SELLING HANDCRAFTED PRODUCTS ===',
    'Product ID,Product Name,Quantity Sold,Total Revenue (PHP)'
  ];

  if (Array.isArray(r.best_selling_products)) {
    r.best_selling_products.forEach(p => {
      lines.push([
        escapeCsv(p.id),
        escapeCsv(p.name),
        p.total_quantity_sold || 0,
        Number(p.total_sales || 0).toFixed(2)
      ].join(','));
    });
  }

  lines.push('');
  lines.push('=== MOST ACTIVE ARTISAN MAKERS ===');
  lines.push('Artisan ID,Artisan Name,Shop Name,Orders Fulfilled,Total GMV (PHP)');

  if (Array.isArray(r.most_active_sellers)) {
    r.most_active_sellers.forEach(s => {
      lines.push([
        escapeCsv(s.id),
        escapeCsv(s.name),
        escapeCsv(s.seller_profile?.shop_name || s.shop_name || 'Artisan Workshop'),
        s.total_orders || 0,
        Number(s.total_sales || 0).toFixed(2)
      ].join(','));
    });
  }

  const csvContent = lines.join('\r\n');
  const filename = `craftmarket_executive_report_${new Date().toISOString().slice(0, 10)}.csv`;
  const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast('Executive financial report exported successfully!', 'alert-success');
};

const exportAdminPayoutsToCsv = () => {
  const reqs = payoutsData.value?.requests || [];
  if (reqs.length === 0) {
    showToast('No payout requests to export.', 'alert-warning');
    return;
  }
  const escapeCsv = (str) => {
    if (str == null) return '""';
    const s = String(str).replace(/"/g, '""');
    return `"${s}"`;
  };
  const headers = [
    'Request ID',
    'Artisan / Seller Name',
    'Shop Name',
    'Amount (PHP)',
    'Payment Channel',
    'Account Details',
    'Status',
    'Requested Date',
    'Processed Date',
    'Transaction Reference'
  ];
  const rows = reqs.map(p => [
    escapeCsv(p.id),
    escapeCsv(p.seller?.name || 'Seller'),
    escapeCsv(p.seller?.seller_profile?.shop_name || p.seller?.sellerProfile?.shop_name || 'Workshop'),
    Number(p.amount || 0).toFixed(2),
    escapeCsv(p.payout_method || p.payment_method || 'GCash'),
    escapeCsv(p.account_number || p.account_details || ''),
    escapeCsv(p.status),
    escapeCsv(p.requested_at || p.created_at || ''),
    escapeCsv(p.processed_at || p.approved_at || ''),
    escapeCsv(p.reference_number || '')
  ].join(','));

  const csvContent = [headers.join(','), ...rows].join('\r\n');
  const filename = `craftmarket_admin_payouts_${new Date().toISOString().slice(0, 10)}.csv`;
  const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast('Payout requests ledger exported successfully!', 'alert-success');
};

const reviewRatingFilter = ref('all');
const fetchReviews = async () => {
  try {
    const params = {};
    if (reviewRatingFilter.value === 'low') {
      params.low = '1';
    } else if (reviewRatingFilter.value !== 'all') {
      params.rating = reviewRatingFilter.value;
    }
    reviews.value = await adminService.getReviews(params);
  } catch (err) {
    showToast(err.message || 'Failed to retrieve reviews.', 'alert-danger');
  }
};

const handleAdminDeleteReview = async (review) => {
  if (!confirm(`Are you sure you want to moderate and remove this review from ${review.user_name} on product "${review.product_name}"? This action cannot be undone.`)) {
    return;
  }
  try {
    await adminService.deleteReview(review.id);
    feedbackMsg.value = 'Review removed successfully by administrator moderation.';
    feedbackClass.value = 'alert-success';
    reviews.value = await adminService.getReviews();
  } catch (err) {
    feedbackMsg.value = err?.response?.data?.message || 'Failed to remove review.';
    feedbackClass.value = 'alert-danger';
  }
};

const fetchSubscriptionMetrics = async () => {
  try {
    const res = await subscriptionService.getAdminSubscriptionMetrics();
    subscriptionMetrics.value = res?.data || null;
  } catch (err) {
    console.warn('Could not load subscription metrics:', err);
  }
};
</script>

<style scoped>
.admin-dashboard-wrapper {
  background-color: var(--cm-cream, #FAF6F0);
  min-height: 90vh;
  padding: 2.5rem 0 5rem;
}

.admin-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

/* Header Banner */
.admin-header-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 16px;
  padding: 28px 36px;
  margin-bottom: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

.dashboard-welcome h1 {
  font-size: 1.85rem;
  font-weight: 700;
  color: #2c2c2c;
  margin: 6px 0 8px;
}

.dashboard-welcome p {
  color: #7a6f68;
  margin: 0;
  font-size: 0.95rem;
}

.section-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #c25e3e;
}

.admin-profile-pill {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 18px;
  background: #faf6f0;
  border: 1px solid #e8d5b8;
  border-radius: 12px;
}

.admin-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #3e5943;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
}

.admin-role-badge {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #c25e3e;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Layout Grid */
.admin-layout-grid {
  display: grid;
  /* minmax(0, 1fr) lets the content column shrink so wide tables
     scroll inside their card instead of pushing cards off-screen. */
  grid-template-columns: 240px minmax(0, 1fr);
  gap: 24px;
  align-items: start;
}

/* Sidebar */
.admin-sidebar {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 16px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.02);
}

.dashboard-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 500;
  color: #555555;
  background: transparent;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s ease;
  text-decoration: none;
  width: 100%;
}

.dashboard-nav-item:hover {
  background: #faf6f0;
  color: #3e5943;
}

.dashboard-nav-item.active {
  background: #3e5943;
  color: #ffffff;
  font-weight: 600;
}

.nav-item-icon {
  font-size: 1.15rem;
}

.sidebar-divider {
  border: none;
  border-top: 1px solid #efe8de;
  margin: 12px 0;
}

.btn-logout {
  color: #c25e3e;
}
.btn-logout:hover {
  background: #fdf2ee;
  color: #a33e20;
}

/* Alert Notification */
.admin-alert {
  padding: 14px 20px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 0.92rem;
  font-weight: 500;
}
.alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.alert-danger  { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
.btn-close-alert {
  background: transparent;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: inherit;
}

/* Loading Box */
.admin-loading-box {
  text-align: center;
  padding: 80px 20px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #efe8de;
}
.admin-spinner {
  width: 44px;
  height: 44px;
  border: 4px solid #e8d5b8;
  border-top-color: #3e5943;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Cards & Metrics */
.admin-metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.metric-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 14px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.02);
  border-left: 4px solid #3e5943;
  min-width: 0;
  box-sizing: border-box;
}
.metric-primary   { border-left-color: #3e5943; }
.metric-forest    { border-left-color: #2f6940; }
.metric-terracotta{ border-left-color: #c25e3e; }
.metric-sand      { border-left-color: #9c8a80; }
.metric-amber     { border-left-color: #f59e0b; }
.metric-gold      { border-left-color: #059669; }
.metric-danger    { border-left-color: #c62828; }

.metric-icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  border-radius: 12px;
  background: #faf6f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
  color: #7a6f68;
}
.metric-primary    .metric-icon { background: #f0f4ff; color: #4a6fa5; }
.metric-forest     .metric-icon { background: #f0faf3; color: #3a7a56; }
.metric-terracotta .metric-icon { background: #fff4f0; color: #b05535; }
.metric-sand       .metric-icon { background: #fdf8f0; color: #9a7a45; }
.metric-amber      .metric-icon { background: #fff9ec; color: #b07800; }
.metric-gold       .metric-icon { background: #f0fdf6; color: #059669; }
.metric-danger     .metric-icon { background: #fef2f2; color: #dc2626; }

.metric-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1;
  overflow: hidden;
}
.metric-label {
  font-size: 0.74rem;
  color: #7a6f68;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.metric-value {
  font-size: clamp(1.2rem, 1.7vw, 1.45rem);
  font-weight: 700;
  color: #2c2c2c;
  line-height: 1.25;
  margin: 2px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: -0.01em;
}
.metric-sub {
  font-size: 0.74rem;
  color: #9c8a80;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Two Column Layout */
.admin-dashboard-two-col {
  display: grid;
  /* minmax(0, …) keeps both columns on screen on laptops; wide tables
     scroll inside their own card instead of pushing the side card away. */
  grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
  gap: 24px;
}

.admin-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.02);
  min-width: 0;
}

.admin-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}
.admin-card-header h3 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #2c2c2c;
  margin: 0 0 4px;
}
.admin-card-sub {
  font-size: 0.85rem;
  color: #7a6f68;
  margin: 0;
}

.btn-text-action {
  background: transparent;
  border: none;
  color: #3e5943;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}
.btn-text-action:hover {
  text-decoration: underline;
}

/* Toolbar */
.admin-toolbar {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.search-input-box {
  position: relative;
  flex: 1;
  min-width: 260px;
}
.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #9c8a80;
}
.search-input-box .form-control {
  padding-left: 38px;
}
.filter-controls-group {
  display: flex;
  gap: 12px;
}

/* Tables */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  width: 100%;
}
.admin-table {
  width: 100%;
  min-width: 580px;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.admin-table th {
  text-align: left;
  padding: 12px 14px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #7a6f68;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #efe8de;
  background: #faf6f0;
}
.admin-table td {
  padding: 14px;
  border-bottom: 1px solid #efe8de;
  vertical-align: middle;
}
.empty-cell {
  text-align: center;
  padding: 32px 14px !important;
  color: #9c8a80;
  font-style: italic;
}

/* Compact report tables: fit inside the narrow two-column cards on laptops
   instead of forcing a 580px-wide horizontal scrollbar. */
.admin-table--compact {
  min-width: 0;
}
.admin-table--compact th,
.admin-table--compact td {
  padding: 10px;
  font-size: 0.82rem;
  word-break: break-word;
}

/* Badges */
.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.74rem;
  font-weight: 600;
  text-transform: capitalize;
}
.badge-active, .badge-approved, .badge-paid, .badge-delivered, .badge-confirmed, .badge-released_to_seller, .badge-released-to-seller { background: #e8f5e9; color: #2e7d32; }
.badge-pending, .badge-processing, .badge-unpaid, .badge-held { background: #fff8e1; color: #b78103; }
.badge-shipped, .badge-in-transit                         { background: #e3f2fd; color: #1565c0; }
.badge-inactive, .badge-rejected, .badge-cancelled, .badge-out_of_stock, .badge-out-of-stock, .badge-refunded { background: #ffebee; color: #c62828; }

.role-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
}
.role-customer { background: #e8f4fd; color: #0284c7; }
.role-seller   { background: #eef7ee; color: #2f6940; }
.role-admin    { background: #fdf2ee; color: #c25e3e; }

.product-count-badge {
  background: #faf6f0;
  border: 1px solid #e8d5b8;
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 600;
  color: #7a6f68;
}

.payment-method-pill-sm {
  background: #f3f4f6;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.78rem;
  color: #374151;
}

/* Status select inside table */
.status-select {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  border: 1px solid #d1d5db;
  cursor: pointer;
}

/* Action buttons */
.action-buttons-group {
  display: flex;
  gap: 4px;
  align-items: center;
  flex-wrap: wrap;
  white-space: nowrap;
  max-width: 220px;
}
.btn-action {
  white-space: nowrap;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  line-height: 1.4;
}
.btn-edit       { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
.btn-edit:hover { background: #e5e7eb; }
.btn-view       { background: #e8f4fd; color: #0284c7; border-color: #bae6fd; }
.btn-view:hover { background: #bae6fd; }
.btn-delete     { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
.btn-delete:hover{ background: #ffcdd2; }
.btn-approve    { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
.btn-approve:hover { background: #c8e6c9; }
.btn-activate   { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
.btn-activate:hover { background: #c8e6c9; }
.btn-deactivate { background: #fff8e1; color: #b78103; border-color: #fde68a; }
.btn-deactivate:hover { background: #fef08a; }

/* User cells & thumbs */
.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.user-avatar-small {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}
.user-avatar-circle {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.table-product-thumb {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #efe8de;
}
.table-cat-thumb {
  width: 44px;
  height: 44px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #efe8de;
}
.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Recent Users List */
.recent-users-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.recent-user-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #efe8de;
  background: #faf6f0;
  min-width: 0;
}
.recent-user-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.user-email-text {
  font-size: 0.78rem;
  color: #7a6f68;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.recent-user-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
}

/* Activities Timeline */
.activities-timeline {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.activity-timeline-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  border-radius: 10px;
  background: #faf6f0;
  border: 1px solid #efe8de;
}
.activity-icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #ffffff;
  border: 1px solid #efe8de;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
  color: #7a6f68;
}
.act-order   { background: #f0f4ff; border-color: #c7d4f5; color: #4a6fa5; }
.act-user    { background: #f0faf3; border-color: #b8e4c6; color: #3a7a56; }
.act-product { background: #fff7f0; border-color: #f5d8bb; color: #b06030; }
.activity-details {
  flex: 1;
}
.activity-title {
  display: block;
  font-size: 0.9rem;
  color: #2c2c2c;
}
.activity-desc {
  font-size: 0.8rem;
  color: #7a6f68;
}
.activity-time {
  font-size: 0.78rem;
  color: #9c8a80;
}

/* Status Breakdown Grid */
.status-summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 14px;
}
.status-summary-card {
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  min-width: 0;
  overflow: hidden;
}
.status-count-val {
  display: block;
  font-size: clamp(1.4rem, 2vw, 1.8rem);
  font-weight: 700;
  color: #2c2c2c;
  margin: 6px 0 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.status-count-lbl {
  font-size: 0.75rem;
  color: #7a6f68;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Modals */
.admin-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}
.admin-modal-box {
  background: #ffffff;
  border-radius: 16px;
  max-width: 520px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.admin-modal-lg {
  max-width: 800px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px 24px;
  border-bottom: 1px solid #efe8de;
}
.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
  color: #2c2c2c;
}
.modal-sub {
  font-size: 0.85rem;
  color: #7a6f68;
  margin: 4px 0 0;
}
.btn-modal-close {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #9c8a80;
}
.modal-body {
  padding: 20px 24px;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid #efe8de;
  background: #faf6f0;
}

/* Forms */
.form-group {
  margin-bottom: 16px;
}
.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}
.form-control, .form-select {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  outline: none;
}
.form-control:focus, .form-select:focus {
  border-color: #3e5943;
  box-shadow: 0 0 0 3px rgba(62, 89, 67, 0.12);
}
.form-row-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

/* Seller details modal */
.seller-details-overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 16px;
}
.detail-stat {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}
.detail-stat span {
  font-size: 0.72rem;
  color: #7a6f68;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.detail-stat strong {
  font-size: 1rem;
  color: #2c2c2c;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.seller-bio-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 0.88rem;
  color: #555555;
}

/* Order Details Modal */
.order-info-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.order-info-card {
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 10px;
  padding: 16px;
  font-size: 0.88rem;
}
.order-info-card h4 {
  font-size: 0.95rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 12px;
  color: #3e5943;
}
.order-info-card p {
  margin: 6px 0;
  color: #4b5563;
}
.grand-total-line {
  margin-top: 12px !important;
  padding-top: 8px;
  border-top: 1px solid #e8d5b8;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.grand-total-val {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c2c2c;
}

/* Responsive */
@media (max-width: 992px) {
  .admin-layout-grid {
    grid-template-columns: 1fr;
  }
  .admin-dashboard-two-col {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .admin-container {
    padding-left: 12px;
    padding-right: 12px;
  }

  .admin-header-banner {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }

  .admin-sidebar {
    flex-direction: row;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
    padding: 8px;
    gap: 4px;
  }

  .admin-sidebar .dashboard-nav-item {
    flex-shrink: 0;
    font-size: 0.8rem;
    padding: 8px 12px;
  }

  .order-info-columns {
    grid-template-columns: 1fr;
  }

  .form-row-2col {
    grid-template-columns: 1fr;
  }
}

/* Role assignment (admin-only user audit modal) */
.role-assignment-box {
  margin: 14px 0;
  padding: 12px 14px;
  background: #FAF6F0;
  border: 1px solid #E8DFD5;
  border-radius: 10px;
}

.role-assignment-controls {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-top: 8px;
  flex-wrap: wrap;
}

.role-assignment-controls .form-select {
  flex: 1 1 180px;
  min-width: 160px;
}

.role-assignment-help {
  display: block;
  margin-top: 8px;
  color: #7A6F68;
  font-size: 0.75rem;
  line-height: 1.45;
}
</style>