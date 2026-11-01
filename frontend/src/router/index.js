import { createRouter, createWebHistory } from 'vue-router';
import { authService } from '../services/authService.js';
import HomeView from '../views/HomeView.vue';
import ProductsView from '../views/ProductsView.vue';
import ProductDetailsView from '../views/ProductDetailsView.vue';
import RegisterView from '../views/RegisterView.vue';
import LoginView from '../views/LoginView.vue';
import ProfileView from '../views/ProfileView.vue';
import CustomerDashboardView from '../views/CustomerDashboardView.vue';
import SellerDashboardView from '../views/SellerDashboardView.vue';
import CartView from '../views/CartView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import OrdersView from '../views/OrdersView.vue';
import PaymentStatusView from '../views/PaymentStatusView.vue';

// Route meta fields:
//   requiresAuth: true  — must be logged in
//   roles: ['seller']   — allowed roles (empty = any authenticated user)
//   guestOnly: true     — redirect to profile if already logged in (login/register pages)

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/products',
    name: 'products',
    component: ProductsView
  },
  {
    path: '/products/:id',
    name: 'product-details',
    component: ProductDetailsView
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { guestOnly: true }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/customer',
    name: 'customer-dashboard',
    component: CustomerDashboardView,
    meta: { requiresAuth: true, roles: ['customer', 'admin'] }
  },
  {
    path: '/dashboard/seller',
    name: 'seller-dashboard',
    component: SellerDashboardView,
    meta: { requiresAuth: true, roles: ['seller', 'admin'] }
  },
  {
    path: '/cart',
    name: 'cart',
    component: CartView
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: CheckoutView,
    meta: { requiresAuth: true }
  },
  {
    path: '/orders',
    name: 'orders',
    component: OrdersView,
    meta: { requiresAuth: true, roles: ['customer', 'admin'] }
  },
  {
    path: '/payment/status',
    name: 'payment-status',
    component: PaymentStatusView,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else if (to.hash) {
      return { el: to.hash, behavior: 'smooth' };
    } else {
      return { top: 0, behavior: 'smooth' };
    }
  }
});

// ─── Navigation Guard ─────────────────────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const isAuth  = authService.isAuthenticated();
  const role    = authService.getRole();

  // 1. Route requires authentication and user is NOT logged in → send to login
  if (to.meta.requiresAuth && !isAuth) {
    return next({ name: 'login', query: { redirect: to.fullPath } });
  }

  // 2. Route is guest-only (login/register) and user IS logged in → send to profile
  if (to.meta.guestOnly && isAuth) {
    return next({ name: 'profile' });
  }

  // 3. Route has role restriction and user's role is not allowed → send to home
  if (to.meta.roles && to.meta.roles.length > 0 && isAuth) {
    if (!to.meta.roles.includes(role)) {
      // Redirect sellers away from customer dashboard and vice versa
      if (role === 'seller') return next({ name: 'seller-dashboard' });
      if (role === 'customer') return next({ name: 'customer-dashboard' });
      return next({ name: 'home' });
    }
  }

  next();
});

export default router;
