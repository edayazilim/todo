<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Bildirim alanı -->
    <div v-if="notification.show" :class="['fixed top-4 right-4 px-4 py-2 rounded-md shadow-md z-50', 
      notification.type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 
      notification.type === 'error' ? 'bg-red-100 text-red-800 border-l-4 border-red-500' : 
      'bg-blue-100 text-blue-800 border-l-4 border-blue-500']">
      {{ notification.message }}
      <button @click="closeNotification" class="ml-2 text-sm font-bold">&times;</button>
    </div>
    
    <!-- Navbar -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-indigo-600">İş Yönetim Sistemi</h1>
            </div>
          </div>
          <div class="flex items-center">
            <span class="text-gray-700 mr-4">{{ user?.name }}</span>
            <span v-if="isAdmin" class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs mr-4">Admin</span>
            <button @click="logout" class="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200">
              Çıkış Yap
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page header -->
      <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            İşlerim
          </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <button @click="showTaskForm = !showTaskForm" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ showTaskForm ? 'İptal' : 'Yeni İş Ekle' }}
          </button>
        </div>
      </div>
      
      <!-- Task form -->
      <div v-if="showTaskForm" class="bg-white shadow-md rounded-md mb-6">
        <TaskForm 
          :task="selectedTask" 
          :is-editing="!!selectedTask" 
          @submit="handleTaskSubmit" 
          @cancel="cancelTaskForm"
        />
      </div>
      
      <!-- Task list -->
      <TaskList 
        :tasks="tasks" 
        :loading="loading" 
        :error="error"
        @edit="editTask"
        @delete="deleteTask"
        @status-change="updateTaskStatus"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed, onMounted, reactive } from 'vue';
import type { Router } from 'vue-router';
import { useRouter } from 'vue-router';
import { useTaskStore } from '../store/useTaskStore';
import { useAuthStore } from '../store/useAuthStore';
import TaskList from '../components/TaskList.vue';
import TaskForm from '../components/TaskForm.vue';

interface Task {
  id: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
}

export default defineComponent({
  name: 'DashboardPage',
  components: {
    TaskList,
    TaskForm
  },
  setup() {
    const taskStore = useTaskStore();
    const authStore = useAuthStore();
    const router = useRouter();
    
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      router.push('/login');
    }
    
    const showTaskForm = ref(false);
    const selectedTask = ref(null);
    
    const tasks = computed(() => taskStore.getTasks);
    const loading = computed(() => taskStore.loading);
    const error = computed(() => taskStore.error || undefined);
    const user = computed(() => authStore.currentUser);
    const isAdmin = computed(() => authStore.isAdmin);
    
    const notification = reactive({
      show: false,
      message: '',
      type: 'info' as 'success' | 'error' | 'info'
    });
    
    const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
      notification.message = message;
      notification.type = type;
      notification.show = true;
      
      setTimeout(() => {
        notification.show = false;
      }, 3000);
    };
    
    const closeNotification = () => {
      notification.show = false;
    };
    
    onMounted(() => {
      taskStore.fetchTasks();
    });
    
    const handleTaskSubmit = async (task: any) => {
      let result;
      
      if (selectedTask.value) {
        result = await taskStore.updateTask(task);
        if (result) {
          showNotification('Görev başarıyla güncellendi.', 'success');
        }
      } else {
        result = await taskStore.createTask(task);
        if (result) {
          showNotification('Yeni görev başarıyla oluşturuldu.', 'success');
        }
      }
      
      cancelTaskForm();
    };
    
    const cancelTaskForm = () => {
      showTaskForm.value = false;
      selectedTask.value = null;
    };
    
    const editTask = (task: any) => {
      selectedTask.value = task;
      showTaskForm.value = true;
    };
    
    const deleteTask = async (id: number) => {
      const result = await taskStore.deleteTask(id);
      if (result) {
        showNotification('Görev başarıyla silindi.', 'success');
      }
    };
    
    const updateTaskStatus = async (data: { id: number; status: 'pending' | 'in_progress' | 'completed' }) => {
      const result = await taskStore.updateTask(data);
      if (result) {
        const statusText = data.status === 'pending' ? 'Bekliyor' : 
                           data.status === 'in_progress' ? 'Devam Ediyor' : 'Tamamlandı';
        showNotification(`Görev durumu "${statusText}" olarak güncellendi.`, 'success');
      }
    };
    
    const logout = async () => {
      await authStore.logout();
      router.push('/login');
    };
    
    return {
      tasks,
      loading,
      error,
      user,
      isAdmin,
      showTaskForm,
      selectedTask,
      handleTaskSubmit,
      cancelTaskForm,
      editTask,
      deleteTask,
      updateTaskStatus,
      logout,
      notification,
      closeNotification
    };
  }
});
</script> 