import { createRouter, createWebHistory } from 'vue-router';
import { authService } from '../services/authService.js';

// Lazy-loaded route views (code-split for optimal initial page-load performance)
const HomeView = () => import('../views/HomeView.vue');
const ProductsView = () => import('../views/ProductsView.vue');
const ProductDetailsView = () => import('../views/ProductDetailsView.vue');
const RegisterView = () => import('../views/RegisterView.vue');
const LoginView = () => import('../views/LoginView.vue');
const ProfileView = () => import('../views/ProfileView.vue');
const CustomerDashboardView = () => import('../views/CustomerDashboardView.vue');
const SellerDashboardView = () => import('../views/SellerDashboardView.vue');
const CartView = () => import('../views/CartView.vue');
const WishlistView = () => import('../views/WishlistView.vue');
const CheckoutView = () => import('../views/CheckoutView.vue');
const OrdersView = () => import('../views/OrdersView.vue');
const PaymentStatusView = () => import('../views/PaymentStatusView.vue');
const SandboxCheckoutView = () => import('../views/SandboxCheckoutView.vue');
const AdminDashboardView = () => import('../views/AdminDashboardView.vue');

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
    path: '/about',
    redirect: { path: '/', hash: '#about' }
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
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    path: '/dashboard/seller',
    name: 'seller-dashboard',
    component: SellerDashboardView,
    meta: { requiresAuth: true, roles: ['seller', 'admin'] }
  },
  {
    path: '/dashboard/admin',
    name: 'admin-dashboard',
    component: AdminDashboardView,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/admin',
    redirect: '/dashboard/admin'
  },
  {
    path: '/wishlist',
    name: 'wishlist',
    component: WishlistView,
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    path: '/cart',
    name: 'cart',
    component: CartView,
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: CheckoutView,
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    path: '/orders',
    name: 'orders',
    component: OrdersView,
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    path: '/payment/status',
    name: 'payment-status',
    component: PaymentStatusView,
    meta: { requiresAuth: true, roles: ['customer'] }
  },
  {
    // Reachable only when the backend issues a local checkout session instead
    // of a PayMongo hosted one (developer machines without gateway keys).
    path: '/payment/checkout',
    name: 'payment-checkout',
    component: SandboxCheckoutView,
    meta: { requiresAuth: true, roles: ['customer'] }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else if (to.hash) {
      // `top` is subtracted from the element position, keeping the section
      // heading visible below the sticky 76px site header.
      return { el: to.hash, behavior: 'smooth', top: 100 };
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

  // 2. Route is guest-only (login/register) and user IS logged in → send to dashboard/profile
  if (to.meta.guestOnly && isAuth) {
    if (role === 'admin') return next({ name: 'admin-dashboard' });
    if (role === 'seller') return next({ name: 'seller-dashboard' });
    return next({ name: 'profile' });
  }

  // 4. Route has role restriction and user's role is not allowed → send to home
  if (to.meta.roles && to.meta.roles.length > 0 && isAuth) {
    if (!to.meta.roles.includes(role)) {
      // Redirect users away from unauthorized dashboards
      if (role === 'admin') return next({ name: 'admin-dashboard' });
      if (role === 'seller') return next({ name: 'seller-dashboard' });
      if (role === 'customer') return next({ name: 'customer-dashboard' });
      return next({ name: 'home' });
    }
  }

  next();
});

export default router;
