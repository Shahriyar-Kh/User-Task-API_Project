<template>
  <div class="admin-container">
    <h2>Admin Panel – Manage Tasks</h2>
    <button @click="showNewTask = true" class="new-task-btn">➕ New Task</button>

    <!-- Loading State -->
    <div v-if="loading" class="loading">Loading tasks...</div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
      <button @click="loadTasks" class="retry-btn">Retry</button>
    </div>

    <!-- Task List -->
    <ul v-if="!loading && !error" class="task-list">
      <li v-for="task in tasks" :key="task.id" class="task-item">
        <div class="task-info">
          <b>{{ task.title }}</b> 
          <span class="task-status">({{ task.status }})</span>
          <p class="task-details">
            Assigned to: <strong>{{ task.user?.name || 'N/A' }}</strong> | 
            Priority: <strong>{{ task.priority || 'medium' }}</strong> |
            Due: <strong>{{ task.due_date ? formatDate(task.due_date) : 'N/A' }}</strong>
          </p>
        </div>
        <div class="task-actions">
          <button @click="editTask(task)" class="edit-btn">✏️ Edit</button>
          <button @click="deleteTask(task.id)" class="delete-btn">🗑️ Delete</button>
        </div>
      </li>
    </ul>

    <!-- Empty State -->
    <div v-if="!loading && !error && tasks.length === 0" class="empty-state">
      No tasks found. Create your first task!
    </div>

    <!-- New Task Modal -->
    <div v-if="showNewTask" class="modal">
      <div class="modal-content">
        <h3>Create New Task</h3>
        <form @submit.prevent="createTask" class="task-form">
          <div class="form-group">
            <label>Task Title *</label>
            <input v-model="newTask.title" placeholder="Enter task title" required />
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="newTask.description" placeholder="Enter task description"></textarea>
          </div>
          
          <div class="form-group">
            <label>Assign to User *</label>
            <select v-model="newTask.user_id" required>
              <option disabled value="">Select a user</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Priority</label>
            <select v-model="newTask.priority">
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Due Date</label>
            <input type="date" v-model="newTask.due_date" />
          </div>
          
          <div class="form-actions">
            <button type="submit" :disabled="creatingTask" class="save-btn">
              {{ creatingTask ? 'Creating...' : 'Create Task' }}
            </button>
            <button type="button" @click="showNewTask = false" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Task Modal -->
    <div v-if="showEditTask" class="modal">
      <div class="modal-content">
        <h3>Edit Task</h3>
        <form @submit.prevent="updateTask" class="task-form">
          <div class="form-group">
            <label>Task Title *</label>
            <input v-model="editTaskData.title" required />
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="editTaskData.description" placeholder="Enter task description"></textarea>
          </div>
          
          <div class="form-group">
            <label>Assign to User *</label>
            <select v-model="editTaskData.user_id" required>
              <option disabled value="">Select a user</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Status</label>
            <select v-model="editTaskData.status">
              <option value="todo">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="done">Done</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Priority</label>
            <select v-model="editTaskData.priority">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Due Date</label>
            <input type="date" v-model="editTaskData.due_date" />
          </div>
          
          <div class="form-actions">
            <button type="submit" :disabled="updatingTask" class="save-btn">
              {{ updatingTask ? 'Updating...' : 'Update Task' }}
            </button>
            <button type="button" @click="showEditTask = false" class="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import api from "@/plugins/axios"

const tasks = ref([])
const users = ref([])
const showNewTask = ref(false)
const showEditTask = ref(false)
const loading = ref(false)
const creatingTask = ref(false)
const updatingTask = ref(false)
const error = ref(null)

const newTask = ref({ 
  title: "", 
  user_id: "",
  description: "",
  priority: "medium",
  due_date: null
})

const editTaskData = ref({})

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

async function loadTasks() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get("/tasks")
    
    // Handle different response structures
    if (Array.isArray(data)) {
      tasks.value = data
    } else if (data && Array.isArray(data.data)) {
      tasks.value = data.data
    } else {
      tasks.value = []
      console.warn("Unexpected API response format:", data)
    }
  } catch (e) {
    console.error("Error loading tasks:", e)
    error.value = e.response?.data?.message || e.message || "Failed to load tasks"
  } finally {
    loading.value = false
  }
}

