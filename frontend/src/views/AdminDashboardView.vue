<template>
  <main class="dashboard-page-wrapper admin-dashboard-wrapper">
    <div class="container-fluid admin-container">
      
      <!-- Top Welcome Banner -->
      <div class="dashboard-header-banner admin-header-banner">
        <div class="dashboard-welcome">
          <span class="section-eyebrow">CENTRAL CONTROL PANEL</span>
          <h1>Marketplace Administration 🛡️</h1>
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
            <span class="nav-item-icon">📊</span>
            <span>Dashboard</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'users' }"
            @click="setTab('users')"
          >
            <span class="nav-item-icon">👥</span>
            <span>Users ({{ stats?.total_users ?? users.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'sellers' }"
            @click="setTab('sellers')"
          >
            <span class="nav-item-icon">🏪</span>
            <span>Sellers ({{ stats?.total_sellers ?? sellers.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'products' }"
            @click="setTab('products')"
          >
            <span class="nav-item-icon">🎨</span>
            <span>Products ({{ stats?.total_products ?? products.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'categories' }"
            @click="setTab('categories')"
          >
            <span class="nav-item-icon">🏷️</span>
            <span>Categories ({{ categories.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'orders' }"
            @click="setTab('orders')"
          >
            <span class="nav-item-icon">📦</span>
            <span>Orders ({{ stats?.total_orders ?? orders.length }})</span>
          </button>

          <button 
            type="button" 
            class="dashboard-nav-item" 
            :class="{ active: currentTab === 'reports' }"
            @click="setTab('reports')"
          >
            <span class="nav-item-icon">📈</span>
            <span>Reports</span>
          </button>

          <hr class="sidebar-divider" />

          <router-link to="/" class="dashboard-nav-item">
            <span class="nav-item-icon">🛍️</span>
            <span>Marketplace Storefront</span>
          </router-link>

          <button 
            type="button" 
            class="dashboard-nav-item btn-logout" 
            @click="handleLogout"
          >
            <span class="nav-item-icon">🚪</span>
            <span>Sign Out</span>
          </button>
        </aside>

        <!-- Main Content Canvas -->
        <section class="dashboard-main-area admin-main-area">

          <!-- Global Feedback Message -->
          <div v-if="feedbackMsg" class="admin-alert" :class="feedbackClass">
            <span>{{ feedbackMsg }}</span>
            <button type="button" class="btn-close-alert" @click="feedbackMsg = ''">✕</button>
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
                <div class="metric-icon">👥</div>
                <div class="metric-info">
                  <span class="metric-label">Total Users</span>
                  <strong class="metric-value">{{ stats?.total_users ?? 0 }}</strong>
                  <small class="metric-sub">{{ stats?.total_customers ?? 0 }} Customers · {{ stats?.total_sellers ?? 0 }} Sellers</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">🏪</div>
                <div class="metric-info">
                  <span class="metric-label">Registered Artisans</span>
                  <strong class="metric-value">{{ stats?.total_sellers ?? 0 }}</strong>
                  <small class="metric-sub">Local craft workshops</small>
                </div>
              </div>

              <div class="metric-card metric-terracotta">
                <div class="metric-icon">🛍️</div>
                <div class="metric-info">
                  <span class="metric-label">Active Customers</span>
                  <strong class="metric-value">{{ stats?.total_customers ?? 0 }}</strong>
                  <small class="metric-sub">Registered buyers</small>
                </div>
              </div>

              <div class="metric-card metric-sand">
                <div class="metric-icon">🎨</div>
                <div class="metric-info">
                  <span class="metric-label">Catalog Products</span>
                  <strong class="metric-value">{{ stats?.total_products ?? 0 }}</strong>
                  <small class="metric-sub">Handmade listings</small>
                </div>
              </div>

              <div class="metric-card metric-amber">
                <div class="metric-icon">📦</div>
                <div class="metric-info">
                  <span class="metric-label">Total Orders</span>
                  <strong class="metric-value">{{ stats?.total_orders ?? 0 }}</strong>
                  <small class="metric-sub">{{ stats?.orders_pending ?? 0 }} pending fulfillment</small>
                </div>
              </div>

              <div class="metric-card metric-gold">
                <div class="metric-icon">💰</div>
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
                    {{ act.type === 'order' ? '📦' : act.type === 'user' ? '👤' : '🎨' }}
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
                  <p class="admin-card-sub">Inspect, filter, update details, or toggle active status of all accounts.</p>
                </div>
              </div>

              <!-- Filter Toolbar -->
              <div class="admin-toolbar">
                <div class="search-input-box">
                  <span class="search-icon">🔍</span>
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
                          <button type="button" class="btn-action btn-edit" @click="openEditUserModal(u)" title="Edit User Details">
                            ✏️ Edit
                          </button>
                          <button 
                            type="button" 
                            class="btn-action" 
                            :class="u.is_active ? 'btn-deactivate' : 'btn-activate'"
                            @click="toggleUserActive(u)"
                            :title="u.is_active ? 'Deactivate account' : 'Reactivate account'"
                          >
                            {{ u.is_active ? '🚫 Deactivate' : '✓ Activate' }}
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
                  <span class="search-icon">🔍</span>
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
                            👁️ View Shop
                          </button>
                          <button 
                            type="button" 
                            class="btn-action" 
                            :class="s.is_active ? 'btn-deactivate' : 'btn-activate'"
                            @click="toggleSellerActive(s)"
                          >
                            {{ s.is_active ? '🚫 Deactivate' : '✓ Activate' }}
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
                  <span class="search-icon">🔍</span>
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
                        <select 
                          :value="p.status" 
                          @change="updateProductStatus(p, $event.target.value)"
                          class="status-select"
                          :class="'status-' + p.status"
                        >
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                          <option value="out_of_stock">Out of Stock</option>
                        </select>
                      </td>
                      <td>
                        <div class="action-buttons-group">
                          <router-link :to="'/products/' + p.id" target="_blank" class="btn-action btn-view" title="View product page in new tab">
                            ↗ View
                          </router-link>
                          <button type="button" class="btn-action btn-delete" @click="handleDeleteProduct(p)" title="Remove product listing from marketplace">
                            🗑️ Remove
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
                            ✏️ Edit
                          </button>
                          <button 
                            type="button" 
                            class="btn-action btn-delete" 
                            @click="handleDeleteCategory(c)"
                            :title="c.products_count > 0 ? 'Cannot delete: Category has active products' : 'Delete category'"
                          >
                            🗑️ Delete
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
                  <span class="search-icon">🔍</span>
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
                          {{ o.payment_status === 'paid' ? '✓ Paid' : '⏳ Unpaid' }}
                        </span>
                      </td>
                      <td>
                        <select 
                          :value="o.status" 
                          @change="handleUpdateOrderStatus(o, $event.target.value)"
                          class="status-select"
                          :class="'status-' + o.status"
                        >
                          <option value="pending">Pending</option>
                          <option value="confirmed">Confirmed</option>
                          <option value="processing">Processing</option>
                          <option value="shipped">Shipped</option>
                          <option value="delivered">Delivered</option>
                          <option value="cancelled">Cancelled</option>
                        </select>
                      </td>
                      <td class="text-muted">{{ formatDate(o.created_at) }}</td>
                      <td>
                        <button type="button" class="btn-action btn-view" @click="openOrderDetailsModal(o)">
                          👁️ Details
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
                <div class="metric-icon">💵</div>
                <div class="metric-info">
                  <span class="metric-label">Total Realized Revenue</span>
                  <strong class="metric-value">₱{{ formatMoney(reports?.total_sales ?? 0) }}</strong>
                  <small class="metric-sub">Paid & delivered artisan orders</small>
                </div>
              </div>

              <div class="metric-card metric-primary">
                <div class="metric-icon">📊</div>
                <div class="metric-info">
                  <span class="metric-label">All-time Orders</span>
                  <strong class="metric-value">{{ reports?.total_orders ?? 0 }}</strong>
                  <small class="metric-sub">Recorded in MySQL database</small>
                </div>
              </div>

              <div class="metric-card metric-forest">
                <div class="metric-icon">📦</div>
                <div class="metric-info">
                  <span class="metric-label">Average Order Value</span>
                  <strong class="metric-value">
                    ₱{{ formatMoney(reports?.total_orders ? (reports.total_sales / reports.total_orders) : 0) }}
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

        </section>
      </div>

    </div>

    <!-- ─── MODAL 1: EDIT USER MODAL ─────────────────────────────────────── -->
    <div v-if="showEditUserModal" class="admin-modal-backdrop" @click.self="showEditUserModal = false">
      <div class="admin-modal-box">
        <div class="modal-header">
          <h3>Edit User Account</h3>
          <button type="button" class="btn-modal-close" @click="showEditUserModal = false">✕</button>
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
          <button type="button" class="btn-modal-close" @click="showSellerDetailsModal = false">✕</button>
        </div>

        <div class="modal-body">
          <div class="seller-details-overview-grid">
            <div class="detail-stat">
              <span>Status</span>
              <strong :class="activeSeller?.is_active ? 'text-success' : 'text-danger'">
                {{ activeSeller?.is_active ? '✓ Active Maker' : '🚫 Deactivated' }}
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

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showSellerDetailsModal = false">Close</button>
        </div>
      </div>
    </div>

    <!-- ─── MODAL 3: CREATE / EDIT CATEGORY MODAL ────────────────────────── -->
    <div v-if="showCategoryModal" class="admin-modal-backdrop" @click.self="showCategoryModal = false">
      <div class="admin-modal-box">
        <div class="modal-header">
          <h3>{{ isEditingCategory ? 'Edit Category' : 'Add New Category' }}</h3>
          <button type="button" class="btn-modal-close" @click="showCategoryModal = false">✕</button>
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
          <button type="button" class="btn-modal-close" @click="showOrderModal = false">✕</button>
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

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showOrderModal = false">Close</button>
        </div>
      </div>
    </div>

  </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { adminService } from '../services/adminService.js';
import { auth } from '../data/auth.js';
import { useAuth } from '../composables/useAuth.js';

const route = useRoute();
const router = useRouter();
const { logout } = useAuth();

// Current active sidebar tab
const currentTab = ref(route.query.tab || 'dashboard');

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
      showToast(`✓ User account "${res.data.name}" updated successfully!`, 'alert-success');
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
      showToast(`✓ Listing status for "${product.name}" set to ${newStatus}.`, 'alert-success');
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
      showToast(`✓ Product listing "${product.name}" removed successfully.`, 'alert-success');
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
      showToast(`✓ Category "${res.data.name}" updated successfully.`, 'alert-success');
    } else {
      res = await adminService.createCategory(payload);
      showToast(`✓ New category "${res.data.name}" added to catalog taxonomy.`, 'alert-success');
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
    alert(`⚠️ Cannot Delete Category:\n\n"${cat.name}" is currently associated with ${cat.products_count} active product listing(s).\n\nPlease reassign or delete these products first to prevent orphaned listings.`);
    return;
  }

  if (!confirm(`Are you sure you want to delete category "${cat.name}"?`)) return;

  try {
    const res = await adminService.deleteCategory(cat.id);
    if (res && res.success) {
      categories.value = categories.value.filter(c => c.id !== cat.id);
      showToast(`✓ Category "${cat.name}" deleted.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to delete category.', 'alert-danger');
  }
};

// ─── Order Actions ───────────────────────────────────────────────────────────
const openOrderDetailsModal = (o) => {
  activeOrder.value = o;
  showOrderModal.value = true;
};

const handleUpdateOrderStatus = async (order, newStatus) => {
  try {
    const res = await adminService.updateOrderStatus(order.id, newStatus);
    if (res && res.success) {
      order.status = newStatus;
      showToast(`✓ Order ${order.order_number} status updated to ${newStatus}.`, 'alert-success');
    }
  } catch (err) {
    showToast(err.response?.data?.message || err.message || 'Failed to update order status.', 'alert-danger');
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
}

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
}
.admin-table {
  width: 100%;
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
.badge-active, .badge-paid, .badge-delivered, .badge-confirmed { background: #e8f5e9; color: #2e7d32; }
.badge-pending, .badge-processing, .badge-unpaid            { background: #fff8e1; color: #b78103; }
.badge-shipped                                             { background: #e3f2fd; color: #1565c0; }
.badge-inactive, .badge-cancelled, .badge-out_of_stock     { background: #ffebee; color: #c62828; }

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
}
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
</style>
