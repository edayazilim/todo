<template>
  <div class="bg-white shadow-md rounded-md p-4 mb-4 border-l-4 transition-all"
       :class="{
         'border-yellow-500': task.status === 'pending',
         'border-blue-500': task.status === 'in_progress',
         'border-green-500': task.status === 'completed',
         'border-purple-500': isCreatedByAdmin
       }">
    <div class="flex justify-between items-start mb-2">
      <div>
        <h3 class="text-lg font-semibold">{{ task.title }}</h3>
        <div v-if="isCreatedByAdmin" class="text-xs text-purple-600 font-medium mb-1">
          Admin tarafından oluşturuldu
        </div>
      </div>
      
      <div class="flex space-x-2">
        <span class="px-2 py-1 text-xs rounded-full"
              :class="{
                'bg-yellow-100 text-yellow-800': task.status === 'pending',
                'bg-blue-100 text-blue-800': task.status === 'in_progress',
                'bg-green-100 text-green-800': task.status === 'completed',
              }">
          {{ formatStatus(task.status) }}
        </span>
        
        <button @click="updateStatus('pending')" 
                class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200"
                v-if="task.status !== 'pending'">
          Beklemede
        </button>
        
        <button @click="updateStatus('in_progress')" 
                class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200"
                v-if="task.status !== 'in_progress'">
          Devam Ediyor
        </button>
        
        <button @click="updateStatus('completed')" 
                class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200"
                v-if="task.status !== 'completed'">
          Tamamlandı
        </button>
      </div>
    </div>
    
    <p class="text-gray-600 mb-3">{{ task.description }}</p>
    
    <div v-if="task.start_date || task.end_date" class="flex flex-wrap gap-4 mb-3 text-sm">
      <div v-if="task.start_date" class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-gray-600">Başlangıç: {{ formatShortDate(task.start_date) }}</span>
      </div>
      
      <div v-if="task.end_date" class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-gray-600">Bitiş: {{ formatShortDate(task.end_date) }}</span>
      </div>
      
      <div v-if="isTaskOverdue" class="flex items-center text-red-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Süresi doldu!</span>
      </div>
      
      <div v-if="isTaskApproaching && !isTaskDueSoon && !isTaskOverdue" class="flex items-center text-orange-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ daysUntilDue }} gün kaldı!</span>
      </div>
    </div>
    
    <div class="flex justify-between items-center">
      <div class="text-xs text-gray-500">
        {{ formatDate(task.created_at) }}
      </div>
      
      <div class="flex space-x-2">
        <button @click="$emit('edit', task)" 
                class="px-3 py-1 bg-gray-100 text-gray-800 rounded hover:bg-gray-200"
                :title="isCreatedByAdmin ? 'Admin tarafından oluşturulan görevlerde sadece durum değiştirilebilir' : 'Görevi düzenle'">
          Düzenle
        </button>
        
        <button @click="confirmDelete" 
                class="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200"
                :disabled="isCreatedByAdmin"
                :class="{'opacity-50 cursor-not-allowed': isCreatedByAdmin}"
                :title="isCreatedByAdmin ? 'Admin tarafından oluşturulan görevler silinemez' : 'Görevi sil'">
          Sil
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import type { PropType } from 'vue';
import { useAuthStore } from '../store/useAuthStore';

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

export default defineComponent({
  name: 'TaskItem',
  props: {
    task: {
      type: Object as PropType<Task>,
      required: true
    }
  },
  emits: ['edit', 'delete', 'status-change'],
  setup(props) {
    const authStore = useAuthStore();
    
    const isCreatedByAdmin = computed(() => {
      return props.task.created_by !== undefined && 
             props.task.created_by !== authStore.currentUser?.id;
    });
    
    const isTaskOverdue = computed(() => {
      if (!props.task.end_date || props.task.status === 'completed') return false;
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const endDate = new Date(props.task.end_date);
      return endDate < today;
    });
    
    const isTaskDueSoon = computed(() => {
      if (!props.task.end_date || props.task.status === 'completed') return false;
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const endDate = new Date(props.task.end_date);
      const timeDiff = endDate.getTime() - today.getTime();
      const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
      
      return dayDiff >= 0 && dayDiff <= 3;
    });
    
    const isTaskApproaching = computed(() => {
      if (!props.task.end_date || props.task.status === 'completed') return false;
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const endDate = new Date(props.task.end_date);
      const timeDiff = endDate.getTime() - today.getTime();
      const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
      
      return dayDiff > 3 && dayDiff <= 5;
    });
    
    const daysUntilDue = computed(() => {
      if (!props.task.end_date) return 0;
      
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const endDate = new Date(props.task.end_date);
      const timeDiff = endDate.getTime() - today.getTime();
      return Math.ceil(timeDiff / (1000 * 3600 * 24));
    });
    
    return {
      isCreatedByAdmin,
      isTaskOverdue,
      isTaskDueSoon,
      isTaskApproaching,
      daysUntilDue
    };
  },
  methods: {
    formatStatus(status: string): string {
      const statusMap: Record<string, string> = {
        'pending': 'Beklemede',
        'in_progress': 'Devam Ediyor',
        'completed': 'Tamamlandı'
      };
      return statusMap[status] || status;
    },
    formatDate(dateString: string): string {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },
    formatShortDate(dateString: string | undefined): string {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      }).format(date);
    },
    updateStatus(status: 'pending' | 'in_progress' | 'completed') {
      this.$emit('status-change', { id: this.task.id, status });
    },
    confirmDelete() {
      if (this.isCreatedByAdmin) {
        alert('Admin tarafından oluşturulan görevler silinemez.');
        return;
      }
      
      if (confirm('Bu işi silmek istediğinizden emin misiniz?')) {
        this.$emit('delete', this.task.id);
      }
    }
  }
});
</script> 