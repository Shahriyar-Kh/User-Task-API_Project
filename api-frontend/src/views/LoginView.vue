<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vue.js Login Component</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-router/4.2.4/vue-router.global.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Your styles here (same as before) */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    
    /* ... rest of your styles ... */
  </style>
</head>
<body>
  <div id="app"></div>

  <script>
    const { createApp, ref, onMounted } = Vue;
    const { createRouter, createWebHistory, useRouter, useRoute } = VueRouter;

    // Mock auth store for demonstration
    const useAuthStore = () => {
      const loading = ref(false);
      const error = ref('');
      
      const login = async (email, password) => {
        loading.value = true;
        error.value = '';
        
        // Simulate API call
        return new Promise((resolve, reject) => {
          setTimeout(() => {
            if (email === 'demo@example.com' && password === 'password') {
              resolve({ user: { name: 'Demo User' } });
            } else {
              error.value = 'Invalid email or password';
              reject(new Error('Invalid credentials'));
            }
            loading.value = false;
          }, 1500);
        });
      };
      
      return {
        loading,
        error,
        login
      };
    };

    const Login = {
      setup() {
        const email = ref('demo@example.com');
        const password = ref('password');
        const auth = useAuthStore();
        const router = useRouter();
        const route = useRoute();
        const successMessage = ref('');
        const errorMessage = ref('');
        const showPassword = ref(false);

        // ✅ Show success message if redirected from Register
        onMounted(() => {
          if (route.query.registered === 'true') {
            successMessage.value = '🎉 Registration successful! Please login below.';
          }
        });

        async function login() {
          errorMessage.value = '';
          try {
            await auth.login(email.value, password.value);
            // ✅ after login redirect to tasks
            router.push('/tasks');
          } catch (e) {
            errorMessage.value = auth.error || '❌ Login failed. Please check your credentials.';
          }
        }

        return {
          email,
          password,
          auth,
          successMessage,
          errorMessage,
          showPassword,
          login,
          togglePassword: () => {
            showPassword.value = !showPassword.value;
          }
        };
      },
      template: `
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
                <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Password" required />
                <span class="password-toggle" @click="togglePassword">
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </span>
              </div>
              
              <button type="submit" :disabled="auth.loading" :class="{'button-loading': auth.loading}">
                {{ auth.loading ? '' : 'Login' }}
              </button>
            </form>

            <div class="divider">
              <span>Or continue with</span>
            </div>

            <div class="social-login">
              <div class="social-btn facebook">
                <i class="fab fa-facebook-f"></i>
              </div>
              <div class="social-btn google">
                <i class="fab fa-google"></i>
              </div>
              <div class="social-btn twitter">
                <i class="fab fa-twitter"></i>
              </div>
            </div>

            <div class="register-link">
              Don't have an account? <a href="#">Sign up</a>
            </div>
          </div>
        </div>
      `
    };

    // Create router for demonstration
    const router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: Login },
        { path: '/tasks', component: { template: '<div>Tasks Page</div>' } }
      ]
    });

    // Create and mount the app
    const app = createApp(Login);
    app.use(router);
    app.mount('#app');
  </script>
</body>
</html>