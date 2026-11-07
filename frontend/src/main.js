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
