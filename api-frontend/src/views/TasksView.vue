<template>
  <div>
    <h2>Tasks</h2>

    <p v-if="!auth.isAuthenticated" class="error-message">
      ❌ Please login to view tasks.
    </p>

    <div v-else>
      <!-- Task Form -->
      <form @submit.prevent="createTask" class="task-form">
        <input v-model="newTaskTitle" placeholder="Task title" required />
        <button type="submit">Save</button>
      </form>

      <!-- Task List -->
      <ul>
        <li v-for="task in tasks" :key="task?.id" class="task-item">
          <input 
            v-model="task.title" 
            @blur="updateTask(task)" 
            class="task-input" 
            v-if="task"
          />
          <button @click="removeTask(task?.id)" v-if="task">Delete</button>
        </li>
      </ul>

      <button v-if="tasks.length" @click="clearTasks" class="clear-btn">
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
const tasks = ref([])       // ✅ Ensure this is always a reactive array
const newTaskTitle = ref('')

// Fetch tasks safely
async function fetchTasks() {
  if (!auth.isAuthenticated) return
  try {
    const res = await api.get('/tasks')
    // Make sure we assign an array
    tasks.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Error fetching tasks:', e)
    tasks.value = [] // fallback
  }
}

// Create new task safely
async function createTask() {
  if (!auth.isAuthenticated || !newTaskTitle.value.trim()) return
  try {
    const res = await api.post('/tasks', { title: newTaskTitle.value })
    // Ensure push only if tasks.value is an array
    if (Array.isArray(tasks.value)) {
      tasks.value.push(res.data)
    } else {
      tasks.value = [res.data]
    }
    newTaskTitle.value = ''
  } catch (e) {
    console.error('Error creating task:', e)
  }
}

// Update task safely
async function updateTask(task) {
  if (!auth.isAuthenticated || !task || !task.title.trim()) return
  try {
    const res = await api.put(`/tasks/${task.id}`, { title: task.title })
    task.title = res.data?.title || task.title
  } catch (e) {
    console.error('Error updating task:', e)
  }
}

// Delete task safely
async function removeTask(id) {
  if (!auth.isAuthenticated || !id) return
  try {
    await api.delete(`/tasks/${id}`)
    tasks.value = tasks.value.filter(t => t?.id !== id)
  } catch (e) {
    console.error('Error deleting task:', e)
  }
}

// Clear all tasks safely
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

onMounted(fetchTasks)
</script>

<style scoped>
.error-message { color: red; font-weight: bold; margin-bottom: 15px; }
.task-form { display: flex; gap: 10px; margin-bottom: 20px; }
.task-form input { flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
.task-form button { padding: 8px 12px; border: none; background: #28a745; color: white; border-radius: 4px; cursor: pointer; }
.task-form button:hover { background: #218838; }
.task-item { display: flex; justify-content: space-between; padding: 6px 0; }
.task-item .task-input { flex: 1; padding: 6px; border-radius: 4px; border: 1px solid #ccc; margin-right: 8px; }
.task-item button { padding: 4px 8px; border: none; background: #dc3545; color: white; border-radius: 4px; cursor: pointer; }
.task-item button:hover { background: #c82333; }
.clear-btn { margin-top: 15px; padding: 8px 12px; border: none; background: #6c757d; color: white; border-radius: 4px; cursor: pointer; }
.clear-btn:hover { background: #5a6268; }
</style>
