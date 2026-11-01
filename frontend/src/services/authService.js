import apiClient from './api.js';

// ─── Token helpers ────────────────────────────────────────────────────────────
// We store the Sanctum token in localStorage (NOT the password).
// The user object (without password) is also cached in localStorage for UX.

const TOKEN_KEY = 'craftmarket_token';
const USER_KEY  = 'craftmarket_user';

function saveSession(user, token) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USER_KEY, JSON.stringify(user));
  // Inject token into default Axios headers for all future requests
  apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

function clearSession() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  delete apiClient.defaults.headers.common['Authorization'];
}

function restoreSession() {
  const token = localStorage.getItem(TOKEN_KEY);
  if (token) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
}

// ─── Auth Service ─────────────────────────────────────────────────────────────
export const authService = {

  /**
   * Called once at app startup to restore the session from localStorage.
   */
  init() {
    restoreSession();
  },

  /**
   * Register a new account.
   * @param {object} data  { name, email, password, password_confirmation, role, shop_name? }
   * @returns {{ user, token }}
   */
  async register(data) {
    const response = await apiClient.post('/auth/register', data);
    const { user, token } = response.data.data;
    saveSession(user, token);
    return { user, token };
  },

  /**
   * Login with email and password.
   * @param {string} email
   * @param {string} password
   * @returns {{ user, token }}
   */
  async login(email, password) {
    const response = await apiClient.post('/auth/login', { email, password });
    const { user, token } = response.data.data;
    saveSession(user, token);
    return { user, token };
  },

  /**
   * Logout — tells Laravel to revoke the current token.
   */
  async logout() {
    try {
      await apiClient.post('/auth/logout');
    } catch {
      // Even if API call fails (expired token), clear local session
    } finally {
      clearSession();
    }
  },

  /**
   * Get fresh user data from Laravel (verifies the token is still valid).
   * @returns {object} user
   */
  async fetchMe() {
    const response = await apiClient.get('/auth/me');
    const user = response.data.data;
    // Refresh cached user object
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    return user;
  },

  /**
   * Update authenticated user's profile.
   * @param {object} data  Profile fields to update
   * @returns {object} updated user
   */
  async updateProfile(data) {
    const response = await apiClient.put('/auth/profile', data);
    const user = response.data.data;
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    return user;
  },

  /**
   * Change password.
   * @param {object} data  { current_password, password, password_confirmation }
   */
  async changePassword(data) {
    await apiClient.put('/auth/password', data);
    clearSession(); // Force re-login after password change
  },

  // ─── Synchronous helpers (from cached localStorage, no API call) ────────

  getToken() {
    return localStorage.getItem(TOKEN_KEY);
  },

  getUser() {
    try {
      const raw = localStorage.getItem(USER_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  },

  isAuthenticated() {
    return !!this.getToken() && !!this.getUser();
  },

  getRole() {
    return this.getUser()?.role || null;
  },

  isCustomer() {
    return this.getRole() === 'customer';
  },

  isSeller() {
    return this.getRole() === 'seller';
  },

  isAdmin() {
    return this.getRole() === 'admin';
  },
};
