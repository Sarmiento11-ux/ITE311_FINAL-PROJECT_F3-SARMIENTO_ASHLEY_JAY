import { reactive, computed } from 'vue';
import { cart } from '../data/cart.js';
import { wishlist } from '../data/wishlist.js';
import { auth } from '../data/auth.js';
import { authService } from '../services/authService.js';

// ─── Reactive global auth state ───────────────────────────────────────────────
// Components read from here; authService handles all API communication.
// `data/auth.js` keeps a normalized mirror of the user for header/nav
// components, so every session change is synced there as well.

const state = reactive({
  user: authService.getUser(),   // hydrate from sessionStorage on page load
  loading: false,
  error: null,
});

export const useAuth = () => {
  // ─── Computed ────────────────────────────────────────────────────────────
  const user            = computed(() => state.user);
  const isAuthenticated = computed(() => !!state.user && !!authService.getToken());
  const role            = computed(() => state.user?.role || null);
  const isCustomer      = computed(() => state.user?.role === 'customer');
  const isSeller        = computed(() => state.user?.role === 'seller');
  const isAdmin         = computed(() => state.user?.role === 'admin');
  const loading         = computed(() => state.loading);
  const error           = computed(() => state.error);

  // ─── Actions ─────────────────────────────────────────────────────────────

  /**
   * Login — calls API, saves token, updates reactive state.
   */
  async function login(email, password) {
    state.loading = true;
    state.error   = null;
    try {
      const { user } = await authService.login(email, password);
      state.user = user;
      auth.setUser(user);
      cart.loadCart(true);
      wishlist.loadWishlist(true);
      return user;
    } catch (err) {
      const msg = err.response?.data?.message || err.userFriendlyMessage || err.message || 'Login failed. Please try again.';
      state.error = msg;
      throw new Error(msg);
    } finally {
      state.loading = false;
    }
  }

  /**
   * Register — calls API, saves token, updates reactive state.
   */
  async function register(data) {
    state.loading = true;
    state.error   = null;
    try {
      const { user } = await authService.register(data);
      state.user = user;
      auth.setUser(user);
      return user;
    } catch (err) {
      const msg = err.response?.data?.message
        || Object.values(err.response?.data?.errors || {}).flat().join(' ')
        || 'Registration failed. Please try again.';
      state.error = msg;
      throw new Error(msg);
    } finally {
      state.loading = false;
    }
  }

  /**
   * Logout — revokes token on server, clears state.
   */
  async function logout() {
    state.loading = true;
    try {
      await authService.logout();
    } finally {
      state.user    = null;
      state.error   = null;
      state.loading = false;
      auth.setUser(null);
      cart.reset();
      wishlist.reset();
    }
  }

  /**
   * Refresh user data from server (useful after profile updates).
   */
  async function refreshUser() {
    if (!authService.isAuthenticated()) return;
    try {
      const user = await authService.fetchMe();
      state.user = user;
      auth.setUser(user);
    } catch {
      // Token may have expired — clear session
      await logout();
    }
  }

  /**
   * Update profile in-place.
   */
  async function updateProfile(data) {
    state.loading = true;
    state.error   = null;
    try {
      const user = await authService.updateProfile(data);
      state.user = user;
      auth.setUser(user);
      return user;
    } catch (err) {
      const msg = err.response?.data?.message || 'Profile update failed.';
      state.error = msg;
      throw new Error(msg);
    } finally {
      state.loading = false;
    }
  }

  return {
    // State
    user,
    isAuthenticated,
    role,
    isCustomer,
    isSeller,
    isAdmin,
    loading,
    error,
    // Actions
    login,
    register,
    logout,
    refreshUser,
    updateProfile,
  };
};
