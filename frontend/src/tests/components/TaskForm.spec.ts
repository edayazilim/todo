import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import TaskForm from '../../components/TaskForm.vue'
import { createPinia, setActivePinia } from 'pinia'

// Mock Pinia Store
vi.mock('../../store/useAuthStore', () => ({
  useAuthStore: () => ({
    currentUser: { id: 1, is_admin: false },
    isAdmin: false
  })
}))

vi.mock('../../store/useTaskStore', () => ({
  useTaskStore: () => ({
    users: [],
    fetchUsers: vi.fn().mockResolvedValue([])
  })
}))

describe('TaskForm.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders form with correct inputs', () => {
    const wrapper = mount(TaskForm)

    expect(wrapper.find('input#title').exists()).toBe(true)
    expect(wrapper.find('textarea#description').exists()).toBe(true)
    expect(wrapper.find('select#status').exists()).toBe(true)
    expect(wrapper.find('input#start_date').exists()).toBe(true)
    expect(wrapper.find('input#end_date').exists()).toBe(true)
  })

  it('initializes with empty form data', () => {
    const wrapper = mount(TaskForm)
    
    const titleInput = wrapper.find('input#title')
    const descriptionInput = wrapper.find('textarea#description')
    
    expect(titleInput.element.value).toBe('')
    expect(descriptionInput.element.value).toBe('')
  })

  it('populates form with task data when editing', async () => {
    const task = {
      id: 1,
      title: 'Mevcut Görev',
      description: 'Görev açıklaması',
      status: 'in_progress',
      user_id: 1,
      start_date: '2023-03-25',
      end_date: '2023-03-30',
      created_at: '2023-03-20T10:00:00.000Z',
      updated_at: '2023-03-20T10:00:00.000Z'
    }

    const wrapper = mount(TaskForm, {
      props: { 
        task,
        isEditing: true
      }
    })

    await flushPromises()
    
    const titleInput = wrapper.find('input#title')
    const descriptionInput = wrapper.find('textarea#description')
    const statusSelect = wrapper.find('select#status')
    const startDateInput = wrapper.find('input#start_date')
    const endDateInput = wrapper.find('input#end_date')
    
    expect(titleInput.element.value).toBe('Mevcut Görev')
    expect(descriptionInput.element.value).toBe('Görev açıklaması')
    expect(statusSelect.element.value).toBe('in_progress')
    expect(startDateInput.element.value).toBe('2023-03-25')
    expect(endDateInput.element.value).toBe('2023-03-30')
  })

  it('validates date range', async () => {
    const wrapper = mount(TaskForm)
    
    await wrapper.find('input#start_date').setValue('2023-03-30')
    await wrapper.find('input#end_date').setValue('2023-03-25')
    
    expect(wrapper.text()).toContain('Bitiş tarihi başlangıç tarihinden önce olamaz')
    
    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeDefined()
  })

  it('emits submit event with form data on valid submit', async () => {
    const wrapper = mount(TaskForm)
    
    await wrapper.find('input#title').setValue('Yeni Görev')
    await wrapper.find('textarea#description').setValue('Görev açıklaması')
    await wrapper.find('select#status').setValue('pending')
    await wrapper.find('input#start_date').setValue('2023-03-25')
    await wrapper.find('input#end_date').setValue('2023-03-30')
    
    await wrapper.find('form').trigger('submit.prevent')
    
    expect(wrapper.emitted().submit).toBeTruthy()
    expect(wrapper.emitted().submit[0][0]).toEqual({
      title: 'Yeni Görev',
      description: 'Görev açıklaması',
      status: 'pending',
      user_id: undefined,
      start_date: '2023-03-25',
      end_date: '2023-03-30'
    })
  })

  it('emits cancel event when cancel button is clicked', async () => {
    const wrapper = mount(TaskForm)
    
    await wrapper.find('button[type="button"]').trigger('click')
    
    expect(wrapper.emitted().cancel).toBeTruthy()
  })
}) 