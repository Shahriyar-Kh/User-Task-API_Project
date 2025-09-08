<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const successMessage = ref('')
const errorMessage = ref('')
const showPassword = ref(false)

// ✅ Show success message if redirected from Register
onMounted(() => {
  if (route.query.registered === 'true') {
    successMessage.value = '🎉 Registration successful! Please login below.'
  }
})

async function login() {
  errorMessage.value = ''
  try {
    await auth.login(email.value, password.value)
    // Role-based redirect is handled in the store
  } catch (e) {
    // Display the error from store
    errorMessage.value = auth.error || '❌ Login failed. Please check your credentials.'
  }
}

function togglePassword() {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="login-container">
    <div class="login-content">
      <h2>Welcome Back</h2>

      <!-- 🎉 Success message -->
      <p v-if="successMessage" class="message success-message">{{ successMessage }}</p>

      <!-- ❌ Error message -->
      <p v-if="errorMessage" class="message error-message">{{ errorMessage }}</p>

      <form @submit.prevent="login" class="login-form">
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input v-model="email" type="email" placeholder="Email" required />
        </div>

        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Password"
            required
          />
          <span class="password-toggle" @click="togglePassword">
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </span>
        </div>

        <button type="submit" :disabled="auth.loading" :class="{ 'button-loading': auth.loading }">
          {{ auth.loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <div class="divider">
        <span>Or continue with</span>
      </div>

      <div class="social-login">
        <div class="social-btn facebook"><i class="fab fa-facebook-f"></i></div>
        <div class="social-btn google"><i class="fab fa-google"></i></div>
        <div class="social-btn twitter"><i class="fab fa-twitter"></i></div>
      </div>

      <div class="register-link">
        Don't have an account?
        <RouterLink to="/register">Sign up</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-container {
  width: 100%;
  max-width: 450px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  margin: 60px auto;
}

h2 {
  text-align: center;
  margin-bottom: 25px;
  font-size: 22px;
  font-weight: bold;
}

.message {
  text-align: center;
  font-weight: bold;
  margin-bottom: 15px;
}

.success-message {
  color: green;
}

.error-message {
  color: red;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.input-group {
  display: flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 10px;
  background: #fff;
}

.input-group i {
  margin-right: 10px;
  color: #888;
}

.input-group input {
  border: none;
  flex: 1;
  outline: none;
}

.password-toggle {
  cursor: pointer;
  color: #555;
}

button {
  padding: 12px;
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover {
  background: #0056b3;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 20px 0;
}

.divider span {
  flex: 1;
  border-top: 1px solid #ccc;
  margin: 0 10px;
  font-size: 12px;
  color: #555;
}

.social-login {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}

.social-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  background: #eee;
  transition: 0.3s;
}

.social-btn:hover {
  background: #ddd;
}

.facebook { color: #3b5998; }
.google { color: #db4437; }
.twitter { color: #1da1f2; }

.register-link {
  text-align: center;
  font-size: 14px;
}

.register-link a {
  color: #007bff;
  text-decoration: none;
  font-weight: bold;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>
