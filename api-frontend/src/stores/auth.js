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
    userRole: (state) => state.user?.role || 'user',
  },

  actions: {
    // 🔑 Login with proper validation & role redirect
    async login(email, password) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.post('/auth/login', { email, password })
        console.log('✅ Login response:', data)

        // ✅ Extract token
        const token = data.token || data.access_token
        if (!token) throw new Error('Token not found in response')

        // ✅ Save token & user
        this._setToken(token)
        this._setUser(data.user)

        // ✅ Role-based redirect
        if (data.user.role === 'admin') {
          router.push('/admin-tasks')
        } else {
          router.push('/user-tasks')
        }

        return { user: this.user, token }
      } catch (e) {
        console.error('❌ Login error:', e.response?.data || e.message)

        // ✅ Custom error messages
        if (e.response) {
          if (e.response.status === 404) {
            this.error = 'Email is not registered'
          } else if (e.response.status === 401) {
            this.error = 'Password is incorrect'
          } else {
            this.error = 'Login failed. Please try again.'
          }
        } else {
          this.error = 'Network error. Please check your connection.'
        }

        throw e
      } finally {
        this.loading = false
      }
    },

    // 📝 Register (auto login after registration)
    async register({ name, email, password, password_confirmation }) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.post('/auth/register', {
          name,
          email,
          password,
          password_confirmation: password_confirmation || password,
        })
        console.log('✅ Register response:', data)

        const token = data.token || data.access_token
        if (token) {
          this._setToken(token)
          if (data.user) this._setUser(data.user)
        } else {
          console.warn('⚠️ No token returned, user must login manually')
          return data
        }

        return { user: this.user, token: this.token }
      } catch (e) {
        console.error('❌ Register error:', e.response?.data || e.message)
        this.error =
          e?.response?.data?.message ||
          Object.values(e?.response?.data?.errors || {})[0]?.[0] ||
          e.message
        throw e
      } finally {
        this.loading = false
      }
    },

    // 🔄 Refresh token
    async refreshToken() {
      try {
        const { data } = await api.post('/auth/refresh')
        const token = data.token || data.access_token
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
      try {
        const { data } = await api.get('/me')
        const user = data.user || data
        this._setUser(user)
        return user
      } catch (e) {
        console.error('❌ FetchMe error:', e.response?.data || e.message)
        this.forceLogout()
        throw e
      }
    },

    // 🚪 Logout
    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (e) {
        console.warn('⚠️ Logout request failed (ignored)')
      } finally {
        this._clearAuth()
        router.push('/login')
      }
    },

    forceLogout() {
      this._clearAuth()
      router.push('/login')
    },

    _setToken(token) {
      this.token = token
      if (token) {
        localStorage.setItem('token', token)
        api.defaults.headers.common.Authorization = `Bearer ${token}`
      } else {
        localStorage.removeItem('token')
        delete api.defaults.headers.common.Authorization
      }
    },

    _setUser(user) {
      this.user = user
      if (user) localStorage.setItem('user', JSON.stringify(user))
      else localStorage.removeItem('user')
    },

    _clearAuth() {
      this._setToken(null)
      this._setUser(null)
    },

    initFromStorage() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      if (token) this._setToken(token)
      if (user) this._setUser(JSON.parse(user))
    },
  },
})
