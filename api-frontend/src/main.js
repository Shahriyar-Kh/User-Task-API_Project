import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'
import { useAuthStore } from './stores/auth'  // ✅ import auth store

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)

// ✅ Initialize auth from localStorage to prevent 401 loops
const auth = useAuthStore(pinia)
auth.initFromStorage()

app.use(router)
app.mount('#app')
