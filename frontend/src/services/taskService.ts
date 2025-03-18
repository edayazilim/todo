import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

// Axios instance
const apiClient = axios.create({
  baseURL: API_URL,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Request interceptor for adding the auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      console.log('Token MEVCUT - İsteğe ekleniyor:', token.substring(0, 10) + '...');
      config.headers.Authorization = `Bearer ${token}`;
    } else {
      console.log('Token BULUNAMADI - Kimlik doğrulama olmadan istek gönderiliyor');
    }
    return config;
  },
  (error) => {
    console.error('Axios İstek Hatası:', error);
    return Promise.reject(error);
  }
);

// Response interceptor for handling 401 errors (token expired or invalid)
apiClient.interceptors.response.use(
  (response) => {
    console.log('Başarılı API yanıtı:', response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error('API Hatası:', error.response?.status, error.response?.data, error.config?.url);
    
    if (error.response && error.response.status === 401) {
      // Token is invalid or expired
      console.warn('401 Yetkisiz erişim hatası - Çıkış yapılıyor...');
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const taskService = {
  // Get all tasks
  getTasks() {
    return apiClient.get('/tasks');
  },

  // Get single task
  getTask(id: number) {
    return apiClient.get(`/tasks/${id}`);
  },

  // Create a new task
  createTask(task: any) {
    return apiClient.post('/tasks', task);
  },

  // Update a task
  updateTask(task: any) {
    return apiClient.put(`/tasks/${task.id}`, task);
  },

  // Delete a task
  deleteTask(id: number) {
    return apiClient.delete(`/tasks/${id}`);
  },
  
  // Get all users (admin only)
  getAllUsers() {
    return apiClient.get('/users');
  }
};

export const authService = {
  // Register a new user
  register(user: any) {
    return apiClient.post('/register', user);
  },

  // Login user
  login(credentials: any) {
    return apiClient.post('/login', credentials);
  },

  // Logout user
  logout() {
    const token = localStorage.getItem('token');
    return apiClient.post('/logout', {}, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
  }
}; 