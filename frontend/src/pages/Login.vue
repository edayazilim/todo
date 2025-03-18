<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          İş Yönetim Sistemi
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Hesabınıza giriş yapın
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="login">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email-address" class="sr-only">E-posta</label>
            <input id="email-address" name="email" type="email" autocomplete="email" required
                   v-model="credentials.email"
                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                   placeholder="E-posta adresi" />
          </div>
          <div>
            <label for="password" class="sr-only">Şifre</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   v-model="credentials.password"
                   class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                   placeholder="Şifre" />
          </div>
        </div>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ error }}</span>
        </div>

        <div>
          <button type="submit" 
                  :disabled="loading"
                  class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Giriş Yap
          </button>
        </div>
      </form>
      
      <div class="text-center">
        <p class="mt-2 text-sm text-gray-600">
          Hesabınız yok mu?
          <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
            Kayıt Ol
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/useAuthStore';

export default defineComponent({
  name: 'LoginPage',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    
    const credentials = ref({
      email: '',
      password: ''
    });
    
    const loading = computed(() => authStore.loading);
    const error = computed(() => authStore.error);
    
    const login = async () => {
      const success = await authStore.login(credentials.value);
      if (success) {
        router.push('/dashboard');
      }
    };
    
    return {
      credentials,
      loading,
      error,
      login
    };
  }
});
</script> 