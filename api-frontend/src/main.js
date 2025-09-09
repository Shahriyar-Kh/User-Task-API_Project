import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize auth store after pinia is mounted
const authStore = pinia.state.value.auth
if (authStore) {
  authStore.initFromStorage()
}

app.mount('#app')