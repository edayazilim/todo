<template>
  <div>
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>
    
    <div v-else-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p class="font-bold">Hata</p>
      <p>{{ error }}</p>
    </div>
    
    <div v-else-if="tasks.length === 0" class="bg-gray-100 rounded-md p-8 text-center">
      <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz hiç iş yok</h3>
      <p class="text-gray-500">Yeni bir iş eklemek için "Yeni İş Ekle" butonuna tıklayın.</p>
    </div>
    
    <div v-else>
      <h3 class="text-lg font-semibold mb-4">Bekleyen İşler</h3>
      <div v-if="pendingTasks.length === 0" class="text-gray-500 mb-4">Bekleyen iş bulunmuyor.</div>
      <div v-else>
        <TaskItem 
          v-for="task in pendingTasks" 
          :key="task.id" 
          :task="task" 
          @edit="$emit('edit', task)"
          @delete="$emit('delete', task.id)"
          @status-change="$emit('status-change', $event)"
        />
      </div>
      
      <h3 class="text-lg font-semibold mb-4 mt-8">Devam Eden İşler</h3>
      <div v-if="inProgressTasks.length === 0" class="text-gray-500 mb-4">Devam eden iş bulunmuyor.</div>
      <div v-else>
        <TaskItem 
          v-for="task in inProgressTasks" 
          :key="task.id" 
          :task="task" 
          @edit="$emit('edit', task)"
          @delete="$emit('delete', task.id)"
          @status-change="$emit('status-change', $event)"
        />
      </div>
      
      <h3 class="text-lg font-semibold mb-4 mt-8">Tamamlanan İşler</h3>
      <div v-if="completedTasks.length === 0" class="text-gray-500 mb-4">Tamamlanan iş bulunmuyor.</div>
      <div v-else>
        <TaskItem 
          v-for="task in completedTasks" 
          :key="task.id" 
          :task="task" 
          @edit="$emit('edit', task)"
          @delete="$emit('delete', task.id)"
          @status-change="$emit('status-change', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import type { PropType } from 'vue';
import TaskItem from './TaskItem.vue';

interface Task {
  id: number;
  title: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
  user_id: number;
  created_at: string;
  updated_at: string;
}

export default defineComponent({
  name: 'TaskList',
  components: {
    TaskItem
  },
  props: {
    tasks: {
      type: Array as PropType<Task[]>,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
    }
  },
  emits: ['edit', 'delete', 'status-change'],
  setup(props) {
    const pendingTasks = computed(() => 
      props.tasks.filter(task => task.status === 'pending')
    );
    
    const inProgressTasks = computed(() => 
      props.tasks.filter(task => task.status === 'in_progress')
    );
    
    const completedTasks = computed(() => 
      props.tasks.filter(task => task.status === 'completed')
    );
    
    return {
      pendingTasks,
      inProgressTasks,
      completedTasks
    };
  }
});
</script> 