<template>
  <main class="profile-page-wrapper">
    <div class="container">
      
      <!-- Breadcrumbs -->
      <div class="breadcrumb-trail">
        <router-link to="/">Home</router-link>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Account Profile</span>
      </div>

      <!-- Page Header -->
      <div class="profile-header-strip">
        <div>
          <span class="section-eyebrow">ACCOUNT MANAGEMENT</span>
          <h1 class="profile-page-title">Personal Profile & Settings</h1>
        </div>
        <button type="button" @click="handleLogout" class="btn btn-secondary btn-logout-main">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sign Out</span>
        </button>
      </div>

      <!-- Main Profile Layout Grid -->
      <div class="profile-layout-grid">
        
        <!-- Left Sidebar: Profile Card & Role Preview -->
        <aside class="profile-sidebar">
          
          <div class="profile-summary-card">
            <div class="profile-avatar-container">
              <img :src="avatarUrl" alt="User Profile Picture" class="profile-avatar-large">
              <button type="button" @click="toggleAvatar" class="avatar-edit-badge" title="Change profile picture">📷</button>
            </div>

            <h2 class="profile-card-name">{{ currentUser.fullName }}</h2>
            <p class="profile-card-username">@{{ currentUser.username }}</p>

            <!-- Role Badge -->
            <div class="role-badge-wrapper">
              <span class="role-badge" :class="roleBadgeClass">{{ currentUser.role }}</span>
            </div>

            <p class="profile-bio-text">{{ currentUser.bio }}</p>

            <div class="profile-meta-list">
              <div class="meta-item">
                <span class="meta-icon">✉️</span>
                <span>{{ currentUser.email }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-icon">📍</span>
                <span>{{ currentUser.address }}</span>
              </div>
            </div>
          </div>

          <!-- Role Permissions & Module Matrix Box -->
          <div class="role-permissions-box">
            <h4 class="perm-title">{{ currentUser.role }} Capabilities</h4>
            <div v-if="currentUser.role === 'Administrator'">
              <p style="font-size: 0.85rem; color: #6b635b; margin-bottom: 10px;">Full system administrative authority:</p>
              <ul style="padding-left: 18px; font-size: 0.82rem; color: #423b36; line-height: 1.6;">
                <li>Platform User & Seller Moderation</li>
                <li>Product Listing Curation & Approvals</li>
                <li>Financial Escrow & Commission Audits</li>
                <li>WordPress CMS Announcement Publishing</li>
              </ul>
            </div>
            <div v-else-if="currentUser.role === 'Seller'">
              <p style="font-size: 0.85rem; color: #6b635b; margin-bottom: 10px;">Artisan Merchant dashboard access:</p>
              <ul style="padding-left: 18px; font-size: 0.82rem; color: #423b36; line-height: 1.6;">
                <li>Add, Edit & Inventory Handmade Products</li>
                <li>View Customer Orders & Tracking Status</li>
                <li>Artisan Workshop Profile Customization</li>
                <li>Direct Bank & GCash Payout Management</li>
              </ul>
            </div>
            <div v-else>
              <p style="font-size: 0.85rem; color: #6b635b; margin-bottom: 10px;">Verified Shopper capabilities:</p>
              <ul style="padding-left: 18px; font-size: 0.82rem; color: #423b36; line-height: 1.6;">
                <li>Curated Marketplace Browsing & Search</li>
                <li>Escrow-Protected Shopping Cart Checkout</li>
                <li>Order Tracking with Delivery Confirmation</li>
                <li>Artisan Direct Reviews & Ratings</li>
              </ul>
            </div>
          </div>

        </aside>

        <!-- Right Main Content: Edit Details & Security Settings -->
        <section class="profile-main-content">
          
          <!-- Status Alert Banner -->
          <div v-if="alertMsg" class="form-alert-msg" :class="alertClass">
            {{ alertMsg }}
          </div>

          <!-- 1. Personal Information Card -->
          <div class="content-panel-card">
            <div class="panel-card-header">
              <div>
                <h3 class="panel-title">Personal Information</h3>
                <p class="panel-subtitle">Update your personal identification and delivery contact details.</p>
              </div>
              <button type="button" @click="toggleEdit" class="btn btn-secondary btn-sm">
                <span>{{ isEditing ? 'Cancel Edit' : 'Edit Profile' }}</span>
              </button>
            </div>

            <form @submit.prevent="saveProfile">
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="editFullName" class="form-label">Full Name <span class="required">*</span></label>
                  <input 
                    type="text" 
                    id="editFullName" 
                    v-model="editForm.fullName" 
                    class="form-input" 
                    :disabled="!isEditing" 
                    required
                  >
                </div>

                <div class="form-group">
                  <label for="editUsername" class="form-label">Username <span class="required">*</span></label>
                  <input 
                    type="text" 
                    id="editUsername" 
                    v-model="editForm.username" 
                    class="form-input" 
                    :disabled="!isEditing" 
                    required
                  >
                </div>
              </div>

              <div class="form-row-2col">
                <div class="form-group">
                  <label for="editEmail" class="form-label">Email Address <span class="required">*</span></label>
                  <input 
                    type="email" 
                    id="editEmail" 
                    v-model="editForm.email" 
                    class="form-input" 
                    :disabled="!isEditing" 
                    required
                  >
                </div>

                <div class="form-group">
                  <label for="editPhone" class="form-label">Phone Number</label>
                  <input 
                    type="tel" 
                    id="editPhone" 
                    v-model="editForm.phone" 
                    class="form-input" 
                    :disabled="!isEditing"
                  >
                </div>
              </div>

              <div class="form-group">
                <label for="editAddress" class="form-label">Shipping / Workshop Address</label>
                <input 
                  type="text" 
                  id="editAddress" 
                  v-model="editForm.address" 
                  class="form-input" 
                  :disabled="!isEditing"
                >
              </div>

              <div class="form-group">
                <label for="editBio" class="form-label">About / Craft Bio</label>
                <textarea 
                  id="editBio" 
                  v-model="editForm.bio" 
                  class="form-input textarea-bio" 
                  rows="3" 
                  :disabled="!isEditing"
                ></textarea>
              </div>

              <!-- Form Actions (Visible when editing) -->
              <div v-if="isEditing" class="edit-actions-row">
                <button type="button" @click="cancelEdit" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </div>
            </form>
          </div>

          <!-- 2. Change Password Card -->
          <div class="content-panel-card">
            <div class="panel-card-header">
              <div>
                <h3 class="panel-title">Change Password</h3>
                <p class="panel-subtitle">Update your account login credentials for security.</p>
              </div>
            </div>

            <form @submit.prevent="updatePassword">
              <div class="form-group">
                <label for="currentPassword" class="form-label">Current Password <span class="required">*</span></label>
                <input 
                  type="password" 
                  id="currentPassword" 
                  v-model="passwords.current" 
                  class="form-input" 
                  placeholder="Enter current password" 
                  required
                >
              </div>

              <div class="form-row-2col">
                <div class="form-group">
                  <label for="newPassword" class="form-label">New Password <span class="required">*</span></label>
                  <input 
                    type="password" 
                    id="newPassword" 
                    v-model="passwords.newPass" 
                    class="form-input" 
                    placeholder="Min. 6 characters" 
                    required
                  >
                </div>

                <div class="form-group">
                  <label for="confirmNewPassword" class="form-label">Confirm New Password <span class="required">*</span></label>
                  <input 
                    type="password" 
                    id="confirmNewPassword" 
                    v-model="passwords.confirmPass" 
                    class="form-input" 
                    placeholder="Re-type new password" 
                    required
                  >
                </div>
              </div>

              <button type="submit" class="btn btn-secondary">
                Update Password
              </button>
            </form>
          </div>

        </section>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../data/auth.js';

const router = useRouter();
const isEditing = ref(false);
const alertMsg = ref('');
const alertClass = ref('');
const avatarIndex = ref(0);

const avatarUrls = [
  'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
  'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80'
];

const avatarUrl = computed(() => avatarUrls[avatarIndex.value]);

const toggleAvatar = () => {
  avatarIndex.value = (avatarIndex.value + 1) % avatarUrls.length;
};

// Fallback user if not logged in
const defaultUser = {
  fullName: 'Maria Clara Santos',
  username: 'maria_buyer',
  role: 'Customer',
  email: 'customer@craftmarket.ph',
  phone: '+63 917 123 4567',
  address: 'Quezon City, Metro Manila, Philippines',
  bio: 'Passionate supporter of local indigenous craft and sustainable handmade home accents.'
};

const currentUser = computed(() => auth.user.value || defaultUser);

const editForm = reactive({ ...currentUser.value });

onMounted(() => {
  Object.assign(editForm, currentUser.value);
});

const roleBadgeClass = computed(() => {
  if (currentUser.value.role === 'Administrator') return 'role-badge-admin';
  if (currentUser.value.role === 'Seller') return 'role-badge-seller';
  return 'role-badge-customer';
});

const toggleEdit = () => {
  isEditing.value = !isEditing.value;
  if (!isEditing.value) {
    Object.assign(editForm, currentUser.value);
  }
};

const cancelEdit = () => {
  isEditing.value = false;
  Object.assign(editForm, currentUser.value);
};

const saveProfile = () => {
  if (!editForm.fullName.trim() || !editForm.email.trim() || !editForm.username.trim()) {
    alertMsg.value = 'Full Name, Username, and Email are required.';
    alertClass.value = 'alert-danger';
    return;
  }

  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(editForm.email.trim())) {
    alertMsg.value = 'Please provide a valid email format.';
    alertClass.value = 'alert-danger';
    return;
  }

  auth.updateProfile({ ...editForm });
  isEditing.value = false;
  alertMsg.value = '✓ Profile details updated successfully!';
  alertClass.value = 'alert-success';
  setTimeout(() => { alertMsg.value = ''; }, 3200);
};

const passwords = reactive({
  current: '',
  newPass: '',
  confirmPass: ''
});

const updatePassword = () => {
  alertMsg.value = '';

  if (!passwords.current) {
    alertMsg.value = 'Current password is required.';
    alertClass.value = 'alert-danger';
    return;
  }

  if (passwords.newPass.length < 6) {
    alertMsg.value = 'New password must be at least 6 characters.';
    alertClass.value = 'alert-danger';
    return;
  }

  if (passwords.newPass !== passwords.confirmPass) {
    alertMsg.value = 'New password and confirm password do not match.';
    alertClass.value = 'alert-danger';
    return;
  }

  passwords.current = '';
  passwords.newPass = '';
  passwords.confirmPass = '';
  alertMsg.value = '✓ Password updated successfully in session!';
  alertClass.value = 'alert-success';
  setTimeout(() => { alertMsg.value = ''; }, 3200);
};

const handleLogout = () => {
  auth.logout();
  router.push('/login');
};
</script>
