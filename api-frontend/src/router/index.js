// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Views
import LoginView from '@/views/LoginView.vue'
import AdminTasksView from '@/views/AdminTasksView.vue'
import UserTasksView from '@/views/UserTasksView.vue'

import RegisterView from '@/views/RegisterView.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: LoginView },
  { path: '/register', component: RegisterView },   // ✅ Added
  { path: '/admin-tasks', component: AdminTasksView, meta: { requiresAuth: true, role: 'admin' } },
  { path: '/user-tasks', component: UserTasksView, meta: { requiresAuth: true, role: 'user' } },
]


const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ✅ Route Guards
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.user) {
    return next('/login')
  }

  if (to.meta.role && auth.user?.role !== to.meta.role) {
    // 🚫 block if role mismatch
    return auth.user?.role === 'admin'
      ? next('/admin-tasks')
      : next('/user-tasks')
  }

  next()
})

export default router
