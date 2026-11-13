import axios from 'axios';

// Centralized Axios instance with environment variable fallback
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  timeout: 10000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Request interceptor: attach Sanctum Bearer token if available
apiClient.interceptors.request.use(
  (config) => {
    try {
      const token = sessionStorage.getItem('craftmarket_token') || localStorage.getItem('craftmarket_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch {
      // Ignore storage errors
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: standardize error structure
const AUTH_STORAGE_KEYS = ['craftmarket_token', 'craftmarket_user'];

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    let message = 'An unexpected error occurred. Please try again.';
    const status = error.response?.status;
    const requestUrl = error.config?.url || '';
    const isCredentialAttempt = /\/auth\/(login|register)/.test(requestUrl);

    if (error.response) {
      // Backend returned an error response (4xx, 5xx)
      message = error.response.data?.message || `Server error: ${error.response.status}`;
    } else if (error.request) {
      // Request made but no response (server offline / connection refused)
      message = 'Unable to connect to the CraftMarket backend server. Please check your connection.';
    }

    // Expired/revoked token on a protected request → drop the stale session so
    // the app can send the user back to the login page instead of retrying
    // every call with a token the server will keep rejecting.
    if (status === 401 && !isCredentialAttempt) {
      message = 'Your session has expired. Please log in again.';
      try {
        AUTH_STORAGE_KEYS.forEach((key) => {
          sessionStorage.removeItem(key);
          localStorage.removeItem(key);
        });
      } catch {
        // Ignore storage errors
      }
      delete apiClient.defaults.headers.common['Authorization'];
      error.sessionExpired = true;
      try {
        window.dispatchEvent(new CustomEvent('craftmarket:session-expired'));
      } catch {
        // Ignore environments without window/CustomEvent
      }
    }

    console.warn('[CraftMarket API]', message, error);
    error.userFriendlyMessage = message;
    // Views commonly surface `err.message`; keep it human-readable.
    error.message = message;
    return Promise.reject(error);
  }
);

export default apiClient;
