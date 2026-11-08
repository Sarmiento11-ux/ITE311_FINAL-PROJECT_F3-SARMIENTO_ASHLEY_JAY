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
            
            <span class="nav-icon">📊</span><span>Dashboard</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'users' }"
            @click="setTab('users')"
          >
            
            <span class="nav-icon">👥</span><span>Users ({{ stats?.total_users ?? users.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'sellers' }"
            @click="setTab('sellers')"
          >
            
            <span class="nav-icon">🏪</span><span>Sellers ({{ stats?.total_sellers ?? sellers.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'products' }"
            @click="setTab('products')"
          >
            
            <span class="nav-icon">🎨</span><span>Products ({{ stats?.total_products ?? products.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'categories' }"
            @click="setTab('categories')"
          >
            
            <span class="nav-icon">🏷️</span><span>Categories ({{ categories.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="setTab('orders')"
          >
            
            <span class="nav-icon">📦</span><span>Orders ({{ stats?.total_orders ?? orders.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reports' }"
            @click="setTab('reports')"
          >
            
            <span class="nav-icon">📈</span><span>Reports</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'subscriptions' }"
            @click="setTab('subscriptions')"
          >
            <span class="nav-icon">💎</span><span>Subscriptions</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reviews' }"
            @click="setTab('reviews')"
          >
            <span class="nav-icon">★</span><span>Reviews</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'cms' }"
            @click="setTab('cms')"
          >
            <span class="nav-icon">🌐</span><span>WordPress CMS</span>
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
                  <span class="metric-label">Total Users</span>
                  <strong class="metric-value">{{ stats?.total_users ?? 0 }}</strong>
                  <small class="metric-sub">{{ stats?.total_customers ?? 0 }} Customers · {{ stats?.total_sellers ?? 0 }} Sellers</small>
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
                  <span class="metric-label">Registered Artisans</span>
                  <strong class="metric-value">{{ stats?.total_sellers ?? 0 }}</strong>
                  <small class="metric-sub">Local craft workshops</small>
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
                  <span class="metric-label">Active Customers</span>
                  <strong class="metric-value">{{ stats?.total_customers ?? 0 }}</strong>
                  <small class="metric-sub">Registered buyers</small>
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
                  <span class="metric-label">Catalog Products</span>
                  <strong class="metric-value">{{ stats?.total_products ?? 0 }}</strong>
                  <small class="metric-sub">Handmade listings</small>
                </div>
              </div>

              <div class="metric-card metric-amber">
                <div class="metric-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                  </svg>
                </div>
                <div class="metric-info">
                  <span class="metric-label">Total Orders</span>
                  <strong class="metric-value">{{ stats?.total_orders ?? 0 }}</strong>
                  <small class="metric-sub">{{ stats?.orders_pending ?? 0 }} pending fulfillment</small>
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
                  <span class="metric-label">Total Sales Volume</span>
                  <strong class="metric-value">₱{{ formatMoney(stats?.total_sales ?? 0) }}</strong>
                  <small class="metric-sub">{{ stats?.orders_paid ?? 0 }} confirmed transactions</small>
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
                          <span class="status-badge" :class="'badge-' + order.status.toLowerCase()">
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
                            @click="openEditUserModal(u)" 
                            title="Edit user details and role"
                          >
                            Edit
                          </button>
                          <button 
                            type="button" 
                            class="btn-action" 
                            :class="u.is_active ? 'btn-delete' : 'btn-view'"
                            @click="toggleUserActive(u)" 
                            :title="u.is_active ? 'Deactivate user account' : 'Reactivate user account'"
                          >
                            {{ u.is_active ? 'Deactivate' : 'Activate' }}
                          </button>
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
                  <h3>Artisan & Seller Management</h3>
                  <p class="admin-card-sub">Oversight of registered artisans, workshop storefronts, inventory volume, and performance.</p>
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
                        <span class="status-badge" :class="s.is_active ? 'badge-active' : 'badge-inactive'">
                          {{ s.is_active ? 'Active' : 'Deactivated' }}
                        </span>
                      </td>
                      <td>
                        <div class="action-buttons-group">
                          <button type="button" class="btn-action btn-view" @click="viewSellerDetails(s.id)">
                            View Shop
                          </button>
                          <button 
                            type="button" 
                            class="btn-action" 
                            :class="s.is_active ? 'btn-delete' : 'btn-view'"
                            @click="toggleSellerActive(s)"
                            :title="s.is_active ? 'Deactivate seller account' : 'Reactivate seller account'"
                          >
                            {{ s.is_active ? 'Deactivate' : 'Activate' }}
                          </button>
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
                          <img :src="p.image || fallbackImg" :alt="p.name" class="table-product-thumb" />
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
                        <span class="status-badge" :class="'badge-' + (p.status || 'active').toLowerCase()">
                          {{ p.status === 'out_of_stock' ? 'Out of Stock' : (p.status || 'active').charAt(0).toUpperCase() + (p.status || 'active').slice(1) }}
                        </span>
                      </td>
                      <td>
                        <div class="action-buttons-group">
                          <router-link :to="'/products/' + p.id" target="_blank" class="btn-action btn-view" title="View product page in new tab">
                            ↗ View
                          </router-link>
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
                  <h3>Category Management</h3>
                  <p class="admin-card-sub">Organize artisanal craftsmanship taxonomy. Prevents accidental deletion of categories containing live products.</p>
                </div>
                <button type="button" class="btn btn-primary" @click="openCreateCategoryModal">
                  + Add New Category
                </button>
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
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in categories" :key="c.id">
                      <td>
                        <img :src="c.image || fallbackCatImg" :alt="c.name" class="table-cat-thumb" />
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
                      <td>
                        <div class="action-buttons-group">
                          <button type="button" class="btn-action btn-edit" @click="openEditCategoryModal(c)">
                            Edit
                          </button>
                          <button 
                            type="button" 
                            class="btn-action btn-delete" 
                            @click="handleDeleteCategory(c)"
                            :title="c.products_count > 0 ? 'Cannot delete: Category has active products' : 'Delete category'"
                          >
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!categories.length">
                      <td colspan="7" class="empty-cell">No categories found in database.</td>
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
                        <span class="status-badge" :class="'badge-' + (o.status || 'pending').toLowerCase()">
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

          <!-- TAB 7: REPORTS -->
          <div v-else-if="currentTab === 'reports'" class="tab-pane">
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
                  <span class="metric-label">Total Realized Revenue</span>
                  <strong class="metric-value">₱{{ formatMoney(reports?.total_sales ?? 0) }}</strong>
                  <small class="metric-sub">Paid & delivered artisan orders</small>
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
                  <span class="metric-label">All-time Orders</span>
                  <strong class="metric-value">{{ reports?.total_orders ?? 0 }}</strong>
                  <small class="metric-sub">Recorded in MySQL database</small>
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
                  <span class="metric-label">Average Order Value</span>
                  <strong class="metric-value">
                    ₱{{ formatMoney(reports?.average_order_value ?? (reports?.completed_orders_count ? (reports.total_sales / reports.completed_orders_count) : (reports?.total_orders ? (reports.total_sales / reports.total_orders) : 0))) }}
                  </strong>
                  <small class="metric-sub">Per completed transaction</small>
                </div>
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
                  <table class="admin-table">
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
                  <table class="admin-table">
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
                  <span class="status-badge" :class="'badge-' + st.toLowerCase()">{{ st }}</span>
                  <strong class="status-count-val">{{ count }}</strong>
                  <span class="status-count-lbl">orders</span>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 8: REVIEWS MODERATION -->

          <!-- TAB 9: SUBSCRIPTIONS & ARTISAN PRO REVENUE -->
          <div v-else-if="currentTab === 'subscriptions'" class="tab-pane">
            <div class="admin-metrics-grid">
              <div class="metric-card metric-gold">
                <div class="metric-icon">💎</div>
                <div class="metric-info">
                  <span class="metric-label">Monthly Recurring Revenue (MRR)</span>
                  <strong class="metric-value">₱{{ formatMoney(subscriptionMetrics?.mrr ?? 0) }}</strong>
                  <small class="metric-sub">Active Artisan Pro subscribers</small>
                </div>
              </div>

              <div class="metric-card metric-primary">
                <div class="metric-icon">⭐</div>
                <div class="metric-info">
                  <span class="metric-label">Artisan Pro Makers</span>
                  <strong class="metric-value">{{ subscriptionMetrics?.pro_subscribers_count ?? 0 }}</strong>
                  <small class="metric-sub">On 3% commission tier</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">🏛️</div>
                <div class="metric-info">
                  <span class="metric-label">Basic Free Makers</span>
                  <strong class="metric-value">{{ subscriptionMetrics?.basic_sellers_count ?? 0 }}</strong>
                  <small class="metric-sub">On standard 8% commission</small>
                </div>
              </div>

              <div class="metric-card metric-amber">
                <div class="metric-icon">📈</div>
                <div class="metric-info">
                  <span class="metric-label">All-time Subscription GMV</span>
                  <strong class="metric-value">₱{{ formatMoney(subscriptionMetrics?.all_time_revenue ?? 0) }}</strong>
                  <small class="metric-sub">{{ subscriptionMetrics?.conversion_rate ?? 0 }}% conversion rate</small>
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
                        <span class="status-badge badge-active">⭐ Artisan Pro</span>
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
            <div class="panel-header-row">
              <div>
                <h3 class="panel-heading">Product Reviews & Content Moderation</h3>
                <p class="panel-subheading">Oversee customer feedback across all artisan products. Remove inappropriate or violating reviews following marketplace policy.</p>
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
              <a href="http://localhost/craftmarket-cms/wp-admin" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
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
                    <h4>📢 1. Announcements</h4>
                    <p class="admin-card-sub">Top bar announcement visible across the storefront</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #C25E3E;">
                  <em>"Free nationwide shipping on handcrafted orders over ₱1,500 • Direct Maker Support"</em>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Managed via WordPress Posts tagged <code>announcement</code>.</small>
                  <a href="http://localhost/craftmarket-cms/wp-admin/edit.php" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    Manage Announcements in WordPress ↗
                  </a>
                </div>
              </div>

              <!-- 2. Required CMS Function: Website Content -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>🎨 2. Website Content</h4>
                    <p class="admin-card-sub">Homepage narrative, About Us, & cultural blogs</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #2F4533;">
                  <strong>Homepage Hero, About Us & Heritage Stories</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">Cultural storytelling pages published in WordPress and rendered headless on Vue.js storefront.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Managed via WordPress Pages (<code>homepage</code>, <code>about-us</code>) and Posts.</small>
                  <a href="http://localhost/craftmarket-cms/wp-admin/edit.php?post_type=page" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    Edit Website Content in WordPress ↗
                  </a>
                </div>
              </div>

              <!-- 3. Required CMS Function: Products / Services -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>🏺 3. Products & Services</h4>
                    <p class="admin-card-sub">Artisan catalog listings & inventory synced to CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #3B82F6;">
                  <strong>{{ stats?.total_products ?? products.length }} Active Craft Listings</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">All marketplace products, stock levels, and category taxonomies synced with WordPress CMS bridge.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-products</code> plugin hook.</small>
                  <a href="http://localhost/craftmarket-cms/wp-admin/admin.php?page=craftmarket-products" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    View Products in WordPress CMS ↗
                  </a>
                </div>
              </div>

              <!-- 4. Required CMS Function: User Data -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>👥 4. User Data</h4>
                    <p class="admin-card-sub">Artisan makers & customer directory synced to CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #8B5CF6;">
                  <strong>{{ stats?.total_users ?? users.length }} Registered Members</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">{{ stats?.total_sellers ?? sellers.length }} artisan workshops and {{ stats?.total_customers ?? 0 }} buyers managed and accessible from CMS.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-users</code> plugin hook.</small>
                  <a href="http://localhost/craftmarket-cms/wp-admin/admin.php?page=craftmarket-users" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
                    View Users in WordPress CMS ↗
                  </a>
                </div>
              </div>

              <!-- 5. Required CMS Function: Reports -->
              <div class="admin-card">
                <div class="admin-card-header">
                  <div>
                    <h4>📈 5. Financial & Sales Reports</h4>
                    <p class="admin-card-sub">Order summaries & escrow payouts mirrored in CMS</p>
                  </div>
                </div>
                <div style="padding: 16px; background: #FAF8F5; border-radius: 8px; margin: 16px; font-size: 0.88rem; color: #423B36; border-left: 3px solid #10B981;">
                  <strong>₱{{ formatMoney(stats?.total_sales ?? 0) }} Total GMV Volume</strong>
                  <p style="margin-top: 6px; font-size: 0.82rem; color: #7A6F68;">Financial audit trails, order breakdown, and escrow payouts accessible by CMS administrators.</p>
                </div>
                <div style="padding: 0 16px 16px;">
                  <small style="color: #7A6F68; display: block; margin-bottom: 12px;">Synced to WordPress via <code>craftmarket-reports</code> plugin hook.</small>
                  <a href="http://localhost/craftmarket-cms/wp-admin/admin.php?page=craftmarket-reports" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; text-align: center;">
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

    <!-- ─── MODAL 1: EDIT USER MODAL ─────────────────────────────────────── -->
    <div v-if="showEditUserModal" class="admin-modal-backdrop" @click.self="showEditUserModal = false">
      <div class="admin-modal-box">
        <div class="modal-header">
          <h3>Edit User Account</h3>
          <button type="button" class="btn-modal-close" @click="showEditUserModal = false"></button>
        </div>
        <form @submit.prevent="saveUserEdit" class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" v-model="editUserForm.name" required class="form-control" />
          </div>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" v-model="editUserForm.email" required class="form-control" />
          </div>

          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label">Role</label>
              <select v-model="editUserForm.role" required class="form-select">
                <option value="customer">Customer</option>
                <option value="seller">Seller / Artisan</option>
                <option value="admin">Administrator</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Account Status</label>
              <select v-model="editUserForm.is_active" class="form-select">
                <option :value="true">Active</option>
                <option :value="false">Deactivated</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" v-model="editUserForm.phone" placeholder="+63 9..." class="form-control" />
          </div>

          <div class="form-group">
            <label class="form-label">Delivery / Shop Address</label>
            <textarea v-model="editUserForm.address" rows="2" class="form-control"></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showEditUserModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Saving…' : 'Save Changes' }}
            </button>
          </div>
        </form>
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
                    <span class="status-badge" :class="'badge-' + p.status">{{ p.status }}</span>
                  </td>
                </tr>
                <tr v-if="!activeSeller?.products?.length">
                  <td colspan="5" class="empty-cell">No products currently listed by this artisan maker.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
          <button 
            type="button" 
            class="btn btn-sm" 
            :class="activeSeller?.is_active ? 'btn-danger' : 'btn-primary'"
            @click="toggleSellerActive(activeSeller); if (activeSeller) activeSeller.is_active = !activeSeller.is_active;"
          >
            {{ activeSeller?.is_active ? 'Deactivate Artisan Workshop' : 'Activate Artisan Workshop' }}
          </button>
          <button type="button" class="btn btn-secondary" @click="showSellerDetailsModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- ─── MODAL 3: CREATE / EDIT CATEGORY MODAL ────────────────────────── -->
    <div v-if="showCategoryModal" class="admin-modal-backdrop" @click.self="showCategoryModal = false">
      <div class="admin-modal-box">
        <div class="modal-header">
          <h3>{{ isEditingCategory ? 'Edit Category' : 'Add New Category' }}</h3>
          <button type="button" class="btn-modal-close" @click="showCategoryModal = false"></button>
        </div>
        <form @submit.prevent="saveCategory" class="modal-body">
          <div class="form-group">
            <label class="form-label">Category Name <span class="required">*</span></label>
            <input 
              type="text" 
              v-model="categoryForm.name" 
              @input="onCategoryNameInput" 
              required 
              placeholder="e.g. Traditional Handweaves" 
              class="form-control" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">URL Slug <span class="required">*</span></label>
            <input 
              type="text" 
              v-model="categoryForm.slug" 
              required 
              placeholder="traditional-handweaves" 
              class="form-control" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea 
              v-model="categoryForm.description" 
              rows="2" 
              placeholder="Brief summary of crafts grouped into this category…" 
              class="form-control"
            ></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Image URL</label>
            <input 
              type="url" 
              v-model="categoryForm.image" 
              placeholder="https://images.unsplash.com/..." 
              class="form-control" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <select v-model="categoryForm.is_active" class="form-select">
              <option :value="true">Active (Visible in catalog)</option>
              <option :value="false">Inactive (Hidden)</option>
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCategoryModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="isSavingCategory">
              {{ isSavingCategory ? 'Saving…' : (isEditingCategory ? 'Update Category' : 'Create Category') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── MODAL 4: ORDER DETAILS MODAL ─────────────────────────────────── -->
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
                <span class="status-badge" :class="'badge-' + (activeOrder?.escrow_status || 'held')">
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
                    <span class="status-badge" :class="'badge-' + (item.status || 'pending')">
                      {{ item.status || 'pending' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <label style="font-weight: 600; font-size: 0.85rem; color: #4b5563;">Update Status:</label>
            <select v-model="selectedNewStatus" class="form-select" style="min-width: 175px; padding: 6px 10px; font-size: 0.875rem;">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered (Release Escrow)</option>
              <option value="cancelled">Cancelled</option>
              <option value="refunded">Refunded</option>
            </select>
            <button 
              type="button" 
              class="btn btn-primary btn-sm" 
              :disabled="isUpdatingOrderStatus || selectedNewStatus === activeOrder?.status"
              @click="handleUpdateOrderStatus(activeOrder, selectedNewStatus)"
            >
              <span v-if="isUpdatingOrderStatus">Updating...</span>
              <span v-else>Apply Status</span>
            </button>
          </div>
          <button type="button" class="btn btn-secondary" @click="showOrderModal = false">Close</button>
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

const checkCmsConnection = async () => {
  cmsStatus.value.checking = true;
  try {
    const res = await cmsService.checkHealth();
    cmsStatus.value = { checked: true, online: res.online, checking: false };
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

// Filter States
const userFilter = reactive({ search: '', role: '', is_active: '' });
const sellerFilter = reactive({ search: '', is_active: '' });
const productFilter = reactive({ search: '', category: '', status: '' });
const orderFilter = reactive({ search: '', status: '', payment_status: '' });

// Modals State
const showEditUserModal = ref(false);
const isSaving = ref(false);
const editUserForm = reactive({
  id: null,
  name: '',
  email: '',
  role: 'customer',
  is_active: true,
  phone: '',
  address: ''
});

const showSellerDetailsModal = ref(false);
const activeSeller = ref(null);

const showCategoryModal = ref(false);
const isEditingCategory = ref(false);
const isSavingCategory = ref(false);
const categoryForm = reactive({
  id: null,
  name: '',
  slug: '',
  description: '',
  image: '',
  is_active: true
});

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
    } else if (currentTab.value === 'reports') {
      reports.value = await adminService.getReports();
    } else if (currentTab.value === 'reviews') {
      reviews.value = await adminService.getReviews();
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
    is_active: sellerFilter.is_active !== '' ? sellerFilter.is_active : undefined
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
const openEditUserModal = (u) => {
  editUserForm.id = u.id;
  editUserForm.name = u.name;
  editUserForm.email = u.email;
  editUserForm.role = u.role;
  editUserForm.is_active = Boolean(u.is_active);
  editUserForm.phone = u.phone || '';
  editUserForm.address = u.address || '';
  showEditUserModal.value = true;
};

const saveUserEdit = async () => {
  isSaving.value = true;
  try {
    const res = await adminService.updateUser(editUserForm.id, {
      name: editUserForm.name.trim(),
      email: editUserForm.email.trim(),
      role: editUserForm.role,
      is_active: editUserForm.is_active,
      phone: editUserForm.phone.trim(),
      address: editUserForm.address.trim()
    });

    if (res && res.success) {
      showToast(` User account "${res.data.name}" updated successfully!`, 'alert-success');
      showEditUserModal.value = false;
      await fetchUsers();
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update user.', 'alert-danger');
  } finally {
    isSaving.value = false;
  }
};

const toggleUserActive = async (u) => {
  const actionWord = u.is_active ? 'deactivate' : 'activate';
  if (!confirm(`Are you sure you want to ${actionWord} the account of ${u.name}?`)) return;

  try {
    const res = await adminService.toggleUserActive(u.id);
    if (res && res.success) {
      u.is_active = res.data.is_active;
      showToast(res.message, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update user status.', 'alert-danger');
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

const toggleSellerActive = async (s) => {
  await toggleUserActive(s);
  await fetchSellers();
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

// ─── Category Actions ────────────────────────────────────────────────────────
const openCreateCategoryModal = () => {
  isEditingCategory.value = false;
  categoryForm.id = null;
  categoryForm.name = '';
  categoryForm.slug = '';
  categoryForm.description = '';
  categoryForm.image = '';
  categoryForm.is_active = true;
  showCategoryModal.value = true;
};

const openEditCategoryModal = (cat) => {
  isEditingCategory.value = true;
  categoryForm.id = cat.id;
  categoryForm.name = cat.name;
  categoryForm.slug = cat.slug;
  categoryForm.description = cat.description || '';
  categoryForm.image = cat.image || '';
  categoryForm.is_active = Boolean(cat.is_active);
  showCategoryModal.value = true;
};

const onCategoryNameInput = () => {
  if (!isEditingCategory.value) {
    categoryForm.slug = categoryForm.name
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-');
  }
};

const saveCategory = async () => {
  isSavingCategory.value = true;
  try {
    const payload = {
      name: categoryForm.name.trim(),
      slug: categoryForm.slug.trim(),
      description: categoryForm.description.trim(),
      image: categoryForm.image.trim() || undefined,
      is_active: categoryForm.is_active
    };

    let res;
    if (isEditingCategory.value) {
      res = await adminService.updateCategory(categoryForm.id, payload);
      showToast(` Category "${res.data.name}" updated successfully.`, 'alert-success');
    } else {
      res = await adminService.createCategory(payload);
      showToast(` New category "${res.data.name}" added to catalog taxonomy.`, 'alert-success');
    }

    showCategoryModal.value = false;
    await fetchCategories();
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to save category.', 'alert-danger');
  } finally {
    isSavingCategory.value = false;
  }
};

const handleDeleteCategory = async (cat) => {
  if (cat.products_count > 0) {
    alert(`Cannot Delete Category:\n\n"${cat.name}" is currently associated with ${cat.products_count} active product listing(s).\n\nPlease reassign or delete these products first to prevent orphaned listings.`);
    return;
  }

  if (!confirm(`Are you sure you want to delete category "${cat.name}"?`)) return;

  try {
    const res = await adminService.deleteCategory(cat.id);
    if (res && res.success) {
      categories.value = categories.value.filter(c => c.id !== cat.id);
      showToast(` Category "${cat.name}" deleted.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to delete category.', 'alert-danger');
  }
};

// ─── Order Actions ───────────────────────────────────────────────────────────
const openOrderDetailsModal = (o) => {
  activeOrder.value = o;
  selectedNewStatus.value = o?.status || 'pending';
  showOrderModal.value = true;
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

const handleLogout = async () => {
  if (confirm('Are you sure you want to log out from the Admin Panel?')) {
    await logout();
    router.push('/login');
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
  grid-template-columns: 240px 1fr;
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.metric-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 14px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.02);
  border-left: 4px solid #3e5943;
}
.metric-primary   { border-left-color: #3e5943; }
.metric-forest    { border-left-color: #2f6940; }
.metric-terracotta{ border-left-color: #c25e3e; }
.metric-sand      { border-left-color: #9c8a80; }
.metric-amber     { border-left-color: #f59e0b; }
.metric-gold      { border-left-color: #059669; }

.metric-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #faf6f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
  color: #7a6f68;
}
.metric-primary    .metric-icon { background: #f0f4ff; color: #4a6fa5; }
.metric-forest     .metric-icon { background: #f0faf3; color: #3a7a56; }
.metric-terracotta .metric-icon { background: #fff4f0; color: #b05535; }
.metric-sand       .metric-icon { background: #fdf8f0; color: #9a7a45; }
.metric-amber      .metric-icon { background: #fff9ec; color: #b07800; }
.metric-gold       .metric-icon { background: #f0fdf6; color: #059669; }

.metric-info {
  display: flex;
  flex-direction: column;
}
.metric-label {
  font-size: 0.78rem;
  color: #7a6f68;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.metric-value {
  font-size: 1.55rem;
  font-weight: 700;
  color: #2c2c2c;
  line-height: 1.2;
  margin: 2px 0;
}
.metric-sub {
  font-size: 0.75rem;
  color: #9c8a80;
}

/* Two Column Layout */
.admin-dashboard-two-col {
  display: grid;
  grid-template-columns: 1.35fr 1fr;
  gap: 24px;
}

.admin-card {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.02);
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

/* Badges */
.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.74rem;
  font-weight: 600;
  text-transform: capitalize;
}
.badge-active, .badge-paid, .badge-delivered, .badge-confirmed, .badge-released_to_seller { background: #e8f5e9; color: #2e7d32; }
.badge-pending, .badge-processing, .badge-unpaid, .badge-held { background: #fff8e1; color: #b78103; }
.badge-shipped                                             { background: #e3f2fd; color: #1565c0; }
.badge-inactive, .badge-cancelled, .badge-out_of_stock, .badge-refunded { background: #ffebee; color: #c62828; }

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
  gap: 6px;
  align-items: center;
}
.btn-action {
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.btn-edit       { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
.btn-edit:hover { background: #e5e7eb; }
.btn-view       { background: #e8f4fd; color: #0284c7; border-color: #bae6fd; }
.btn-view:hover { background: #bae6fd; }
.btn-delete     { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
.btn-delete:hover{ background: #ffcdd2; }
.btn-activate   { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
.btn-deactivate { background: #fff8e1; color: #b78103; border-color: #fde68a; }

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
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.95rem;
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
}
.recent-user-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.user-email-text {
  font-size: 0.78rem;
  color: #7a6f68;
}
.recent-user-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
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
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 14px;
}
.status-summary-card {
  background: #faf6f0;
  border: 1px solid #efe8de;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}
.status-count-val {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #2c2c2c;
  margin: 6px 0 2px;
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
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
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
}
.detail-stat span {
  font-size: 0.72rem;
  color: #7a6f68;
  text-transform: uppercase;
}
.detail-stat strong {
  font-size: 1rem;
  color: #2c2c2c;
  margin-top: 2px;
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
</style>