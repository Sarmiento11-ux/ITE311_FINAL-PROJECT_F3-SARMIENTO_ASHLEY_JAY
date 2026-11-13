import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// Import CraftMarket Main Stylesheet
import './assets/css/main.css';

// Restore Sanctum token from sessionStorage into Axios headers on page load
import { authService } from './services/authService.js';
authService.init();

const app = createApp(App);

app.use(router);
app.mount('#app');

// When the API rejects a protected request with 401 (expired/revoked token),
// the Axios interceptor clears the stale session and emits this event. Send
// the user to the login page and remember where they were.
window.addEventListener('craftmarket:session-expired', () => {
  const current = router.currentRoute.value;
  if (current.name === 'login') return;
  // Only bounce protected pages; public pages can keep working after the
  // stale token is cleared.
  if (!current.meta?.requiresAuth) return;
  router.push({ name: 'login', query: { redirect: current.fullPath } });
});
