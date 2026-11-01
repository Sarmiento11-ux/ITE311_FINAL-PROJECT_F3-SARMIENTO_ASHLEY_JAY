<template>
  <main class="auth-page-wrapper">
    <div class="container">
      
      <div class="auth-card-container">

        <div class="auth-card">
          <div class="auth-card-header">
            <span class="section-eyebrow">JOIN THE MARKETPLACE</span>
            <h1 class="auth-title">Create your Account</h1>
            <p class="auth-subtitle">
              Start exploring authentic local crafts or open an artisan storefront today.
            </p>
          </div>

          <form @submit.prevent="handleRegister" novalidate>
            
            <!-- Account Type Selection (Customer vs Seller) -->
            <div class="form-group">
              <label class="form-label">I want to join as a:</label>
              <div class="role-selector-grid">
                <label class="role-card-option">
                  <input type="radio" v-model="form.accountType" value="customer" id="roleCustomer">
                  <div class="role-card-content">
                    <div class="role-icon">🛍️</div>
                    <div>
                      <strong class="role-name">Customer / Shopper</strong>
                      <p class="role-desc">Browse & buy handcrafted goods</p>
                    </div>
                  </div>
                </label>

                <label class="role-card-option">
                  <input type="radio" v-model="form.accountType" value="seller" id="roleSeller">
                  <div class="role-card-content">
                    <div class="role-icon">🎨</div>
                    <div>
                      <strong class="role-name">Artisan / Seller</strong>
                      <p class="role-desc">Showcase & sell handmade creations</p>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Full Name -->
            <div class="form-group">
              <label for="fullName" class="form-label">Full Name <span class="required">*</span></label>
              <input 
                type="text" 
                id="fullName" 
                v-model="form.fullName" 
                class="form-input" 
                :class="{ 'input-error': errors.fullName }"
                placeholder="e.g. Maria Santos"
                autocomplete="name"
              >
              <span v-if="errors.fullName" class="field-error-msg">{{ errors.fullName }}</span>
            </div>

            <div class="form-row-2col">
              <!-- Email Address -->
              <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                <input 
                  type="email" 
                  id="email" 
                  v-model="form.email" 
                  class="form-input" 
                  :class="{ 'input-error': errors.email }"
                  placeholder="maria@example.ph"
                  autocomplete="email"
                >
                <span v-if="errors.email" class="field-error-msg">{{ errors.email }}</span>
              </div>

              <!-- Username (optional display identifier) -->
              <div class="form-group">
                <label for="username" class="form-label">Username <span class="required">*</span></label>
                <input 
                  type="text" 
                  id="username" 
                  v-model="form.username" 
                  class="form-input" 
                  :class="{ 'input-error': errors.username }"
                  placeholder="mariacrafts"
                  autocomplete="username"
                >
                <span v-if="errors.username" class="field-error-msg">{{ errors.username }}</span>
              </div>
            </div>

            <!-- Seller Shop Name (if seller role selected) -->
            <div v-if="form.accountType === 'seller'" class="form-group">
              <label for="shopName" class="form-label">Storefront / Shop Name <span class="required">*</span></label>
              <input 
                type="text" 
                id="shopName" 
                v-model="form.shopName" 
                class="form-input" 
                :class="{ 'input-error': errors.shopName }"
                placeholder="e.g. Cordillera Weaves Studio"
              >
              <span v-if="errors.shopName" class="field-error-msg">{{ errors.shopName }}</span>
            </div>

            <div class="form-row-2col">
              <!-- Password -->
              <div class="form-group">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <input 
                  type="password" 
                  id="password" 
                  v-model="form.password" 
                  class="form-input" 
                  :class="{ 'input-error': errors.password }"
                  placeholder="Minimum 6 characters"
                  autocomplete="new-password"
                >
                <span v-if="errors.password" class="field-error-msg">{{ errors.password }}</span>
              </div>

              <!-- Confirm Password -->
              <div class="form-group">
                <label for="confirmPassword" class="form-label">Confirm Password <span class="required">*</span></label>
                <input 
                  type="password" 
                  id="confirmPassword" 
                  v-model="form.confirmPassword" 
                  class="form-input" 
                  :class="{ 'input-error': errors.confirmPassword }"
                  placeholder="Re-type your password"
                  autocomplete="new-password"
                >
                <span v-if="errors.confirmPassword" class="field-error-msg">{{ errors.confirmPassword }}</span>
              </div>
            </div>

            <!-- Terms and Conditions Checkbox -->
            <div class="form-group terms-group">
              <label class="checkbox-container">
                <input type="checkbox" v-model="form.terms" id="termsCheck">
                <span class="custom-checkbox"></span>
                <span class="checkbox-label-text">
                  I agree to the <a href="#terms" class="legal-link">Terms and Conditions</a> and <a href="#privacy" class="legal-link">Privacy Policy</a> of CraftMarket.
                </span>
              </label>
              <span v-if="errors.terms" class="field-error-msg">{{ errors.terms }}</span>
            </div>

            <!-- Submission Status Banner -->
            <div v-if="statusMsg" class="form-alert-msg" :class="statusClass">
              {{ statusMsg }}
            </div>

            <!-- Create Account Button -->
            <button type="submit" class="btn btn-primary btn-auth-submit" :disabled="loading">
              <span v-if="loading">Creating Account…</span>
              <span v-else>Create Account</span>
              <span v-if="!loading" class="btn-arrow">→</span>
            </button>

          </form>

          <!-- Footer Link to Login -->
          <div class="auth-card-footer">
            <p>Already have a CraftMarket account? <router-link to="/login" class="auth-switch-link">Sign In here ↗</router-link></p>
          </div>

        </div>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';

