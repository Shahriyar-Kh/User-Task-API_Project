<template>
<nav class="flex justify-between" style="padding: 12px 0;">
<div>
<RouterLink to="/tasks">Tasks</RouterLink>
<RouterLink v-if="isAuthenticated" to="/import">Import</RouterLink>
<RouterLink v-if="isAuthenticated" to="/profile">Profile</RouterLink>
<RouterLink v-if="isAdmin" to="/admin/users">Admin</RouterLink>
</div>
<div>
<button v-if="isAuthenticated" class="btn" @click="onRefresh">Refresh Token</button>
<RouterLink v-if="!isAuthenticated" to="/login">Login</RouterLink>
<RouterLink v-if="!isAuthenticated" to="/register">Register</RouterLink>
<button v-if="isAuthenticated" class="btn danger" @click="onLogout">Logout</button>
</div>
</nav>
</template>


<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { RouterLink } from 'vue-router'


const auth = useAuthStore()
const isAuthenticated = computed(() => !!auth.token)
const isAdmin = computed(() => auth?.user?.role === 'admin')


async function onLogout() { await auth.logout() }
async function onRefresh() {
try {
await auth.refreshToken()
alert('Token refreshed')
} catch (e) {
alert('Refresh failed. You may need to login again.')
}
}
</script>