import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import TaskItem from '../../components/TaskItem.vue'
import { createPinia, setActivePinia } from 'pinia'

// Mock Pinia ve Auth Store
vi.mock('../../store/useAuthStore', () => ({
  useAuthStore: () => ({
    currentUser: { id: 1 }
  })
}))

describe('TaskItem.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders task title correctly', () => {
    const task = {
      id: 1,
      title: 'Test Görev',
      description: 'Test görev açıklaması',
      status: 'pending',
      user_id: 1,
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task }
    })

    expect(wrapper.find('h3').text()).toBe('Test Görev')
    expect(wrapper.find('p').text()).toBe('Test görev açıklaması')
  })

  it('shows "Admin tarafından oluşturuldu" when task is created by admin', () => {
    const task = {
      id: 1,
      title: 'Admin Görevi',
      description: 'Admin tarafından oluşturulan görev',
      status: 'pending',
      user_id: 1,
      created_by: 2, // Admin kullanıcının ID'si (mevcut kullanıcıdan farklı)
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task }
    })

    expect(wrapper.text()).toContain('Admin tarafından oluşturuldu')
  })

  it('applies correct status class based on task status', async () => {
    const pendingTask = {
      id: 1,
      title: 'Bekleyen Görev',
      description: 'Açıklama',
      status: 'pending',
      user_id: 1,
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task: pendingTask }
    })

    expect(wrapper.classes()).toContain('border-l-4')
    expect(wrapper.attributes('class')).toContain('border-yellow-500')
    
    await wrapper.setProps({
      task: {
        ...pendingTask,
        status: 'in_progress'
      }
    })
    
    expect(wrapper.attributes('class')).toContain('border-blue-500')
  })

  it('emits edit event when edit button is clicked', async () => {
    const task = {
      id: 1,
      title: 'Test Görev',
      description: 'Açıklama',
      status: 'pending',
      user_id: 1,
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task }
    })

    await wrapper.find('button[title="Görevi düzenle"]').trigger('click')
    
    expect(wrapper.emitted().edit).toBeTruthy()
    expect(wrapper.emitted().edit[0]).toEqual([task])
  })

  it('emits status-change event when status button is clicked', async () => {
    const task = {
      id: 1,
      title: 'Test Görev',
      description: 'Açıklama',
      status: 'pending',
      user_id: 1,
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task }
    })

    await wrapper.find('button[class*="bg-blue-100"]').trigger('click')
    
    expect(wrapper.emitted()['status-change']).toBeTruthy()
    expect(wrapper.emitted()['status-change'][0]).toEqual([{ id: 1, status: 'in_progress' }])
  })

  it('shows overdue warning when task end date is in the past', async () => {
    const yesterday = new Date()
    yesterday.setDate(yesterday.getDate() - 1)
    
    const task = {
      id: 1,
      title: 'Geçmiş Görev',
      description: 'Açıklama',
      status: 'pending',
      user_id: 1,
      end_date: yesterday.toISOString().split('T')[0],
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskItem, {
      props: { task }
    })

    expect(wrapper.text()).toContain('Süresi doldu!')
  })
}) 