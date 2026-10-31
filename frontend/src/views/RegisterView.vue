<template>
  <main class="auth-page-wrapper">
    <div class="container">
      
      <div class="auth-card-container">
        
        <!-- Notice Banner -->
        <div class="simulation-alert-banner">
          <span class="alert-icon">ℹ️</span>
          <div>
            <strong>Frontend Prototype Notice:</strong>
            <span>This registration uses reactive frontend validation and session modeling. Real password encryption (bcrypt) and database storage will be connected via the upcoming Laravel REST API.</span>
          </div>
        </div>

        <div class="auth-card">
          <div class="auth-card-header">
            <span class="section-eyebrow">JOIN THE MARKETPLACE</span>
            <h1 class="auth-title">Create your Account</h1>
            <p class="auth-subtitle">
              Start exploring authentic local crafts or open an artisan storefront today.
            </p>
          </div>

          <form @submit.prevent="handleRegister" novalidate>
            
            <!-- Account Type Selection -->
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
                placeholder="e.g. Maria Santos"
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
                  placeholder="maria@example.ph"
                >
                <span v-if="errors.email" class="field-error-msg">{{ errors.email }}</span>
              </div>

              <!-- Username -->
              <div class="form-group">
                <label for="username" class="form-label">Username <span class="required">*</span></label>
                <input 
                  type="text" 
                  id="username" 
                  v-model="form.username" 
                  class="form-input" 
                  placeholder="mariacrafts"
                >
                <span v-if="errors.username" class="field-error-msg">{{ errors.username }}</span>
              </div>
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
                  placeholder="Minimum 6 characters"
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
                  placeholder="Re-type your password"
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

            <!-- Status Banner -->
            <div v-if="statusMsg" class="form-alert-msg" :class="statusClass">{{ statusMsg }}</div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-auth-submit">
              <span>Register Account</span>
              <span class="btn-arrow">→</span>
            </button>

          </form>

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

const route = useRoute();
const router = useRouter();

const form = reactive({
  accountType: 'customer',
  fullName: '',
  email: '',
  username: '',
  password: '',
  confirmPassword: '',
  terms: false
});

const errors = reactive({
  fullName: '',
  email: '',
  username: '',
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
  errors.password = '';
  errors.confirmPassword = '';
  errors.terms = '';

  if (!form.fullName.trim()) {
    errors.fullName = 'Full Name is required';
    isValid = false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!form.email.trim() || !emailRegex.test(form.email)) {
    errors.email = 'Please provide a valid email address';
    isValid = false;
  }

  if (form.username.length < 3) {
    errors.username = 'Username must be at least 3 characters';
    isValid = false;
  }

  if (form.password.length < 6) {
    errors.password = 'Password must be at least 6 characters';
    isValid = false;
  }

  if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match';
    isValid = false;
  }

  if (!form.terms) {
    errors.terms = 'You must accept the Terms and Conditions to proceed';
    isValid = false;
  }

  return isValid;
};

const handleRegister = () => {
  if (!validate()) return;

  const newUser = {
    fullName: form.fullName.trim(),
    email: form.email.trim().toLowerCase(),
    username: form.username.trim(),
    role: form.accountType === 'seller' ? 'Seller' : 'Customer',
    phone: '+63 917 555 0192',
    address: 'Metro Manila, Philippines',
    bio: form.accountType === 'seller' ? 'Independent craftmaker and handmade artisan.' : 'Conscious supporter of local handmade crafts.'
  };

  sessionStorage.setItem('craftMarketRegisteredUser', JSON.stringify(newUser));
  statusMsg.value = '✓ Account registered successfully! Redirecting to login...';
  statusClass.value = 'alert-success';

  setTimeout(() => {
    router.push({
      path: '/login',
      query: { registered: 'true', user: form.username }
    });
  }, 1200);
};
</script>
