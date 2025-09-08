import { defineStore } from 'pinia'
import api from '@/plugins/axios'

export const useTaskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
  }),
  actions: {
    async fetchTasks() {
      const { data } = await api.get('/tasks')
      this.tasks = data
    },
    async addTask(task) {
      const { data } = await api.post('/tasks', task)
      this.tasks.push(data)
    },
    async deleteTask(id) {
      await api.delete(`/tasks/${id}`)
      this.tasks = this.tasks.filter((t) => t.id !== id)
    },
  },
})
