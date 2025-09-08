import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLoadingStore } from '@/stores/loading'

const routes = [
  { path: '/login', name: 'Login', component: () => import('@/views/LoginView.vue') },
  { path: '/register', name: 'Register', component: () => import('@/views/RegisterView.vue') },
  { path: '/profile', name: 'Profile', component: () => import('@/views/ProfileView.vue'), meta: { requiresAuth: true } },
  { path: '/tasks', name: 'Tasks', component: () => import('@/views/TasksView.vue'), meta: { requiresAuth: true } },
  { path: '/import', name: 'Import', component: () => import('@/views/ImportView.vue'), meta: { requiresAuth: true } },
  { 
    path: '/admin/users', 
    name: 'AdminUsers', 
    component: () => import('@/views/AdminUsersView.vue'), 
    meta: { requiresAuth: true, requiresAdmin: true } 
  },
  { path: '/', redirect: '/tasks' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  const loading = useLoadingStore()

  loading.start()

  if (!auth.token) {
    auth.initFromStorage()
  }

  // 🔒 Protect routes that require login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next({ path: '/login', query: { redirect: to.fullPath } })
    return
  }

  // 🔒 Prevent logged-in users from accessing /login again
  if (to.path === '/login' && auth.isAuthenticated) {
    next('/tasks')
    return
  }

  // 🔒 Protect admin routes
  if (to.meta.requiresAdmin && auth.user?.role !== 'admin') {
    next('/tasks') // or redirect to "403 Forbidden" page if you create one
    return
  }

  next()
})

router.afterEach(() => {
  const loading = useLoadingStore()
  loading.stop()
})

export default router
