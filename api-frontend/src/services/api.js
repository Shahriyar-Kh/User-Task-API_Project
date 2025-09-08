import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

export const api = axios.create({
  baseURL: 'http://localhost:8000/api', // change if your backend runs elsewhere
})

// Attach token automatically if available
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Handle 401 errors (optional)
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const authStore = useAuthStore()
    if (error.response?.status === 401) {
      try {
        // try refreshing token
        const newToken = await authStore.refreshToken()
        error.config.headers.Authorization = `Bearer ${newToken}`
        return api.request(error.config) // retry request
      } catch (_) {
        // if refresh fails → force logout
        authStore.forceLogout()
      }
    }
    return Promise.reject(error)
  }
)
