<template>
  <div>
    <h2>Admin - Users</h2>
    <ul>
      <li v-for="u in users" :key="u.id">
        {{ u.name }} - {{ u.email }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api/http'

const users = ref([])

async function load() {
  try {
    const res = await api.get('/admin/users')
    users.value = res.data.data || res.data
  } catch (err) {
    console.error('Error fetching users:', err)
  }
}

onMounted(load)
</script>
