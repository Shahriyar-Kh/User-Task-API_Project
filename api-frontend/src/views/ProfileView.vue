<template>
  <div class="profile">
    <h2>My Profile</h2>

    <div v-if="user">
      <p><strong>Name:</strong> {{ user.name }}</p>
      <p><strong>Email:</strong> {{ user.email }}</p>
    </div>

    <form @submit.prevent="updateProfile">
      <div>
        <label>Name</label>
        <input v-model="form.name" type="text" />
      </div>
      <div>
        <label>Email</label>
        <input v-model="form.email" type="email" />
      </div>
      <div>
        <label>Password</label>
        <input v-model="form.password" type="password" />
      </div>
      <button type="submit">Update</button>
    </form>

    <!-- ✅ Logout button -->
    <button @click="logout" class="logout-btn">Logout</button>
  </div>
</template>

<script>
import api from "../api";

export default {
  name: "Profile",
  data() {
    return {
      user: null,
      form: {
        name: "",
        email: "",
        password: ""
      }
    };
  },
  methods: {
    async fetchProfile() {
      try {
        const res = await api.get("/auth/me");
        this.user = res.data.user;
        this.form.name = this.user.name;
        this.form.email = this.user.email;
      } catch (err) {
        console.error("Failed to load profile", err);
      }
    },
    async updateProfile() {
      try {
        const res = await api.put("/auth/me", this.form);
        this.user = res.data.user;
        alert("Profile updated!");
      } catch (err) {
        console.error("Failed to update profile", err);
      }
    },
    async logout() {
      try {
        await api.post("/auth/logout"); // ✅ Laravel logout API
        localStorage.removeItem("token"); // clear token
        this.$router.push("/login"); // redirect to login
      } catch (err) {
        console.error("Logout failed", err);
      }
    }
  },
  mounted() {
    this.fetchProfile();
  }
};
</script>

<style>
.logout-btn {
  margin-top: 20px;
  background: red;
  color: white;
  padding: 8px 12px;
  border: none;
  cursor: pointer;
}
</style>