const route = useRoute();
const router = useRouter();
const { register, loading } = useAuth();

const form = reactive({
  accountType: 'customer',
  fullName: '',
  email: '',
  username: '',
  shopName: '',
  password: '',
  confirmPassword: '',
  terms: false
});

const errors = reactive({
  fullName: '',
  email: '',
  username: '',
  shopName: '',
  password: '',
  confirmPassword: '',
  terms: ''
});

const statusMsg = ref('');
const statusClass = ref('');

onMounted(() => {
  if (route.query.role === 'seller') {
    form.accountType = 'seller';
  }
});

const validate = () => {
  let isValid = true;
  errors.fullName = '';
  errors.email = '';
  errors.username = '';
  errors.shopName = '';
  errors.password = '';
  errors.confirmPassword = '';
  errors.terms = '';

  // 1. Full name validation
  if (!form.fullName.trim()) {
    errors.fullName = 'Full Name is required.';
    isValid = false;
  }

  // 2. Email format validation
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!form.email.trim()) {
    errors.email = 'Email Address is required.';
    isValid = false;
  } else if (!emailRegex.test(form.email.trim())) {
    errors.email = 'Please enter a valid email format (e.g. maria@example.ph).';
    isValid = false;
  }

  // 3. Username validation
  if (!form.username.trim()) {
    errors.username = 'Username is required.';
    isValid = false;
  } else if (form.username.trim().length < 3) {
    errors.username = 'Username must be at least 3 characters.';
    isValid = false;
  }

  // 4. Shop name for sellers
  if (form.accountType === 'seller' && !form.shopName.trim()) {
    errors.shopName = 'Storefront name is required for artisans.';
    isValid = false;
  }

  // 5. Password length validation
  if (!form.password) {
    errors.password = 'Password is required.';
    isValid = false;
  } else if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters.';
    isValid = false;
  }

  // 6. Password confirmation match
  if (!form.confirmPassword) {
    errors.confirmPassword = 'Please confirm your password.';
    isValid = false;
  } else if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match.';
    isValid = false;
  }

  // 7. Terms validation
  if (!form.terms) {
    errors.terms = 'You must accept the Terms and Conditions to proceed.';
    isValid = false;
  }

  return isValid;
};

const handleRegister = async () => {
  statusMsg.value = '';
  if (!validate()) return;

  try {
    const payload = {
      name: form.fullName.trim(),
      email: form.email.trim().toLowerCase(),
      password: form.password,
      password_confirmation: form.confirmPassword,
      role: form.accountType,
      shop_name: form.accountType === 'seller' ? form.shopName.trim() : undefined
    };

    const user = await register(payload);

    statusMsg.value = `✔ Account registered successfully! Welcome, ${user.name}!`;
    statusClass.value = 'alert-success';

    setTimeout(() => {
      if (user.role === 'seller') {
        router.push('/dashboard/seller');
      } else {
        router.push('/dashboard/customer');
      }
    }, 1000);
  } catch (err) {
    statusMsg.value = err.message || 'Registration failed. Please check inputs and try again.';
    statusClass.value = 'alert-danger';
  }
};
</script>

<style scoped>
.input-error {
  border-color: #c25e3e !important;
  background-color: #fffaf8 !important;
}
</style>
