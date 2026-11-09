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

          <button
            type="button"
            class="dashboard-nav-item"
            :class="{ active: currentTab === 'cart' }"
            @click="currentTab = 'cart'"
          >
            <span>My Cart ({{ cart.totalCount }})</span>
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

          <!-- Tab 4: Shopping Cart -->
          <div v-else-if="currentTab === 'cart'">
            <div class="dashboard-panel-card">
              <div class="panel-header-row">
                <div>
                  <h3 class="panel-heading">Shopping Cart</h3>
                  <p class="panel-subheading">Manage your selected handcrafted crafts and proceed to secure checkout.</p>
                </div>
                <router-link to="/cart" class="btn btn-secondary btn-sm">Full Cart View ↗</router-link>
              </div>

              <!-- Empty State -->
              <div v-if="cart.items.length === 0" style="text-align: center; padding: 2.5rem 1rem; color: #7A6F68;">
                <div style="font-size: 2.2rem; margin-bottom: 10px;">🛍️</div>
                <h4 style="color: #2C221E; font-size: 1.1rem; margin-bottom: 6px;">Your cart is empty</h4>
                <p style="margin-bottom: 16px; font-size: 0.92rem;">Discover authentic handcrafted pottery, textiles, and woodcrafts to add to your basket.</p>
                <router-link to="/products" class="btn btn-primary btn-sm">Browse Marketplace →</router-link>
              </div>

              <!-- Cart Items List -->
              <div v-else style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
                <div
                  v-for="item in cart.items"
                  :key="item.id || item.productId"
                  style="display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 14px; background: #FAF6F0; border-radius: 8px; border: 1px solid #E8DFD5; flex-wrap: wrap;"
                >
                  <div style="display: flex; align-items: center; gap: 12px; min-width: 200px; flex: 1;">
                    <img
                      :src="item.image || 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=80&q=80'"
                      :alt="item.title || item.name"
                      style="width: 54px; height: 54px; object-fit: cover; border-radius: 6px; flex-shrink: 0; border: 1px solid #E8DFD5;"
                      @error="$event.target.src='https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=80&q=80'"
                    >
                    <div>
                      <strong style="font-size: 0.92rem; display: block; color: #2C221E;">{{ item.title || item.name }}</strong>
                      <span style="font-size: 0.8rem; color: #7A6F68; display: block;">Artisan: {{ item.seller || 'Artisan Maker' }}</span>
                      <span style="font-size: 0.82rem; color: #3E5943; font-weight: 600;">₱{{ Number(item.price).toFixed(2) }} each</span>
                    </div>
                  </div>

                  <!-- Quantity Controls & Subtotal -->
                  <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="display: flex; align-items: center; border: 1px solid #D9CFC4; border-radius: 6px; overflow: hidden; background: #ffffff;">
                      <button 
                        type="button" 
                        @click="cart.updateQuantity(item.id || item.productId, item.quantity - 1)" 
                        style="border: none; background: transparent; padding: 4px 10px; cursor: pointer; font-size: 0.95rem; color: #423B36;"
                        title="Decrease quantity"
                      >−</button>
                      <span style="padding: 4px 8px; font-weight: 600; font-size: 0.88rem; min-width: 24px; text-align: center;">{{ item.quantity }}</span>
                      <button 
                        type="button" 
                        @click="cart.updateQuantity(item.id || item.productId, item.quantity + 1)" 
                        style="border: none; background: transparent; padding: 4px 10px; cursor: pointer; font-size: 0.95rem; color: #423B36;"
                        :disabled="item.quantity >= item.stock"
                        title="Increase quantity"
                      >+</button>
                    </div>

                    <strong style="color: #2C221E; font-size: 0.95rem; min-width: 80px; text-align: right;">
                      ₱{{ ((Number(item.price) || 0) * (Number(item.quantity) || 1)).toFixed(2) }}
                    </strong>

                    <button 
                      type="button" 
                      @click="cart.removeItem(item.id || item.productId)" 
                      style="border: none; background: transparent; color: #C25E3E; cursor: pointer; padding: 4px 8px; font-size: 1rem;"
                      title="Remove from cart"
                    >🗑️</button>
                  </div>
                </div>

                <!-- Cart Footer Summary & Checkout CTA -->
                <div style="margin-top: 10px; padding: 16px 18px; background: #ffffff; border: 1px solid #E8DFD5; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                  <div>
                    <span style="font-size: 0.85rem; color: #7A6F68; display: block;">Estimated Basket Subtotal:</span>
                    <strong style="font-size: 1.35rem; color: #2C221E;">₱{{ cart.subtotal.toFixed(2) }}</strong>
                    <span style="display: block; font-size: 0.8rem; color: #3E5943; margin-top: 2px;">
                      🛡️ Fair-Trade Escrow Guaranteed
                    </span>
                  </div>

                  <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" @click="currentTab = 'checkout'" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                      <span>Proceed to Checkout</span>
                      <span>→</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 5: INTEGRATED ORDER CHECKOUT -->
          <div v-else-if="currentTab === 'checkout'" class="dashboard-panel-card">
            <div class="panel-header-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
              <div>
                <h3 class="panel-heading">Order Checkout & Delivery Details</h3>
                <p class="panel-subheading">Complete your delivery address and confirm payment directly inside your customer portal.</p>
              </div>
              <button type="button" class="btn btn-secondary btn-sm" @click="currentTab = 'cart'">
                ← Back to Cart
              </button>
            </div>

            <!-- Empty Cart Alert -->
            <div v-if="cart.items.length === 0 && !checkoutSuccess" style="text-align: center; padding: 2.5rem 1rem; color: #7A6F68;">
              <div style="font-size: 2.2rem; margin-bottom: 10px;">🛒</div>
              <h4 style="color: #2C221E; font-size: 1.1rem; margin-bottom: 6px;">Your cart is empty</h4>
              <p style="margin-bottom: 16px; font-size: 0.92rem;">Please add handcrafted artisan items before placing an order.</p>
              <button type="button" class="btn btn-primary btn-sm" @click="currentTab = 'overview'">Return to Dashboard</button>
            </div>

            <!-- Order Success Confirmation Screen -->
            <div v-else-if="checkoutSuccess" style="text-align: center; padding: 2rem 1rem;">
              <div style="font-size: 2.8rem; margin-bottom: 10px;">🎉</div>
              <span class="status-pill status-delivered" style="margin-bottom: 8px;">Order Confirmed</span>
              <h3 style="font-size: 1.4rem; color: #2C221E; margin-top: 6px;">Salamat! Your Order has been Placed.</h3>
              <p style="color: #7A6F68; font-size: 0.92rem; margin-bottom: 18px;">
                Order Number: <strong style="color: #C25E3E;">{{ placedOrder?.order_number }}</strong>
              </p>
              <div style="background: #FAF6F0; border: 1px solid #E8DFD5; border-radius: 8px; padding: 16px; max-width: 480px; margin: 0 auto 20px; text-align: left; font-size: 0.88rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span>Payment Method:</span>
                  <strong>{{ placedOrder?.payment_method || 'Cash on Delivery (COD)' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span>Amount Due on Delivery:</span>
                  <strong style="color: #2E7D32;">₱{{ Number(placedOrder?.total_amount || 0).toFixed(2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span>Delivery Address:</span>
                  <span>{{ placedOrder?.shipping_address }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                  <span>Contact Number:</span>
                  <span>{{ placedOrder?.contact_number }}</span>
                </div>
              </div>
              <div style="display: flex; justify-content: center; gap: 12px;">
                <button type="button" class="btn btn-primary btn-sm" @click="viewPlacedOrders">
                  View My Orders →
                </button>
                <button type="button" class="btn btn-secondary btn-sm" @click="currentTab = 'overview'">
                  Back to Dashboard
                </button>
              </div>
            </div>

            <!-- Integrated Checkout Form Grid -->
            <form v-else @submit.prevent="handleDashboardCheckout" style="margin-top: 16px;">
              <div v-if="checkoutAlert" style="padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 0.88rem;" :style="{ background: checkoutAlertError ? '#FDF2F0' : '#E8F5E9', color: checkoutAlertError ? '#C25E3E' : '#2E7D32', border: checkoutAlertError ? '1px solid #F3C5BA' : '1px solid #C8E6C9' }">
                {{ checkoutAlert }}
              </div>

              <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
                <!-- Left: Shipping & Contact Form -->
                <div style="background: #FAF6F0; border: 1px solid #E8DFD5; border-radius: 8px; padding: 20px;">
                  <h4 style="font-size: 1rem; color: #2C221E; margin-bottom: 14px; border-bottom: 1px solid #E8DFD5; padding-bottom: 8px;">
                    1. Delivery Destination & Contact Details
                  </h4>

                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                      <label style="font-size: 0.8rem; font-weight: 600; color: #2C221E; display: block; margin-bottom: 4px;">Recipient Name *</label>
                      <input type="text" v-model="checkoutForm.fullName" required class="form-input" style="width: 100%; padding: 8px 10px; border: 1px solid #D6C8B8; border-radius: 6px; font-size: 0.88rem; box-sizing: border-box;" placeholder="Full Name">
                    </div>
                    <div>
                      <label style="font-size: 0.8rem; font-weight: 600; color: #2C221E; display: block; margin-bottom: 4px;">Contact Number *</label>
                      <input type="tel" v-model="checkoutForm.contactNumber" required class="form-input" style="width: 100%; padding: 8px 10px; border: 1px solid #D6C8B8; border-radius: 6px; font-size: 0.88rem; box-sizing: border-box;" placeholder="+63 917 123 4567">
                    </div>
                  </div>

                  <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #2C221E; display: block; margin-bottom: 4px;">Complete Delivery Address *</label>
                    <textarea v-model="checkoutForm.shippingAddress" rows="3" required class="form-input" style="width: 100%; padding: 8px 10px; border: 1px solid #D6C8B8; border-radius: 6px; font-size: 0.88rem; resize: vertical; box-sizing: border-box;" placeholder="House/Unit No., Street, Barangay, City, Province, Postal Code"></textarea>
                  </div>

                  <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #2C221E; display: block; margin-bottom: 4px;">Delivery Landmark / Notes (Optional)</label>
                    <input type="text" v-model="checkoutForm.notes" class="form-input" style="width: 100%; padding: 8px 10px; border: 1px solid #D6C8B8; border-radius: 6px; font-size: 0.88rem; box-sizing: border-box;" placeholder="e.g. Near yellow gate, call rider upon arrival">
                  </div>

                  <h4 style="font-size: 1rem; color: #2C221E; margin-bottom: 12px; border-bottom: 1px solid #E8DFD5; padding-bottom: 8px;">
                    2. Payment Method
                  </h4>

                  <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border-radius: 6px; border: 1px solid #D6C8B8; background: #ffffff; cursor: pointer;" :style="checkoutPaymentMethod === 'cod' ? { borderColor: '#3E5943', background: '#F0F5F1' } : {}">
                      <input type="radio" v-model="checkoutPaymentMethod" value="cod" style="margin-top: 3px;">
                      <div>
                        <strong style="font-size: 0.9rem; color: #2C221E;">🚚 Cash on Delivery (COD)</strong>
                        <p style="font-size: 0.78rem; color: #7A6F68; margin: 2px 0 0;">Pay directly with exact cash upon receiving and inspecting packages.</p>
                      </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border-radius: 6px; border: 1px solid #D6C8B8; background: #ffffff; cursor: pointer;" :style="checkoutPaymentMethod === 'paymongo' ? { borderColor: '#3E5943', background: '#F0F5F1' } : {}">
                      <input type="radio" v-model="checkoutPaymentMethod" value="paymongo" style="margin-top: 3px;">
                      <div>
                        <strong style="font-size: 0.9rem; color: #2C221E;">💳 Online Payment via PayMongo</strong>
                        <p style="font-size: 0.78rem; color: #7A6F68; margin: 2px 0 0;">Pay securely online with GCash, Maya, credit/debit card, or GrabPay.</p>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Right: Order Items & Subtotal -->
                <div style="background: #FAF6F0; border: 1px solid #E8DFD5; border-radius: 8px; padding: 20px;">
                  <h4 style="font-size: 1rem; color: #2C221E; margin-bottom: 12px; border-bottom: 1px solid #E8DFD5; padding-bottom: 8px;">
                    Order Summary ({{ cart.totalCount }} items)
                  </h4>

                  <div style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
                    <div v-for="item in cart.items" :key="item.id || item.productId" style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #423B36;">
                      <span>{{ item.quantity }} × {{ item.title || item.name }}</span>
                      <strong>₱{{ ((Number(item.price) || 0) * (Number(item.quantity) || 1)).toFixed(2) }}</strong>
                    </div>
                  </div>

                  <div style="border-top: 1px solid #E8DFD5; padding-top: 10px; display: flex; flex-direction: column; gap: 6px; font-size: 0.88rem;">
                    <div style="display: flex; justify-content: space-between; color: #7A6F68;">
                      <span>Items Subtotal</span>
                      <span>₱{{ cart.subtotal.toFixed(2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #7A6F68;">
                      <span>Shipping & Handling</span>
                      <span v-if="cart.subtotal >= 1500" style="color: #2E7D32; font-weight: 700;">FREE (₱0.00)</span>
                      <span v-else>₱150.00</span>
                    </div>
                    <div style="border-top: 1px solid #D6C8B8; margin-top: 6px; padding-top: 8px; display: flex; justify-content: space-between; font-size: 1.05rem;">
                      <strong>Grand Total</strong>
                      <strong style="color: #C25E3E;">₱{{ (cart.subtotal + (cart.subtotal >= 1500 ? 0 : 150)).toFixed(2) }}</strong>
                    </div>
                  </div>

                  <button
                    type="submit"
                    class="btn btn-primary"
                    style="width: 100%; margin-top: 16px; padding: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 6px;"
                    :disabled="isSubmittingCheckout"
                  >
                    <span v-if="isSubmittingCheckout">Placing Order...</span>
                    <span v-else-if="checkoutPaymentMethod === 'paymongo'">Proceed to PayMongo</span>
                    <span v-else>Confirm & Place Order (COD)</span>
                    <span v-if="!isSubmittingCheckout">→</span>
                  </button>

                  <button type="button" @click="currentTab = 'cart'" style="width: 100%; margin-top: 8px; background: none; border: none; font-size: 0.82rem; color: #7A6F68; cursor: pointer; text-decoration: underline;">
                    ← Return to Cart
                  </button>
                </div>
              </div>
            </form>
          </div>

        </section>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { paymentService } from '../services/paymentService.js';
import { useAuth } from '../composables/useAuth.js';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import CustomerOrdersTable from '../components/CustomerOrdersTable.vue';
import { orderService } from '../services/orderService.js';
import { auth } from '../data/auth.js';
import { wishlist } from '../data/wishlist.js';
import { cart } from '../data/cart.js';

const router = useRouter();
const route = useRoute();

const checkoutPaymentMethod = ref('cod');
const isSubmittingCheckout = ref(false);
const checkoutSuccess = ref(false);
const placedOrder = ref(null);
const checkoutAlert = ref('');
const checkoutAlertError = ref(false);

const checkoutForm = reactive({
  fullName: '',
  contactNumber: '',
  shippingAddress: '',
  notes: ''
});

const initCheckoutForm = () => {
  const u = auth.user.value;
  if (u) {
    checkoutForm.fullName = u.fullName || u.name || '';
    checkoutForm.contactNumber = u.phone || u.contact_number || '';
    checkoutForm.shippingAddress = u.address || '';
  }
};

const handleDashboardCheckout = async () => {
  checkoutAlert.value = '';
  checkoutAlertError.value = false;

  if (!checkoutForm.fullName.trim() || !checkoutForm.contactNumber.trim() || !checkoutForm.shippingAddress.trim()) {
    checkoutAlert.value = 'Please provide your Full Name, Contact Phone, and Complete Delivery Address.';
    checkoutAlertError.value = true;
    return;
  }

  if (cart.items.length === 0) {
    checkoutAlert.value = 'Your basket is empty. Please add items to checkout.';
    checkoutAlertError.value = true;
    return;
  }

  isSubmittingCheckout.value = true;
  try {
    const payload = {
      items: cart.items.map(i => ({
        product_id: i.productId || i.id,
        quantity: i.quantity
      })),
      shipping_address: checkoutForm.shippingAddress.trim(),
      contact_number: checkoutForm.contactNumber.trim(),
      notes: checkoutForm.notes.trim() || undefined,
      payment_method: checkoutPaymentMethod.value === 'paymongo'
        ? 'PayMongo Online Payment'
        : 'Cash on Delivery',
    };

    const res = await orderService.createOrder(payload);
    if (!res || !res.success) {
      checkoutAlert.value = res?.message || 'Failed to place order.';
      checkoutAlertError.value = true;
      return;
    }

    const order = res.data;
    if (checkoutPaymentMethod.value === 'paymongo') {
      try {
        const pmRes = await paymentService.createCheckoutSession(order.id);
        if (pmRes && pmRes.success && pmRes.data?.checkout_url) {
          cart.items.splice(0, cart.items.length);
          localStorage.removeItem('craftmarket_local_cart');
          window.location.href = pmRes.data.checkout_url;
          return;
        } else {
          checkoutAlert.value = pmRes?.message || 'Could not start PayMongo session. Your order was saved.';
          checkoutAlertError.value = true;
        }
      } catch (pmErr) {
        checkoutAlert.value = pmErr.response?.data?.message || pmErr.message || 'Payment session failed.';
        checkoutAlertError.value = true;
      }
    } else {
      placedOrder.value = order;
      checkoutSuccess.value = true;
      cart.items.splice(0, cart.items.length);
      localStorage.removeItem('craftmarket_local_cart');
      loadOrders();
    }
  } catch (err) {
    checkoutAlert.value = err.response?.data?.message || err.message || 'Failed to place order.';
    checkoutAlertError.value = true;
  } finally {
    isSubmittingCheckout.value = false;
  }
};

const viewPlacedOrders = () => {
  checkoutSuccess.value = false;
  currentTab.value = 'orders';
  loadOrders();
};
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
  if (route.query.tab) {
    currentTab.value = route.query.tab;
  }
  initCheckoutForm();
  loadOrders();
  wishlist.loadWishlist(true);
  cart.loadCart(true);
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
