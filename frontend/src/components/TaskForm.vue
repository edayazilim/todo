<template>
  <div class="bg-white p-6 rounded-md shadow-md">
    <h2 class="text-xl font-semibold mb-4">{{ isEditing ? 'İş Düzenle' : 'Yeni İş Ekle' }}</h2>
    
    <div v-if="isAdminCreatedTask" class="mb-4 bg-purple-100 text-purple-800 p-3 rounded-md">
      <strong>Not:</strong> Bu görev admin tarafından oluşturulmuştur. Sadece durumunu değiştirebilirsiniz.
    </div>
    
    <form @submit.prevent="handleSubmit">
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
          Başlık
        </label>
        <input 
          id="title"
          v-model="form.title"
          type="text"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{'bg-gray-100': isAdminCreatedTask}"
          placeholder="İş başlığını girin"
          :disabled="isAdminCreatedTask"
          required
        />
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
          Açıklama
        </label>
        <textarea 
          id="description"
          v-model="form.description"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :class="{'bg-gray-100': isAdminCreatedTask}"
          placeholder="İş açıklamasını girin"
          :disabled="isAdminCreatedTask"
          rows="4"
          required
        ></textarea>
      </div>
      
      <div class="flex gap-4 mb-4">
        <div class="w-1/2">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
            Başlangıç Tarihi
          </label>
          <input 
            id="start_date"
            v-model="form.start_date"
            type="date"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            :class="{'bg-gray-100': isAdminCreatedTask, 'border-red-500': isOnlyOneDateFilled}"
            :disabled="isAdminCreatedTask"
            :max="form.end_date || undefined"
          />
        </div>
        
        <div class="w-1/2">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
            Bitiş Tarihi
          </label>
          <input 
            id="end_date"
            v-model="form.end_date"
            type="date"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            :class="{'bg-gray-100': isAdminCreatedTask, 'border-red-500': isOnlyOneDateFilled}"
            :disabled="isAdminCreatedTask"
            :min="form.start_date || undefined"
          />
          <div v-if="form.end_date && form.start_date && form.end_date < form.start_date" class="text-red-500 text-xs mt-1">
            Bitiş tarihi başlangıç tarihinden önce olamaz
          </div>
        </div>
      </div>
      
      <div v-if="isOnlyOneDateFilled" class="mb-4 text-red-500 text-sm">
        Başlangıç ve bitiş tarihlerinin ikisi de girilmelidir veya ikisi de boş olmalıdır.
      </div>
      
      <div v-if="isAdmin" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="user_id">
          Kullanıcı Seç
        </label>
        <select 
          id="user_id"
          v-model="form.user_id"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
        </select>
      </div>
      
      <div class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
          Durum
        </label>
        <select 
          id="status"
          v-model="form.status"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
          <option value="pending">Beklemede</option>
          <option value="in_progress">Devam Ediyor</option>
          <option value="completed">Tamamlandı</option>
        </select>
      </div>
      
      <div class="flex items-center justify-between">
        <button 
          type="submit"
          class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
          :disabled="!!isDateRangeInvalid"
        >
          {{ isEditing ? 'Güncelle' : 'Ekle' }}
        </button>
        
        <button 
          type="button"
          @click="$emit('cancel')"
          class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
        >
          İptal
        </button>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted, computed } from 'vue';
import type { PropType } from 'vue';
import { useTaskStore } from '../store/useTaskStore';
import { useAuthStore } from '../store/useAuthStore';

interface Task {
  id?: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
  user_id?: number;
  created_by?: number;
  start_date?: string;
  end_date?: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

export default defineComponent({
  name: 'TaskForm',
  props: {
    task: {
      type: Object as PropType<Task | null>,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit', 'cancel'],
  setup(props, { emit }) {
    const taskStore = useTaskStore();
    const authStore = useAuthStore();
    const users = ref<User[]>([]);
    
    const isAdmin = computed(() => authStore.isAdmin);
    const currentUserId = computed(() => authStore.currentUser?.id);
    
    const isAdminCreatedTask = computed(() => {
      return props.isEditing && 
             props.task?.created_by !== undefined && 
             props.task?.created_by !== currentUserId.value &&
             !isAdmin.value;
    });
    
    const form = ref({
      title: '',
      description: '',
      status: 'pending' as 'pending' | 'in_progress' | 'completed',
      user_id: undefined as number | undefined,
      start_date: '',
      end_date: ''
    });
    
    const isDateRangeInvalid = computed(() => {
      return form.value.start_date && form.value.end_date && form.value.end_date < form.value.start_date;
    });
    
    const isOnlyOneDateFilled = computed(() => {
      return (form.value.start_date && !form.value.end_date) || (!form.value.start_date && form.value.end_date);
    });
    
    onMounted(async () => {
      if (isAdmin.value) {
        users.value = await taskStore.fetchUsers();
      }
    });
    
    // Reset form when task prop changes
    watch(() => props.task, (newTask) => {
      if (newTask) {
        
        const formatDate = (dateString: string | undefined): string => {
          if (!dateString) return '';
          try {
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return '';
            
            return date.toISOString().split('T')[0];
          } catch (error) {
            console.error('Tarih formatı dönüştürme hatası:', error);
            return '';
          }
        };
        
        form.value = {
          title: newTask.title,
          description: newTask.description,
          status: newTask.status,
          user_id: newTask.user_id,
          start_date: formatDate(newTask.start_date),
          end_date: formatDate(newTask.end_date)
        };
        
        console.log('Form güncellendi:', form.value);
      } else {
        form.value = {
          title: '',
          description: '',
          status: 'pending',
          user_id: undefined,
          start_date: '',
          end_date: ''
        };
      }
    }, { immediate: true });
    
    const handleSubmit = () => {
      if (isAdminCreatedTask.value) {
        const taskData = {
          id: props.task?.id,
          status: form.value.status
        };
        emit('submit', taskData);
        return;
      }
      
      if (isDateRangeInvalid.value) {
        alert('Bitiş tarihi başlangıç tarihinden önce olamaz');
        return;
      }
      
      if (isOnlyOneDateFilled.value) {
        alert('Başlangıç ve bitiş tarihlerinin ikisi de girilmelidir veya ikisi de boş olmalıdır.');
        return;
      }
      
      const taskData: any = {
        ...form.value
      };
      
      if (props.isEditing && props.task?.id) {
        taskData.id = props.task.id;
      }
      
      emit('submit', taskData);
    };
    
    return {
      form,
      users,
      isAdmin,
      isAdminCreatedTask,
      isDateRangeInvalid,
      isOnlyOneDateFilled,
      handleSubmit
    };
  }
});
</script> 