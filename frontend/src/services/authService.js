import apiClient from './api.js';

// ─── Token helpers ────────────────────────────────────────────────────────────
// Auth token and user data are stored in sessionStorage so sessions do NOT
// automatically persist across browser restarts or when reopening the system.

const TOKEN_KEY = 'craftmarket_token';
const USER_KEY  = 'craftmarket_user';

function saveSession(user, token) {
  try {
    sessionStorage.setItem(TOKEN_KEY, token);
    sessionStorage.setItem(USER_KEY, JSON.stringify(user));
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  } catch {
    // Ignore storage errors
  }
  // Inject token into default Axios headers for all future requests
  apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

function clearSession() {
  try {
    sessionStorage.removeItem(TOKEN_KEY);
    sessionStorage.removeItem(USER_KEY);
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  } catch {
    // Ignore storage errors
  }
  delete apiClient.defaults.headers.common['Authorization'];
}

function restoreSession() {
  try {
    const token = sessionStorage.getItem(TOKEN_KEY) || localStorage.getItem(TOKEN_KEY);
    if (token) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
  } catch {
    // Ignore storage errors
  }
}

// ─── Auth Service ─────────────────────────────────────────────────────────────
export const authService = {

  /**
   * Called once at app startup to restore the session from sessionStorage.
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
    try {
      sessionStorage.setItem(USER_KEY, JSON.stringify(user));
    } catch {
      // Ignore storage errors
    }
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
    try {
      sessionStorage.setItem(USER_KEY, JSON.stringify(user));
    } catch {
      // Ignore storage errors
    }
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

  // ─── Synchronous helpers (from cached sessionStorage, no API call) ────────

  getToken() {
    try {
      return sessionStorage.getItem(TOKEN_KEY) || localStorage.getItem(TOKEN_KEY);
    } catch {
      return null;
    }
  },

  getUser() {
    try {
      const raw = sessionStorage.getItem(USER_KEY) || localStorage.getItem(USER_KEY);
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