async function loadUsers() {
  try {
    const { data } = await api.get("/users")
    users.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error("Error loading users:", e)
    alert("Error loading users: " + (e.response?.data?.error || e.message))
    users.value = []
  }
}

async function createTask() {
  creatingTask.value = true
  try {
    const taskData = {
      title: newTask.value.title,
      user_id: newTask.value.user_id,
      description: newTask.value.description || "",
      priority: newTask.value.priority || "medium",
      due_date: newTask.value.due_date || null
    }

    const { data } = await api.post("/tasks", taskData)
    
    if (data.task) {
      tasks.value.push(data.task)
    } else if (data) {
      tasks.value.push(data)
    }
    
    // Reset form
    newTask.value = { 
      title: "", 
      user_id: "",
      description: "",
      priority: "medium",
      due_date: null
    }
    showNewTask.value = false
  } catch (e) {
    console.error("Error creating task:", e)
    alert("Error creating task: " + (e.response?.data?.error || e.message))
  } finally {
    creatingTask.value = false
  }
}

function editTask(task) {
  editTaskData.value = { ...task }
  showEditTask.value = true
}

async function updateTask() {
  updatingTask.value = true
  try {
    const { data } = await api.put(`/tasks/${editTaskData.value.id}`, editTaskData.value)
    
    const index = tasks.value.findIndex(t => t.id === editTaskData.value.id)
    if (index !== -1) {
      if (data.task) {
        tasks.value[index] = data.task
      } else if (data) {
        tasks.value[index] = data
      }
    }
    
    showEditTask.value = false
  } catch (e) {
    console.error("Error updating task:", e)
    alert("Error updating task: " + (e.response?.data?.error || e.message))
  } finally {
    updatingTask.value = false
  }
}

async function deleteTask(id) {
  if (!confirm("Are you sure you want to delete this task?")) return
  
  try {
    await api.delete(`/tasks/${id}`)
    tasks.value = tasks.value.filter(t => t.id !== id)
  } catch (e) {
    console.error("Error deleting task:", e)
    alert("Error deleting task: " + (e.response?.data?.error || e.message))
  }
}

onMounted(() => {
  loadTasks()
  loadUsers()
})
</script>

<style scoped>
.admin-container { 
  padding: 20px; 
  max-width: 1000px;
  margin: 0 auto;
}

h2 {
  color: #2c3e50;
  margin-bottom: 20px;
}

.new-task-btn {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 20px;
  font-size: 16px;
}

.new-task-btn:hover {
  background-color: #2980b9;
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

.empty-state {
  text-align: center;
  padding: 40px;
  color: #7f8c8d;
  font-style: italic;
}

.task-list {
  list-style: none;
  padding: 0;
}

.task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  margin-bottom: 10px;
  background-color: #f9f9f9;
}

.task-info {
  flex: 1;
}

.task-status {
  color: #7f8c8d;
  margin-left: 10px;
}

.task-details {
  margin: 5px 0 0;
  font-size: 14px;
  color: #7f8c8d;
}

.task-actions {
  display: flex;
  gap: 10px;
}

.edit-btn {
  background-color: #f39c12;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.edit-btn:hover {
  background-color: #e67e22;
}

.delete-btn {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.delete-btn:hover {
  background-color: #c0392b;
}

/* Modal */
.modal { 
  position: fixed; 
  top: 0; 
  left: 0; 
  width: 100%; 
  height: 100%; 
  background: rgba(0,0,0,0.5); 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  z-index: 1000;
}

.modal-content { 
  background: #fff; 
  padding: 25px; 
  border-radius: 8px; 
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h3 {
  margin-top: 0;
  color: #2c3e50;
}

.task-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}

.save-btn {
  background-color: #2ecc71;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.save-btn:hover:not(:disabled) {
  background-color: #27ae60;
}

.save-btn:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.cancel-btn {
  background-color: #95a5a6;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.cancel-btn:hover {
  background-color: #7f8c8d;
}

@media (max-width: 768px) {
  .task-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .task-actions {
    margin-top: 10px;
    width: 100%;
    justify-content: flex-end;
  }
}
</style>