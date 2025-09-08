import axios from 'axios'
import router from '@/router'

// ✅ Create Axios instance
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // full backend URL
  withCredentials: true,                // needed if using cookies/sanctum
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 10000, // Add timeout to prevent hanging requests
})

// 🔹 Attach token automatically
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    console.error('🚨 Request error:', error)
    return Promise.reject(error)
  }
)

// 🔹 Handle responses globally
api.interceptors.response.use(
  (response) => {
    // You can add any response transformation here if needed
    return response
  },
  (error) => {
    if (!error.response) {
      // 🌐 Network error (server not reachable, CORS issue, etc.)
      console.error('🚨 Network error. Check backend / CORS / connection.')
      
      // Show user-friendly message
      if (error.code === 'ECONNABORTED') {
        return Promise.reject({ 
          message: 'Request timeout. Please check your connection and try again.' 
        })
      }
      
      return Promise.reject({ 
        message: 'Network error. Please check your connection and ensure the server is running.' 
      })
    }

    const status = error.response.status
    const data = error.response.data
    const url = error.config.url
    const method = error.config.method

    // Log detailed error information for debugging
    console.error('🚨 API Error:', {
      status,
      url,
      method,
      data,
      error: error.message
    })

    // 🔒 Auto logout on 401 (Unauthorized)
    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      if (router.currentRoute.value.path !== '/login') {
        router.push('/login')
      }
      return Promise.reject({ 
        message: 'Your session has expired. Please log in again.' 
      })
    }

    // Handle 403 (Forbidden)
    if (status === 403) {
      return Promise.reject({ 
        message: 'You do not have permission to perform this action.' 
      })
    }

    // Handle 404 (Not Found)
    if (status === 404) {
      return Promise.reject({ 
        message: 'The requested resource was not found.' 
      })
    }

    // Handle 422 (Validation Error)
    if (status === 422) {
      // Extract validation errors if available
      const validationErrors = data.errors || {}
      const firstError = Object.values(validationErrors)[0]?.[0] || data.message
      
      return Promise.reject({ 
        message: firstError || 'Validation failed',
        errors: validationErrors
      })
    }

    // Handle 500 (Server Error)
    if (status >= 500) {
      console.error('🚨 Server error:', data)
      
      // Provide more specific error message based on common issues
      let userMessage = 'Server error. Please try again later.'
      
      if (data.message && data.message.includes('SQL')) {
        userMessage = 'Database error. Please contact support.'
      } else if (data.exception) {
        userMessage = 'Server encountered an unexpected error.'
      }
      
      return Promise.reject({ 
        message: userMessage,
        detail: process.env.NODE_ENV === 'development' ? data : undefined
      })
    }

    // For all other errors
    return Promise.reject({
      message: data.message || `Request failed with status ${status}`,
      status,
      data
    })
  }
)

export default api