<template>
  <main class="auth-page-wrapper">
    <div class="container">
      <div class="auth-card-container login-container">

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
            Account registered successfully! You can now log in below.
          </div>

          <form @submit.prevent="handleLogin" novalidate>

            <!-- Email Address -->
            <div class="form-group">
              <label for="loginEmail" class="form-label">Email Address <span class="required">*</span></label>
              <input
                type="email"
                id="loginEmail"
                v-model="email"
                class="form-input"
                :class="{ 'input-error': emailError }"
                placeholder="Enter your email"
                autocomplete="email"
              >
              <span v-if="emailError" class="field-error-msg">{{ emailError }}</span>
            </div>

            <!-- Password -->
            <div class="form-group">
              <div class="label-with-action">
                <label for="loginPassword" class="form-label">Password <span class="required">*</span></label>
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

            <!-- Status Alert Banner -->
            <div v-if="statusMsg" class="form-alert-msg" :class="statusClass">
              {{ statusMsg }}
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-primary btn-auth-submit" :disabled="loading">
              <span v-if="loading">Signing in…</span>
              <span v-else>Login</span>
              <span v-if="!loading" class="btn-arrow">→</span>
            </button>

          </form>


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
import { useAuth } from '../composables/useAuth.js';
import { auth } from '../data/auth.js';

const route  = useRoute();
const router = useRouter();
const { login, loading } = useAuth();

const email    = ref('');
const password = ref('');
const emailError    = ref('');
const passwordError = ref('');
const statusMsg  = ref('');
const statusClass = ref('');
const postRegisterNotice = ref(false);

onMounted(() => {
  if (route.query.registered === 'true') {
    postRegisterNotice.value = true;
  }
  if (route.query.email) {
    email.value = route.query.email;
  }
});

const fillDemo = (demoEmail, demoPass) => {
  email.value    = demoEmail;
  password.value = demoPass;
  emailError.value    = '';
  passwordError.value = '';
  statusMsg.value     = '';
};

const validate = () => {
  let isValid = true;
  emailError.value    = '';
  passwordError.value = '';

  if (!email.value.trim()) {
    emailError.value = 'Email Address is required.';
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

const handleLogin = async () => {
  statusMsg.value = '';
  if (!validate()) return;

  try {
    const user = await login(email.value.trim(), password.value);

    // Sync data/auth.js reactive state so all components reflect the new user immediately
    auth.setUser(user);

    statusMsg.value  = `Welcome back, ${user.name}! Redirecting…`;
    statusClass.value = 'alert-success';

    // Redirect: honour ?redirect= param or go to role-based dashboard immediately
    setTimeout(async () => {
      const redirect = route.query.redirect;
      if (redirect && redirect !== '/login') {
        await router.push(redirect);
      } else if (user.role === 'seller') {
        await router.push('/dashboard/seller');
      } else if (user.role === 'admin') {
        await router.push('/dashboard/admin');
      } else {
        await router.push('/dashboard/customer');
      }
    }, 150);

  } catch (err) {
    statusMsg.value   = err.message || 'Login failed. Please check your credentials.';
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
