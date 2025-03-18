import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../store/useAuthStore'

// localStorage'ı mockla
const localStorageMock = (() => {
  let store: Record<string, string> = {}
  return {
    getItem: vi.fn((key: string) => store[key] || null),
    setItem: vi.fn((key: string, value: string) => {
      store[key] = value.toString()
    }),
    removeItem: vi.fn((key: string) => {
      delete store[key]
    }),
    clear: vi.fn(() => {
      store = {}
    })
  }
})()

// authService'i mockla
vi.mock('../../services/taskService', () => ({
  authService: {
    register: vi.fn(),
    login: vi.fn(),
    logout: vi.fn()
  }
}))

// router'ı mockla
vi.mock('../../router', () => ({
  default: {
    push: vi.fn()
  }
}))

Object.defineProperty(window, 'localStorage', { value: localStorageMock })

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorageMock.clear()
  })

  afterEach(() => {
    vi.resetAllMocks()
  })

  it('başlangıç durumunu doğru ayarlar', () => {
    const store = useAuthStore()
    
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('localStorage\'dan token ve kullanıcıyı yükler', () => {
    const user = { id: 1, name: 'Test User', email: 'test@example.com', role: 'user' }
    localStorageMock.getItem.mockReturnValueOnce('test-token')
    localStorageMock.getItem.mockReturnValueOnce(JSON.stringify(user))
    
    const store = useAuthStore()
    
    expect(store.token).toBe('test-token')
    expect(store.user).toEqual(user)
  })

  it('isAuthenticated getter token varsa true döner', () => {
    const store = useAuthStore()
    store.$patch({ token: 'test-token' })
    
    expect(store.isAuthenticated).toBe(true)
  })

  it('isAdmin getter kullanıcı admin ise true döner', () => {
    const store = useAuthStore()
    store.$patch({ user: { id: 1, name: 'Admin', email: 'admin@example.com', role: 'admin' } })
    
    expect(store.isAdmin).toBe(true)
  })

  it('register fonksiyonu başarılı kayıt durumunda localStorage\'a veri kaydeder', async () => {
    const store = useAuthStore()
    const user = { name: 'New User', email: 'new@example.com', password: 'password', password_confirmation: 'password' }
    const mockResponse = {
      data: {
        token: 'new-token',
        user: { id: 2, name: 'New User', email: 'new@example.com', role: 'user' }
      }
    }
    
    const { authService } = await import('../../services/taskService')
    vi.mocked(authService.register).mockResolvedValue(mockResponse)
    
    const result = await store.register(user)
    
    expect(result).toBe(true)
    expect(store.token).toBe('new-token')
    expect(store.user).toEqual(mockResponse.data.user)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
    expect(localStorageMock.setItem).toHaveBeenCalledWith('token', 'new-token')
    expect(localStorageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(mockResponse.data.user))
  })

  it('login fonksiyonu başarılı giriş durumunda localStorage\'a veri kaydeder', async () => {
    const store = useAuthStore()
    const credentials = { email: 'user@example.com', password: 'password' }
    const mockResponse = {
      data: {
        token: 'login-token',
        user: { id: 3, name: 'Existing User', email: 'user@example.com', role: 'user' }
      }
    }
    
    const { authService } = await import('../../services/taskService')
    vi.mocked(authService.login).mockResolvedValue(mockResponse)
    
    const result = await store.login(credentials)
    
    expect(result).toBe(true)
    expect(store.token).toBe('login-token')
    expect(store.user).toEqual(mockResponse.data.user)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
    expect(localStorageMock.setItem).toHaveBeenCalledWith('token', 'login-token')
    expect(localStorageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(mockResponse.data.user))
  })

  it('logout fonksiyonu localStorage\'dan veriyi temizler', async () => {
    const store = useAuthStore()
    store.$patch({ 
      token: 'test-token', 
      user: { id: 1, name: 'Test User', email: 'test@example.com', role: 'user' } 
    })
    
    const { authService } = await import('../../services/taskService')
    vi.mocked(authService.logout).mockResolvedValue({})
    
    await store.logout()
    
    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(localStorageMock.removeItem).toHaveBeenCalledWith('token')
    expect(localStorageMock.removeItem).toHaveBeenCalledWith('user')
  })
}) 