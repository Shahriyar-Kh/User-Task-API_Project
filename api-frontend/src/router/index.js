import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Use dynamic imports for better code splitting
const LoginView = () => import('@/views/LoginView.vue')
const AdminTasksView = () => import('@/views/AdminTasksView.vue')
const UserTasksView = () => import('@/views/UserTasksView.vue')
const RegisterView = () => import('@/views/RegisterView.vue')

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: LoginView },
  { path: '/register', component: RegisterView },
  { 
    path: '/admin-tasks', 
    component: AdminTasksView, 
    meta: { requiresAuth: true, role: 'admin' } 
  },
  { 
    path: '/user-tasks', 
    component: UserTasksView, 
    meta: { requiresAuth: true, role: 'user' } 
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route Guards
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.role && auth.userRole !== to.meta.role) {
    return auth.userRole === 'admin' 
      ? next('/admin-tasks') 
      : next('/user-tasks')
  }

  next()
})

export default router