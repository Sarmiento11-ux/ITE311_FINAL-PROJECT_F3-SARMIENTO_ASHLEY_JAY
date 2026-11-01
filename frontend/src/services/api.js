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
      const token = localStorage.getItem('craftmarket_token') || sessionStorage.getItem('craftmarket_token');
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
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    let message = 'An unexpected error occurred. Please try again.';

    if (error.response) {
      // Backend returned an error response (4xx, 5xx)
      message = error.response.data?.message || `Server error: ${error.response.status}`;
    } else if (error.request) {
      // Request made but no response (server offline / connection refused)
      message = 'Unable to connect to the CraftMarket backend server. Please check your connection.';
    }

    console.warn('[CraftMarket API]', message, error);
    return Promise.reject({ ...error, userFriendlyMessage: message });
  }
);

export default apiClient;
