import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import Login from '../../pages/Login.vue'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'

vi.mock('../../store/useAuthStore', () => ({
  useAuthStore: vi.fn(() => ({
    login: vi.fn(),
    loading: false,
    error: null
  }))
}))

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div></div>' } },
    { path: '/dashboard', component: { template: '<div></div>' } },
    { path: '/register', component: { template: '<div></div>' } }
  ]
})

describe('Login.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    router.push('/')
  })

  it('giriş formunu doğru şekilde render eder', () => {
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    expect(wrapper.find('h2').text()).toContain('İş Yönetim Sistemi')
    expect(wrapper.find('input[type="email"]').exists()).toBe(true)
    expect(wrapper.find('input[type="password"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').text()).toContain('Giriş Yap')
  })

  it('email ve şifre alanları için v-model bağlantısı kurar', async () => {
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')
    
    await emailInput.setValue('test@example.com')
    await passwordInput.setValue('password123')
    
    expect(wrapper.vm.credentials.email).toBe('test@example.com')
    expect(wrapper.vm.credentials.password).toBe('password123')
  })

  it('form gönderildiğinde login metodunu çağırır', async () => {
    const { useAuthStore } = await import('../../store/useAuthStore')
    const mockAuthStore = {
      login: vi.fn().mockResolvedValue(true),
      loading: false,
      error: null
    }
    vi.mocked(useAuthStore).mockReturnValue(mockAuthStore)
    
    const mockRouterPush = vi.spyOn(router, 'push')
    
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    await wrapper.find('input[type="email"]').setValue('test@example.com')
    await wrapper.find('input[type="password"]').setValue('password123')
    
    await wrapper.find('form').trigger('submit.prevent')
    
    expect(mockAuthStore.login).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123'
    })
    
    await flushPromises()
    
    expect(mockRouterPush).toHaveBeenCalledWith('/dashboard')
  })

  it('giriş başarısız olduğunda hata mesajını gösterir', async () => {
    const { useAuthStore } = await import('../../store/useAuthStore')
    const mockAuthStore = {
      login: vi.fn().mockResolvedValue(false),
      loading: false,
      error: 'Geçersiz e-posta veya şifre'
    }
    vi.mocked(useAuthStore).mockReturnValue(mockAuthStore)
    
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    
    expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    expect(wrapper.find('[role="alert"]').text()).toContain('Geçersiz e-posta veya şifre')
  })

  it('yükleme durumunda butonun devre dışı olduğunu gösterir', async () => {
    const { useAuthStore } = await import('../../store/useAuthStore')
    const mockAuthStore = {
      login: vi.fn().mockReturnValue(new Promise(resolve => setTimeout(() => resolve(true), 100))),
      loading: true,
      error: null
    }
    vi.mocked(useAuthStore).mockReturnValue(mockAuthStore)
    
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
    expect(wrapper.find('svg.animate-spin').exists()).toBe(true)
  })

  it('kayıt ol sayfasına yönlendiren bağlantı içerir', () => {
    const wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
    
    const registerLink = wrapper.find('a')
    
    expect(registerLink.text()).toContain('Kayıt Ol')
    expect(registerLink.attributes('href')).toBe('/register')
  })
}) 