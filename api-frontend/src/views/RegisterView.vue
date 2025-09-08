<template>
  <div class="register-container">
    <h2>Register</h2>

    <!-- ❌ Error Message -->
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

    <form @submit.prevent="register" class="register-form">
      <input v-model="name" type="text" placeholder="Name" required />
      <input v-model="email" type="email" placeholder="Email" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <input v-model="passwordConfirmation" type="password" placeholder="Confirm Password" required />
      <button type="submit" :disabled="loading">
        {{ loading ? 'Registering...' : 'Register' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/plugins/axios'
import { useRouter } from 'vue-router'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')
const loading = ref(false)
const router = useRouter()

async function register() {
  errorMessage.value = ''
  loading.value = true
  try {
    await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value, // ✅ Laravel expects this
    })

    // ✅ Redirect to login page with success flag
    router.push({ path: '/login', query: { registered: 'true' } })
  } catch (error) {
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.error-message {
  color: red;
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.register-form input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.register-form button {
  padding: 10px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}

.register-form button:hover {
  background: #0056b3;
}
</style>
