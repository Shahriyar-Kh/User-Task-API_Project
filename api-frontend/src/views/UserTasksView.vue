<template>
  <div class="user-container">
    <h2>My Tasks</h2>
    <ul class="task-list">
      <li v-for="task in tasks" :key="task.id">
        <b>{{ task.title }}</b> ({{ task.status }})
        <select v-model="task.status" @change="updateTask(task)">
          <option value="todo">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
        </select>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import api from "@/plugins/axios"

const tasks = ref([])

async function loadTasks() {
  try {
    const { data } = await api.get("/tasks")
    tasks.value = Array.isArray(data.data) ? data.data : []
  } catch (e) {
    console.error("Error loading tasks:", e)
    tasks.value = []
  }
}

async function updateTask(task) {
  try {
    await api.put(`/tasks/${task.id}`, { status: task.status })
  } catch (e) {
    console.error("Error updating task:", e)
  }
}

onMounted(loadTasks)
</script>

<style scoped>
.user-container { padding: 20px; }
.task-list li { margin: 8px 0; }
select { margin-left: 6px; }
</style>
