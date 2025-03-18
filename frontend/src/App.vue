<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from './store/useAuthStore';

const authStore = useAuthStore();

onMounted(() => {
  authStore.checkAuth();
});
</script>

<template>
  <router-view />
</template>

<style scoped>
/* Global styles are in style.css */
</style>
