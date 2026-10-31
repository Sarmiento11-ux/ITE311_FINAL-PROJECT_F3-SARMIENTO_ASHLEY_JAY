import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// Import CraftMarket Custom Stylesheets
import './assets/css/style.css';
import './assets/css/products.css';
import './assets/css/product-details.css';
import './assets/css/auth.css';
import './assets/css/profile.css';

const app = createApp(App);

app.use(router);
app.mount('#app');
