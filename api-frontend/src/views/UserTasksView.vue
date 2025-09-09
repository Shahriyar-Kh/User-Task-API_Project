<template>
  <div class="user-container">
    <h2>My Tasks</h2>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Loading your tasks...</div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
      <button @click="loadTasks" class="retry-btn">Retry</button>
    </div>

    <!-- Task List -->
    <div v-if="!loading && !error" class="tasks-content">
      <!-- Filter Options -->
      <div class="filters">
        <select v-model="statusFilter" @change="filterTasks" class="filter-select">
          <option value="">All Statuses</option>
          <option value="todo">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
        </select>
        <select v-model="priorityFilter" @change="filterTasks" class="filter-select">
          <option value="">All Priorities</option>
          <option value="low">Low Priority</option>
          <option value="medium">Medium Priority</option>
          <option value="high">High Priority</option>
        </select>
      </div>

      <!-- Task Cards -->
      <div v-if="filteredTasks.length > 0" class="task-grid">
        <div v-for="task in filteredTasks" :key="task.id" class="task-card" :class="getTaskCardClass(task)">
          <div class="task-header">
            <h3 class="task-title">{{ task.title }}</h3>
            <span class="priority-badge" :class="'priority-' + task.priority">
              {{ task.priority?.toUpperCase() || 'MEDIUM' }}
            </span>
          </div>

          <div v-if="task.description" class="task-description">
            {{ task.description }}
          </div>

          <div class="task-meta">
            <p class="assigned-by">
              <strong>Assigned by:</strong> {{ task.creator?.name || 'Admin' }}
            </p>
            <p class="due-date" :class="{ 'overdue': isOverdue(task) }">
              <strong>Due:</strong> {{ task.due_date ? formatDate(task.due_date) : 'No due date' }}
            </p>
          </div>

          <div class="task-actions">
            <div class="status-update">
              <label>Status:</label>
              <select 
                v-model="task.status" 
                @change="updateTaskStatus(task)"
                class="status-select"
                :disabled="updatingTasks.includes(task.id)"
              >
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>
            </div>

            <button 
              @click="editDescription(task)" 
              class="edit-desc-btn"
              :disabled="updatingTasks.includes(task.id)"
            >
              {{ task.showDescEdit ? 'Save' : 'Edit Description' }}
            </button>
          </div>

          <!-- Description Edit Area -->
          <div v-if="task.showDescEdit" class="desc-edit-area">
            <textarea 
              v-model="task.tempDescription"
              placeholder="Add or update task description..."
              class="desc-textarea"
            ></textarea>
            <div class="desc-actions">
              <button @click="saveDescription(task)" class="save-desc-btn">Save</button>
              <button @click="cancelDescEdit(task)" class="cancel-desc-btn">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <p v-if="statusFilter || priorityFilter">
          No tasks found matching the selected filters.
        </p>
        <p v-else>
          No tasks assigned to you yet. Check back later!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/plugins/axios'

const auth = useAuthStore()
const tasks = ref([])
const loading = ref(false)
const error = ref(null)
const updatingTasks = ref([])
const statusFilter = ref('')
const priorityFilter = ref('')

const filteredTasks = computed(() => {
  let filtered = tasks.value

  if (statusFilter.value) {
    filtered = filtered.filter(task => task.status === statusFilter.value)
  }

  if (priorityFilter.value) {
    filtered = filtered.filter(task => task.priority === priorityFilter.value)
  }

  return filtered
})

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString()
  } catch (e) {
    return 'Invalid Date'
  }
}

function isOverdue(task) {
  if (!task.due_date || task.status === 'done') return false
  const today = new Date()
  const dueDate = new Date(task.due_date)
  return dueDate < today
}

function getTaskCardClass(task) {
  const classes = []
  
  if (task.status === 'done') {
    classes.push('task-completed')
  } else if (isOverdue(task)) {
    classes.push('task-overdue')
  }
  
  return classes.join(' ')
}

async function loadTasks() {
  if (!auth.isAuthenticated) {
    error.value = 'Please login to view tasks'
    return
  }

  loading.value = true
  error.value = null
  
  try {
    console.log('Loading user tasks...')
    const { data } = await api.get('/tasks')
    console.log('API Response:', data)
    
    // Handle different response structures
    if (data.success && Array.isArray(data.data)) {
      tasks.value = data.data.map(task => ({
        ...task,
        showDescEdit: false,
        tempDescription: task.description || ''
      }))
    } else if (Array.isArray(data)) {
      tasks.value = data.map(task => ({
        ...task,
        showDescEdit: false,
        tempDescription: task.description || ''
      }))
    } else {
      tasks.value = []
      console.warn("Unexpected API response format:", data)
    }
    
    console.log('Tasks loaded:', tasks.value.length)
  } catch (e) {
    console.error('Error loading tasks:', e)
    const errorMsg = e.response?.data?.error || e.response?.data?.message || e.message || 'Failed to load tasks'
    error.value = errorMsg
    tasks.value = []
  } finally {
    loading.value = false
  }
}

