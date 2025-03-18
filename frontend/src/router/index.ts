import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Register from '../pages/Register.vue';
import Dashboard from '../pages/Dashboard.vue';

// @ts-ignore
const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
// @ts-ignore
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  // @ts-ignore
  const requiresAuth = to.matched.some(record => record.meta?.requiresAuth);

  if (requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (!requiresAuth && isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    next('/dashboard');
  } else {
    next();
  }
});

export default router; 