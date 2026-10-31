<template>
  <main class="auth-page-wrapper">
    <div class="container">
      
      <div class="auth-card-container login-container">

        <!-- Simulation Notice Banner -->
        <div class="simulation-alert-banner">
          <span class="alert-icon">ℹ️</span>
          <div>
            <strong>Frontend Prototype Simulation:</strong>
            <span>This login demonstrates reactive state management and session persistence. Production-grade token authentication (Laravel Sanctum) and bcrypt password hashing will connect in the upcoming backend phase.</span>
          </div>
        </div>

        <div class="auth-card">
          <div class="auth-card-header">
            <span class="section-eyebrow">WELCOME BACK</span>
            <h1 class="auth-title">Sign In to CraftMarket</h1>
            <p class="auth-subtitle">
              Access your artisan storefront, order history, or profile settings.
            </p>
          </div>

          <!-- Post-Registration Success Notice -->
          <div v-if="postRegisterNotice" class="form-alert-msg alert-success">
            ✓ Account registered successfully! You can now log in below.
          </div>

          <form @submit.prevent="handleLogin" novalidate>
            
            <!-- Email Address or Username -->
            <div class="form-group">
              <label for="loginIdentifier" class="form-label">Email Address or Username <span class="required">*</span></label>
              <input 
                type="text" 
                id="loginIdentifier" 
                v-model="identifier" 
                class="form-input" 
                :class="{ 'input-error': identifierError }"
                placeholder="Enter email or username" 
                autocomplete="username"
              >
              <span v-if="identifierError" class="field-error-msg">{{ identifierError }}</span>
            </div>

            <!-- Password -->
            <div class="form-group">
              <div class="label-with-action">
                <label for="loginPassword" class="form-label">Password <span class="required">*</span></label>
                <a href="#forgot" @click.prevent="handleForgotPassword" class="text-link-sm">Forgot password?</a>
              </div>
              <input 
                type="password" 
                id="loginPassword" 
                v-model="password" 
                class="form-input" 
                :class="{ 'input-error': passwordError }"
                placeholder="Enter your password" 
                autocomplete="current-password"
              >
              <span v-if="passwordError" class="field-error-msg">{{ passwordError }}</span>
            </div>

            <!-- Remember Me -->
            <div class="form-group-inline">
              <label class="checkbox-container">
                <input type="checkbox" v-model="rememberMe">
                <span class="custom-checkbox"></span>
                <span class="checkbox-label-text">Remember this device for 30 days</span>
              </label>
            </div>

            <!-- Status Alert Banner -->
            <div v-if="statusMsg" class="form-alert-msg" :class="statusClass">
              {{ statusMsg }}
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-primary btn-auth-submit">
              <span>Login</span>
              <span class="btn-arrow">→</span>
            </button>

          </form>

          <!-- Quick Demo Accounts for Testing Evaluation -->
          <div class="demo-accounts-box">
            <h4 class="demo-title">🔑 Quick Demo Accounts for Testing:</h4>
            <div class="demo-buttons-grid">
              <button type="button" class="btn-demo-fill" @click="fillDemo('customer@craftmarket.ph', 'password123')">
                <strong>Customer</strong>
                <span>maria_buyer</span>
              </button>
              <button type="button" class="btn-demo-fill" @click="fillDemo('seller@craftmarket.ph', 'password123')">
                <strong>Seller</strong>
                <span>artisan_laguna</span>
              </button>
              <button type="button" class="btn-demo-fill" @click="fillDemo('admin@craftmarket.ph', 'admin123')">
                <strong>Admin</strong>
                <span>admin_craft</span>
              </button>
            </div>
            <p class="demo-hint">Click any role button above to auto-fill sample credentials.</p>
          </div>

          <!-- Footer Link to Register -->
          <div class="auth-card-footer">
            <p>Don't have a CraftMarket account yet? <router-link to="/register" class="auth-switch-link">Create one here ↗</router-link></p>
          </div>

        </div>

      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { auth } from '../data/auth.js';

const route = useRoute();
const router = useRouter();

const identifier = ref('');
const password = ref('');
const rememberMe = ref(false);
const identifierError = ref('');
const passwordError = ref('');
const statusMsg = ref('');
const statusClass = ref('');
const postRegisterNotice = ref(false);

const demoAccounts = [
  {
    email: 'customer@craftmarket.ph',
    username: 'maria_buyer',
    password: 'password123',
    fullName: 'Maria Clara Santos',
    role: 'Customer',
    phone: '+63 917 123 4567',
    address: 'Quezon City, Metro Manila',
    bio: 'Passionate supporter of local indigenous craft and sustainable handmade home accents.'
  },
  {
    email: 'seller@craftmarket.ph',
    username: 'artisan_laguna',
    password: 'password123',
    fullName: 'Studio Clayworks',
    role: 'Seller',
    phone: '+63 918 765 4321',
    address: 'Pila, Laguna, Philippines',
    bio: 'Dedicated to reviving regional handmade pottery using locally sourced Laguna stoneware clay.'
  },
  {
    email: 'admin@craftmarket.ph',
    username: 'admin_craft',
    password: 'admin123',
    fullName: 'Administrator Ashley',
    role: 'Administrator',
    phone: '+63 919 000 1122',
    address: 'CraftMarket Operations Hub, BGC, Taguig City',
    bio: 'Oversees marketplace standards, vendor verification, order dispute resolution, and reports.'
  }
];

onMounted(() => {
  if (route.query.registered === 'true') {
    postRegisterNotice.value = true;
    if (route.query.user) {
      identifier.value = route.query.user;
    }
  }
});

const fillDemo = (demoEmail, demoPass) => {
  identifier.value = demoEmail;
  password.value = demoPass;
  identifierError.value = '';
  passwordError.value = '';
  statusMsg.value = '';
};

const handleForgotPassword = () => {
  alert('Password reset instructions will be handled by Laravel email notifications in the upcoming backend stage.');
};

const validate = () => {
  let isValid = true;
  identifierError.value = '';
  passwordError.value = '';

  if (!identifier.value.trim()) {
    identifierError.value = 'Email Address or Username is required.';
    isValid = false;
  }

  if (!password.value) {
    passwordError.value = 'Password is required.';
    isValid = false;
  } else if (password.value.length < 6) {
    passwordError.value = 'Password must be at least 6 characters.';
    isValid = false;
  }

  return isValid;
};

const handleLogin = () => {
  statusMsg.value = '';
  if (!validate()) return;

  const idQuery = identifier.value.trim().toLowerCase();
  
  // 1. Check against built-in demo accounts
  let matchedUser = demoAccounts.find(
    acc => (acc.email.toLowerCase() === idQuery || acc.username.toLowerCase() === idQuery) && 
           acc.password === password.value
  );

  // 2. Check against custom registered user in sessionStorage
  if (!matchedUser) {
    const rawSaved = sessionStorage.getItem('craftMarketRegisteredUser');
    if (rawSaved) {
      try {
        const saved = JSON.parse(rawSaved);
        if (
          (saved.email.toLowerCase() === idQuery || saved.username.toLowerCase() === idQuery) &&
          password.value.length >= 6
        ) {
          matchedUser = saved;
        }
      } catch (e) {
        console.error(e);
      }
    }
  }

  if (matchedUser) {
    auth.login(matchedUser);
    statusMsg.value = `✓ Welcome back, ${matchedUser.fullName}! Logging in...`;
    statusClass.value = 'alert-success';

    setTimeout(() => {
      router.push('/profile');
    }, 700);
  } else {
    statusMsg.value = 'Invalid login credentials. Click a demo account button below to auto-fill sample credentials.';
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
