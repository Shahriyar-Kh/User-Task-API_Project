// src/stores/auth.js
import { defineStore } from 'pinia'
import api from '@/plugins/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    error: null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || '',
  },

  actions: {
    // 🔑 Login
async login(email, password) {
  this.loading = true
  this.error = null
  try {
    const { data } = await api.post('/auth/login', { email, password })
    console.log('✅ Login response:', data)

    const token = data?.token || data?.access_token || data?.accessToken
    if (!token) throw new Error('Token not found in response')

    // ✅ Save token first
    this._setToken(token)

    // ✅ Fetch user after token is saved
    await this.fetchMe()

    // ✅ Redirect after everything is ready
    const redirect = router.currentRoute.value.query.redirect || '/tasks'
    await router.push(redirect)

    return data
  } catch (e) {
    console.error('❌ Login error:', e.response?.data || e.message)
    this.error = e?.response?.data?.message || e.message
    throw e
  } finally {
    this.loading = false
  }
},
    // 📝 Register
    async register({ name, email, password }) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.post('/auth/register', {
          name,
          email,
          password,
          password_confirmation: password, // Laravel expects confirmation
        })
        console.log('✅ Register response:', data)

        // Redirect to login
        await router.push({ path: '/login', query: { registered: 'true' } })
        return data
      } catch (e) {
        console.error('❌ Register error:', e.response?.data || e.message)
        this.error = e?.response?.data?.message || e.message
        throw e
      } finally {
        this.loading = false
      }
    },

    // 🔄 Refresh token
    async refreshToken() {
      try {
        const { data } = await api.post('/auth/refresh')
        console.log('✅ Refresh response:', data)

        const token = data?.access_token || data?.token || data?.accessToken
        if (!token) throw new Error('Refresh token missing in response')
        this._setToken(token)
        return token
      } catch (e) {
        console.error('❌ Refresh error:', e.response?.data || e.message)
        this.forceLogout()
        throw e
      }
    },

    // 👤 Fetch user info
    async fetchMe() {
      const { data } = await api.get('/users/me')
      console.log('✅ FetchMe response:', data)
      this._setUser(data)
      return data
    },

    // ✏️ Update profile
    async updateMe(payload) {
      const { data } = await api.put('/users/me', payload)
      console.log('✅ UpdateMe response:', data)
      this._setUser(data)
      return data
    },

    // 🚪 Logout
    async logout() {
      try {
        await api.post('/auth/logout')
        console.log('✅ Logout success')
      } catch (e) {
        console.warn('⚠️ Logout request failed (ignored)')
      } finally {
        this._setToken(null)
        this._setUser(null)
        router.push('/login')
      }
    },

    // 🔒 Force logout
    async forceLogout() {
      this._setToken(null)
      this._setUser(null)
      router.push('/login')
    },

    // -----------------------------
    // Private helpers
    // -----------------------------
    _setToken(token) {
      this.token = token
      if (token) {
        localStorage.setItem('token', token)
      } else {
        localStorage.removeItem('token')
      }
    },

    _setUser(user) {
      this.user = user
      if (user) {
        localStorage.setItem('user', JSON.stringify(user))
      } else {
        localStorage.removeItem('user')
      }
    },

    // 🗂️ Restore from localStorage (on app start)
    initFromStorage() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      if (token) this.token = token
      if (user) this.user = JSON.parse(user)
    },
  },
})
