import axios from 'axios'


const api = axios.create({
baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
})


// Attach token (if present)
api.interceptors.request.use((config) => {
const token = localStorage.getItem('access_token')
if (token) config.headers.Authorization = `Bearer ${token}`
return config
})


// Basic auth failure handling
api.interceptors.response.use(
(r) => r,
(err) => {
if (err?.response?.status === 401) {
localStorage.removeItem('access_token')
localStorage.removeItem('user')
// Hard redirect ensures clean state
if (location.pathname !== '/login') location.href = '/login'
}
return Promise.reject(err)
}
)


export default api