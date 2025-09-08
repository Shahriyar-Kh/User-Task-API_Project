<template>
  <div class="register-container">
    <h2>Register</h2>
    <form @submit.prevent="handleRegister" class="register-form">
      <div class="form-group">
        <label>Name:</label>
        <input v-model="form.name" type="text" required />
      </div>

      <div class="form-group">
        <label>Email:</label>
        <input v-model="form.email" type="email" required />
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input v-model="form.password" type="password" required />
      </div>

      <div class="form-group">
        <label>Role:</label>
        <select v-model="form.role">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button type="submit" class="btn register-btn">Register</button>
    </form>

    <!-- ❌ Success / Error Messages -->
    <p v-if="successMessage" class="success">{{ successMessage }}</p>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    <!-- Login button below -->
    <button class="btn login-btn" @click="$router.push('/login')">
      Already have an account? Login
    </button>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "RegisterView",
  data() {
    return {
      form: {
        name: "",
        email: "",
        password: "",
        role: "user",
      },
      successMessage: "",
      errorMessage: "",
    };
  },
  methods: {
    async handleRegister() {
      this.successMessage = "";
      this.errorMessage = "";

      try {
        const res = await axios.post("http://127.0.0.1:8000/api/auth/register", this.form);

        // Store token in localStorage
        localStorage.setItem("token", res.data.access_token);
        localStorage.setItem("user", JSON.stringify(res.data.user));

        this.successMessage = "Registration successful! 🎉 You are logged in.";
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.errorMessage = err.response.data.message || "Validation failed!";
        } else {
          this.errorMessage = "Something went wrong. Please try again.";
        }
      }
    },
  },
};
</script>

<style scoped>
.register-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 30px;
  border: 1px solid #ddd;
  border-radius: 10px;
  background-color: #f9f9f9;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.register-container h2 {
  margin-bottom: 20px;
  color: #333;
}

.register-form .form-group {
  margin-bottom: 15px;
  text-align: left;
}

.register-form label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #555;
}

.register-form input,
.register-form select {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

.btn {
  width: 100%;
  padding: 12px;
  margin-top: 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}

.register-btn {
  background-color: #4CAF50;
  color: white;
}

.register-btn:hover {
  background-color: #45a049;
}

.login-btn {
  background-color: #f1f1f1;
  color: #333;
}

.login-btn:hover {
  background-color: #ddd;
}

.success {
  color: green;
  margin-top: 15px;
  font-weight: 500;
}

.error {
  color: red;
  margin-top: 15px;
  font-weight: 500;
}
</style>
