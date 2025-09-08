<template>
  <div>
    <h2>Tasks</h2>

    <!-- Show login message if not authenticated -->
    <p v-if="!auth.isAuthenticated" class="error-message">
      ❌ Please login to view tasks.
    </p>

    <div v-else>
      <!-- Task Form (Admin only) -->
      <form v-if="auth.user?.role === 'admin'" @submit.prevent="createTask" class="task-form">
        <input v-model="newTaskTitle" placeholder="Task title" required />

        <!-- Assign user dropdown -->
        <select v-model="selectedUserId" required>
          <option disabled value="">Assign to user</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }} ({{ user.email }})
          </option>
        </select>

        <button type="submit">Save</button>
      </form>

      <!-- Task List -->
      <ul>
        <li v-for="task in tasks" :key="task?.id" class="task-item">
          <div class="task-info">
            <!-- Title (Editable only for Admin) -->
            <input
              v-if="auth.user?.role === 'admin'"
              v-model="task.title"
              @blur="updateTask(task)"
              class="task-input"
            />
            <span v-else>{{ task.title }}</span>

            <!-- Task details -->
            <p class="meta">
              👤 Assigned to: <strong>{{ task.user?.name || 'N/A' }}</strong>
              <span v-if="task.creator"> | 🛠 Created by: <strong>{{ task.creator?.name }}</strong></span>
              <span> | 📌 Status: {{ task.status }}</span>
              <span> | ⏳ Due: {{ task.due_date ? formatDate(task.due_date) : 'N/A' }}</span>
            </p>
          </div>

          <!-- Status update (User & Admin) -->
          <select
            v-model="task.status"
            @change="updateTask(task)"
            class="status-select"
          >
            <option value="todo">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
          </select>

          <!-- Delete (Admin only) -->
          <button v-if="auth.user?.role === 'admin'" @click="removeTask(task?.id)">
            Delete
          </button>
        </li>
      </ul>

      <!-- Clear All (Admin only) -->
      <button
        v-if="auth.user?.role === 'admin' && tasks.length"
        @click="clearTasks"
        class="clear-btn"
      >
        Clear All Tasks
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/plugins/axios'

const auth = useAuthStore()
const tasks = ref([])
const users = ref([])  // for dropdown list
const newTaskTitle = ref('')
const selectedUserId = ref('')

// Format date for display
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString()
}

// Fetch all tasks
async function fetchTasks() {
  if (!auth.isAuthenticated) return
  try {
    const res = await api.get('/tasks')
    tasks.value = Array.isArray(res.data.data) ? res.data.data : []
  } catch (e) {
    console.error('Error fetching tasks:', e)
    tasks.value = []
  }
}

// Fetch all users (admin only)
async function fetchUsers() {
  if (!auth.isAuthenticated || auth.user?.role !== 'admin') return
  try {
    const res = await api.get('/users')
    users.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching users:', e)
    users.value = []
  }
}

// Create task (Admin only)
async function createTask() {
  if (!auth.isAuthenticated || !newTaskTitle.value.trim() || !selectedUserId.value) return
  try {
    const payload = {
      title: newTaskTitle.value,
      user_id: selectedUserId.value,
    }

    const res = await api.post('/tasks', payload)
    if (res.data?.task) {
      tasks.value.push(res.data.task)
    }
    newTaskTitle.value = ''
    selectedUserId.value = ''
  } catch (e) {
    console.error('Error creating task:', e)
  }
}

// Update task
async function updateTask(task) {
  if (!auth.isAuthenticated || !task?.id) return
  try {
    // Admin can update everything, user can only update status/description
    const payload =
      auth.user?.role === 'admin'
        ? { title: task.title, status: task.status }
        : { status: task.status }

    const res = await api.put(`/tasks/${task.id}`, payload)
    Object.assign(task, res.data?.task)
  } catch (e) {
    console.error('Error updating task:', e)
  }
}

// Delete task (Admin only)
async function removeTask(id) {
  if (!auth.isAuthenticated || !id) return
  try {
    await api.delete(`/tasks/${id}`)
    tasks.value = tasks.value.filter(t => t?.id !== id)
  } catch (e) {
    console.error('Error deleting task:', e)
  }
}

// Clear all tasks (Admin only)
async function clearTasks() {
  if (!auth.isAuthenticated) return
  try {
    for (const task of [...tasks.value]) {
      if (task?.id) await api.delete(`/tasks/${task.id}`)
    }
    tasks.value = []
  } catch (e) {
    console.error('Error clearing tasks:', e)
  }
}

onMounted(() => {
  fetchTasks()
  fetchUsers()
})
</script>

<style scoped>
.error-message { color: red; font-weight: bold; margin-bottom: 15px; }
.task-form { display: flex; gap: 10px; margin-bottom: 20px; }
.task-form input, .task-form select { flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
.task-form button { padding: 8px 12px; border: none; background: #28a745; color: white; border-radius: 4px; cursor: pointer; }
.task-form button:hover { background: #218838; }
.task-item { display: flex; flex-direction: column; gap: 6px; padding: 10px; border-bottom: 1px solid #eee; }
.task-info { display: flex; flex-direction: column; }
.task-input { padding: 6px; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 5px; }
.meta { font-size: 0.9em; color: #555; }
.status-select { width: fit-content; padding: 4px; border-radius: 4px; border: 1px solid #ccc; }
.task-item button { padding: 4px 8px; border: none; background: #dc3545; color: white; border-radius: 4px; cursor: pointer; margin-top: 5px; }
.task-item button:hover { background: #c82333; }
.clear-btn { margin-top: 15px; padding: 8px 12px; border: none; background: #6c757d; color: white; border-radius: 4px; cursor: pointer; }
.clear-btn:hover { background: #5a6268; }
</style>
