import { defineStore } from 'pinia';
import { taskService } from '../services/taskService';

interface Task {
  id: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
  user_id: number;
  created_by?: number;
  start_date?: string;
  end_date?: string;
  created_at: string;
  updated_at: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

interface TaskState {
  tasks: Task[];
  users: User[];
  loading: boolean;
  error: string | null;
  currentTask: Task | null;
}

export const useTaskStore = defineStore('task', {
  state: (): TaskState => ({
    tasks: [],
    users: [],
    loading: false,
    error: null,
    currentTask: null
  }),

  getters: {
    getTasks: (state) => state.tasks,
    getUsers: (state) => state.users,
    getTaskById: (state) => (id: number) => {
      return state.tasks.find(task => task.id === id) || null;
    },
    getTasksByStatus: (state) => (status: string) => {
      return state.tasks.filter(task => task.status === status);
    },
    pendingTasks: (state) => state.tasks.filter(task => task.status === 'pending'),
    inProgressTasks: (state) => state.tasks.filter(task => task.status === 'in_progress'),
    completedTasks: (state) => state.tasks.filter(task => task.status === 'completed'),
  },

  actions: {
    clearError() {
      this.error = null;
    },
    
    async fetchTasks() {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.getTasks();
        this.tasks = response.data.data;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Görevler yüklenirken hata oluştu';
        console.error('Error fetching tasks:', error);
      } finally {
        this.loading = false;
      }
    },

    async fetchUsers() {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.getAllUsers();
        this.users = response.data.data;
        return this.users;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Kullanıcılar yüklenirken hata oluştu';
        console.error('Error fetching users:', error);
        return [];
      } finally {
        this.loading = false;
      }
    },

    async fetchTask(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.getTask(id);
        this.currentTask = response.data.data;
        return this.currentTask;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Görev detayı yüklenirken hata oluştu';
        console.error('Error fetching task:', error);
        return null;
      } finally {
        this.loading = false;
      }
    },

    async createTask(task: Omit<Task, 'id' | 'created_by' | 'created_at' | 'updated_at'>) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.createTask(task);
        const newTask = response.data.data;
        this.tasks = [newTask, ...this.tasks].sort((a, b) => {
          const aDate = a.start_date ? new Date(a.start_date) : new Date(a.created_at);
          const bDate = b.start_date ? new Date(b.start_date) : new Date(b.created_at);
          return aDate.getTime() - bDate.getTime();
        });
        return newTask;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Görev oluşturulurken hata oluştu';
        console.error('Error creating task:', error);
        return null;
      } finally {
        this.loading = false;
      }
    },

    async updateTask(task: Partial<Task> & { id: number }) {
      this.loading = true;
      this.error = null;
      try {
        const response = await taskService.updateTask(task);
        const updatedTask = response.data.data;
        
        const index = this.tasks.findIndex(t => t.id === task.id);
        if (index !== -1) {
          this.tasks = [
            ...this.tasks.slice(0, index),
            updatedTask,
            ...this.tasks.slice(index + 1)
          ];
        }
        return updatedTask;
      } catch (error: any) {
        if (error.response?.status === 403 && error.response?.data?.message) {
          this.error = error.response.data.message;
          alert(this.error);
        } else {
          this.error = error.response?.data?.message || 'Görev güncellenirken hata oluştu';
        }
        console.error('Error updating task:', error);
        return null;
      } finally {
        this.loading = false;
      }
    },

    async deleteTask(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await taskService.deleteTask(id);
        this.tasks = this.tasks.filter(task => task.id !== id);
        return true;
      } catch (error: any) {
        if (error.response?.status === 403 && error.response?.data?.message) {
          this.error = error.response.data.message;
          alert(this.error);
        } else {
          this.error = error.response?.data?.message || 'Görev silinirken hata oluştu';
        }
        console.error('Error deleting task:', error);
        return false;
      } finally {
        this.loading = false;
      }
    }
  }
}); 