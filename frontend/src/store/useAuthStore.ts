import { defineStore } from 'pinia';
import { authService } from '../services/taskService';
import router from '../router';

interface User {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin';
}

interface AuthState {
  token: string | null;
  user: User | null;
  loading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    currentUser: (state) => state.user
  },

  actions: {
    async register(user: { name: string; email: string; password: string; password_confirmation: string }) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await authService.register(user);
        this.token = response.data.token;
        this.user = response.data.user;
        
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Kayıt işlemi başarısız';
        console.error('Registration error:', error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    async login(credentials: { email: string; password: string }) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await authService.login(credentials);
        if (response.data && response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          
          // Token ve kullanıcı verilerini localStorage'a kaydet
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user', JSON.stringify(response.data.user));
          
          return true;
        } else {
          throw new Error('Token alınamadı');
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Giriş başarısız';
        console.error('Login error:', error);
        return false;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      this.error = null;
      
      try {
        if (this.token) {
          await authService.logout();
        }
        
        this.token = null;
        this.user = null;
        
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        
        router.push('/login');
        
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Çıkış başarısız';
        console.error('Logout error:', error);
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    checkAuth() {
      const token = localStorage.getItem('token');
      const user = localStorage.getItem('user');
      
      if (!token || !user) {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        return false;
      }
      
      this.token = token;
      this.user = JSON.parse(user);
      return true;
    }
  }
}); 