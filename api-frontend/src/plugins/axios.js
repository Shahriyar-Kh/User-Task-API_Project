import axios from 'axios'
import router from '@/router'

// Create Axios instance
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15000, // Increased timeout for better reliability
})

// Attach token automatically
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Log request details for debugging
    console.log(`🔄 API Request: ${config.method?.toUpperCase()} ${config.url}`, {
      headers: config.headers,
      data: config.data
    })
    
    return config
  },
  (error) => {
    console.error('🚨 Request interceptor error:', error)
    return Promise.reject(error)
  }
)

// Handle responses globally
api.interceptors.response.use(
  (response) => {
    // Log successful responses for debugging
    console.log(`✅ API Response: ${response.config.method?.toUpperCase()} ${response.config.url}`, {
      status: response.status,
      data: response.data
    })
    return response
  },
  (error) => {
    // Enhanced error logging
    console.error('🚨 API Error:', {
      message: error.message,
      status: error.response?.status,
      url: error.config?.url,
      method: error.config?.method,
      data: error.response?.data,
      headers: error.response?.headers
    })

    if (!error.response) {
      // Network error (server not reachable, CORS issue, etc.)
      console.error('🚨 Network error. Check backend / CORS / connection.')
      
      if (error.code === 'ECONNABORTED') {
        return Promise.reject({ 
          message: 'Request timeout. Please check your connection and try again.',
          type: 'timeout'
        })
      }
      
      return Promise.reject({ 
        message: 'Network error. Please ensure the server is running and accessible.',
        type: 'network'
      })
    }

    const status = error.response.status
    const data = error.response.data
    const url = error.config?.url
    const method = error.config?.method

    // Enhanced server error logging
    if (status >= 500) {
      console.error('🚨 Server error:', {
        status,
        url,
        method,
        data,
        stack: data?.trace || data?.exception
      })
    }

    // Auto logout on 401 (Unauthorized)
    if (status === 401) {
      console.warn('🔐 Unauthorized - clearing auth data')
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      
      if (router.currentRoute.value.path !== '/login') {
        router.push('/login')
      }
      
      return Promise.reject({ 
        message: data?.error || data?.message || 'Your session has expired. Please log in again.',
        status,
        type: 'auth'
      })
    }

    // Handle 403 (Forbidden)
    if (status === 403) {
      return Promise.reject({ 
        message: data?.error || data?.message || 'You do not have permission to perform this action.',
        status,
        type: 'permission'
      })
    }

    // Handle 404 (Not Found)
    if (status === 404) {
      return Promise.reject({ 
        message: data?.error || data?.message || 'The requested resource was not found.',
        status,
        type: 'notfound'
      })
    }

    // Handle 422 (Validation Error)
    if (status === 422) {
      const validationErrors = data?.errors || {}
      const firstError = Object.values(validationErrors)[0]?.[0] || data?.error || data?.message
      
      return Promise.reject({ 
        message: firstError || 'Validation failed',
        errors: validationErrors,
        status,
        type: 'validation'
      })
    }

    // Handle 500+ (Server Error)
    if (status >= 500) {
      let userMessage = 'Server error. Please try again later.'
      
      // Provide more specific error messages for common server issues
      if (data?.message) {
        if (data.message.includes('SQLSTATE') || data.message.includes('database')) {
          userMessage = 'Database connection error. Please contact support.'
        } else if (data.message.includes('Class') && data.message.includes('not found')) {
          userMessage = 'Server configuration error. Please contact support.'
        } else if (data.message.includes('Call to undefined')) {
          userMessage = 'Server method error. Please contact support.'
        }
      }
      
      return Promise.reject({ 
        message: data?.error || userMessage,
        detail: process.env.NODE_ENV === 'development' ? data : undefined,
        status,
        type: 'server'
      })
    }

    // For all other errors
    return Promise.reject({
      message: data?.error || data?.message || `Request failed with status ${status}`,
      status,
      data,
      type: 'unknown'
    })
  }
)

export default api