async function updateTaskStatus(task) {
  if (!auth.isAuthenticated || updatingTasks.value.includes(task.id)) return

  updatingTasks.value.push(task.id)
  
  try {
    console.log('Updating task status:', task.id, task.status)
    const { data } = await api.put(`/tasks/${task.id}`, {
      status: task.status
    })
    
    if (data.success && data.task) {
      // Update the task in the list
      const index = tasks.value.findIndex(t => t.id === task.id)
      if (index !== -1) {
        Object.assign(tasks.value[index], data.task, {
          showDescEdit: false,
          tempDescription: data.task.description || ''
        })
      }
      console.log('Task status updated successfully')
    }
  } catch (e) {
    console.error('Error updating task status:', e)
    const errorMsg = e.response?.data?.error || e.response?.data?.message || e.message
    alert('Error updating task: ' + errorMsg)
    
    // Revert the status change
    await loadTasks()
  } finally {
    updatingTasks.value = updatingTasks.value.filter(id => id !== task.id)
  }
}

function editDescription(task) {
  if (task.showDescEdit) {
    saveDescription(task)
  } else {
    task.tempDescription = task.description || ''
    task.showDescEdit = true
  }
}

async function saveDescription(task) {
  if (updatingTasks.value.includes(task.id)) return

  updatingTasks.value.push(task.id)
  
  try {
    console.log('Updating task description:', task.id)
    const { data } = await api.put(`/tasks/${task.id}`, {
      description: task.tempDescription,
      status: task.status
    })
    
    if (data.success && data.task) {
      const index = tasks.value.findIndex(t => t.id === task.id)
      if (index !== -1) {
        Object.assign(tasks.value[index], data.task, {
          showDescEdit: false,
          tempDescription: data.task.description || ''
        })
      }
      console.log('Task description updated successfully')
    }
  } catch (e) {
    console.error('Error updating task description:', e)
    const errorMsg = e.response?.data?.error || e.response?.data?.message || e.message
    alert('Error updating task: ' + errorMsg)
  } finally {
    updatingTasks.value = updatingTasks.value.filter(id => id !== task.id)
  }
}

function cancelDescEdit(task) {
  task.tempDescription = task.description || ''
  task.showDescEdit = false
}

function filterTasks() {
  // This function is called when filters change
  // The computed property will automatically update
}

onMounted(() => {
  console.log('UserTasksView mounted, loading tasks...')
  loadTasks()
})
</script>

<style scoped>
.user-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

h2 {
  color: #2c3e50;
  margin-bottom: 20px;
}

.loading {
  padding: 20px;
  text-align: center;
  color: #7f8c8d;
}

.error-message {
  background-color: #ffecec;
  color: #e74c3c;
  padding: 15px;
  border-radius: 4px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.retry-btn {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
}

.tasks-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  font-size: 14px;
}

.task-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.task-card {
  background: white;
  border: 1px solid #e1e8ed;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: box-shadow 0.2s ease;
}

.task-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.task-card.task-completed {
  background-color: #f8fff8;
  border-color: #d4edda;
}

.task-card.task-overdue {
  background-color: #fff5f5;
  border-color: #f8d7da;
}

.task-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.task-title {
  margin: 0;
  color: #2c3e50;
  font-size: 18px;
  flex: 1;
  margin-right: 10px;
}

.priority-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: bold;
  text-transform: uppercase;
}

.priority-low {
  background-color: #d4edda;
  color: #155724;
}

.priority-medium {
  background-color: #fff3cd;
  color: #856404;
}

.priority-high {
  background-color: #f8d7da;
  color: #721c24;
}

.task-description {
  margin: 10px 0;
  color: #555;
  font-style: italic;
  line-height: 1.4;
}

.task-meta {
  margin: 15px 0;
  font-size: 14px;
  color: #666;
}

.task-meta p {
  margin: 5px 0;
}

.due-date.overdue {
  color: #e74c3c;
  font-weight: bold;
}

.task-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 15px;
  gap: 10px;
}

.status-update {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-update label {
  font-weight: bold;
  color: #2c3e50;
}

.status-select {
  padding: 6px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
}

.edit-desc-btn {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.edit-desc-btn:hover:not(:disabled) {
  background-color: #2980b9;
}

.edit-desc-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.desc-edit-area {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.desc-textarea {
  width: 100%;
  min-height: 80px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  resize: vertical;
  font-family: inherit;
}

.desc-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.save-desc-btn {
  background-color: #2ecc71;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.save-desc-btn:hover {
  background-color: #27ae60;
}

.cancel-desc-btn {
  background-color: #95a5a6;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.cancel-desc-btn:hover {
  background-color: #7f8c8d;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #7f8c8d;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
}

.empty-state p {
  font-size: 16px;
  margin: 0;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .task-grid {
    grid-template-columns: 1fr;
  }
  
  .task-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .status-update {
    justify-content: space-between;
  }
}
</style>