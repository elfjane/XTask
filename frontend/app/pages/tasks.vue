<template>
  <div class="page">
    <div class="header">
      <h1>{{ $t('tasks.title') }}</h1>
      <button @click="showCreateModal = true" class="btn-primary">{{ $t('tasks.addTask') }}</button>
    </div>

    <div v-if="pending" class="loading">{{ $t('login.loggingIn') ? $t('login.loggingIn') : 'Loading...' }}</div>
    <div v-else-if="error" class="error">{{ error.message }}</div>
    
    <div v-else class="content-wrapper">
      <!-- Desktop View (Table) -->
      <div class="desktop-view">
        <table class="task-table">
          <thead>
            <tr>
              <th>{{ $t('tasks.idx') }}</th>
              <th>{{ $t('tasks.level') }}</th>
              <th>{{ $t('tasks.status') }}</th>
              <th>{{ $t('tasks.assignee') }}</th>
              <th>{{ $t('tasks.relatedPersonnel') }}</th>
              <th>{{ $t('tasks.project') }}</th>
              <th>{{ $t('tasks.item') }}</th>
              <th>{{ $t('tasks.department') }}</th>
              <th>{{ $t('tasks.work') }}</th>
              <th>{{ $t('tasks.points') }}</th>
              <th>{{ $t('tasks.releaseDate') }}</th>
              <th>{{ $t('tasks.startDate') }}</th>
              <th>{{ $t('tasks.expectedFinishDate') }}</th>
              <th>{{ $t('tasks.actualFinishDate') }}</th>
              <th>{{ $t('tasks.outputUrl') }}</th>
              <th>{{ $t('tasks.memo') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in tasks" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.level }}</td>
              <td>
                <span :class="['status-badge', item.status.replace(' ', '-')]">
                  {{ $t(`tasks.${item.status.replace(' ', '_')}`) }}
                </span>
              </td>
              <td class="user-cell clickable" @click="startEditingAssignee(item)">
                <div v-if="editingAssigneeId === item.id" class="edit-select-wrapper" @click.stop>
                  <select 
                    :value="item.user_id" 
                    @change="updateAssignee(item, ($event.target as HTMLSelectElement).value)"
                    @blur="editingAssigneeId = null"
                    ref="assigneeSelect"
                  >
                    <option v-for="opt in userOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
                <div v-else class="user-info">
                  <img v-if="item.assignee" :src="item.assignee.photo_url || 'https://ui-avatars.com/api/?name=' + item.assignee.name" class="avatar" />
                  {{ item.assignee?.name || '-' }}
                </div>
              </td>
              <td>{{ item.related_personnel || '-' }}</td>
              <td>{{ item.project }}</td>
              <td>{{ item.item || '-' }}</td>
              <td>{{ item.department }}</td>
              <td class="work-cell">{{ item.work }}</td>
              <td>{{ item.points }}</td>
              <td>{{ item.release_date || '-' }}</td>
              <td>{{ item.start_date || '-' }}</td>
              <td>{{ item.expected_finish_date || '-' }}</td>
              <td>{{ item.actual_finish_date || '-' }}</td>
              <td>
                <a v-if="item.output_url" :href="item.output_url" target="_blank" class="link-btn">🔗</a>
                <span v-else>-</span>
              </td>
              <td class="memo-cell">
                <div class="memo-list">
                  <div v-for="remark in item.remarks" :key="remark.id" class="memo-item">
                    <span class="memo-user">{{ remark.user_name }}:</span>
                    <span class="memo-content">{{ remark.content }}</span>
                  </div>
                </div>
                <div class="memo-add">
                  <input 
                    v-model="newRemarks[item.id]" 
                    :placeholder="$t('schedules.addMemoPlaceHolder')" 
                    @keyup.enter="handlePostRemark(item.id)"
                  />
                  <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                    {{ postingRemark === item.id ? '...' : '➔' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View (Cards) -->
      <div class="mobile-view">
        <div v-for="item in tasks" :key="item.id" class="card">
          <div class="card-header">
            <span class="idx">#{{ item.id }}</span>
            <span :class="['status', item.status.replace(' ', '-')]">{{ $t(`tasks.${item.status.replace(' ', '_')}`) }}</span>
          </div>
          <div class="card-body">
            <h2 class="work-title">{{ item.work }}</h2>
            <div class="info-grid">
              <div class="info-item"><strong>{{ $t('tasks.project') }}:</strong> {{ item.project }}</div>
              <div class="info-item"><strong>{{ $t('tasks.assignee') }}:</strong> {{ item.assignee?.name || '-' }}</div>
              <div class="info-item"><strong>{{ $t('tasks.expectedFinishDate') }}:</strong> {{ item.expected_finish_date || '-' }}</div>
            </div>
            <div class="memo-board">
              <strong>{{ $t('tasks.memo') }}:</strong>
              <div class="memo-list mobile">
                <div v-for="remark in item.remarks" :key="remark.id" class="memo-item">
                  <span class="memo-user">{{ remark.user_name }}:</span>
                  <span class="memo-content">{{ remark.content }}</span>
                </div>
              </div>
              <div class="memo-add mobile">
                <input 
                  v-model="newRemarks[item.id]" 
                  :placeholder="$t('schedules.addMemoPlaceHolder')"
                  @keyup.enter="handlePostRemark(item.id)"
                />
                <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                  {{ postingRemark === item.id ? '...' : '➔' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="tasks?.length === 0" class="empty">
        No tasks found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" :title="$t('tasks.addTask')" class="task-modal">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.user_id" 
              :label="$t('tasks.assignee')" 
              type="select" 
              :options="userOptions" 
              required
              :error="errors.user_id"
            />
            <BaseInput 
              v-model="form.related_personnel" 
              :label="$t('tasks.relatedPersonnel')" 
              type="select" 
              :options="userNameOptions" 
              :error="errors.related_personnel"
            />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.project" 
              :label="$t('tasks.project')" 
              type="select" 
              :options="projectOptions"
              required 
              :error="errors.project"
            />
            <BaseInput v-model="form.item" :label="$t('tasks.item')" placeholder="E.g. 技術" :error="errors.item" />
            <BaseInput 
              v-model="form.department" 
              :label="$t('tasks.department')" 
              type="select" 
              :options="departmentOptions"
              required 
              :error="errors.department"
            />
            <BaseInput v-model="form.points" :label="$t('tasks.points')" type="number" step="0.5" required :error="errors.points" />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput v-model="form.release_date" :label="$t('tasks.releaseDate')" type="date" />
            <BaseInput v-model="form.start_date" :label="$t('tasks.startDate')" type="date" />
            <BaseInput v-model="form.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
            <BaseInput v-model="form.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          </div>
        </div>

        <div class="form-section no-border">
          <div class="form-grid">
            <div class="full-width">
              <BaseInput v-model="form.work" :label="$t('tasks.work')" placeholder="Task description..." required :error="errors.work" />
            </div>
            <BaseInput v-model="form.output_url" :label="$t('tasks.outputUrl')" placeholder="https://..." :error="errors.output_url" />
            <div class="full-width">
              <BaseInput v-model="form.memo" :label="$t('tasks.memo')" type="textarea" placeholder="Remarks..." :error="errors.memo" />
            </div>
          </div>
        </div>

        <div v-if="createError" class="error-box">
          {{ createError }}
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">{{ $t('tasks.cancel') }}</button>
        <button @click="handleCreate" :disabled="creating" class="btn-primary">
          {{ creating ? $t('tasks.creating') : $t('tasks.create') }}
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { token } = useAuth()
const config = useRuntimeConfig()
const apiBase = (config.public.apiBase as string) || ''
const showCreateModal = ref(false)
const creating = ref(false)
const createError = ref('')
const errors = reactive<Record<string, string>>({})
const postingRemark = ref<number | null>(null)
const newRemarks = ref<Record<number, string>>({})
const editingAssigneeId = ref<number | null>(null)
const assigneeSelect = ref<HTMLSelectElement | null>(null)

const form = reactive({
  level: 1,
  status: 'in progress',
  user_id: '',
  related_personnel: '',
  project: '',
  item: '',
  department: '',
  work: '',
  points: 1.0,
  release_date: '',
  start_date: '',
  expected_finish_date: '',
  actual_finish_date: '',
  output_url: '',
  memo: ''
})

interface Task {
  id: number;
  level: number;
  status: string;
  user_id: number;
  related_personnel?: string;
  project: string;
  item?: string;
  department: string;
  work: string;
  points: number;
  release_date?: string;
  start_date?: string;
  expected_finish_date?: string;
  actual_finish_date?: string;
  output_url?: string;
  memo?: string;
  remarks: TaskRemark[];
  assignee?: {
    id: number;
    name: string;
    photo_url?: string;
  };
}

interface TaskRemark {
  id: number;
  user_name: string;
  content: string;
  created_at: string;
}

const { data: tasks, pending, error, refresh } = await useFetch<Task[]>(`${apiBase}/api/tasks`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: users } = await useFetch<any[]>(`${apiBase}/api/users`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: projectsData } = await useFetch<any[]>(`${apiBase}/api/projects`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: departmentsData } = await useFetch<any[]>(`${apiBase}/api/departments`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const projectOptions = computed(() => {
  if (!projectsData.value) return []
  return projectsData.value.map(p => ({ label: p.name, value: p.name }))
})

const departmentOptions = computed(() => {
  if (!departmentsData.value) return []
  return departmentsData.value.map(d => ({ label: d.name, value: d.name }))
})

const userOptions = computed(() => {
  if (!users.value) return []
  return users.value.map((u: any) => ({ label: u.name, value: u.id }))
})

const userNameOptions = computed(() => {
  if (!users.value) return []
  return users.value.map((u: any) => ({ label: u.name, value: u.name }))
})

const handlePostRemark = async (taskId: number) => {
  const content = newRemarks.value[taskId]
  if (!content || !content.trim()) return

  postingRemark.value = taskId
  try {
    await $fetch(`${apiBase}/api/tasks/${taskId}/remarks`, {
      method: 'POST',
      body: { content },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    newRemarks.value[taskId] = ''
    refresh()
  } catch (err) {
    console.error('Failed to post remark:', err)
  } finally {
    postingRemark.value = null
  }
}

const startEditingAssignee = (task: Task) => {
  editingAssigneeId.value = task.id
  nextTick(() => {
    assigneeSelect.value?.focus()
  })
}

const updateAssignee = async (task: Task, newUserId: string) => {
  if (Number(newUserId) === task.user_id) {
    editingAssigneeId.value = null
    return
  }

  try {
    const selectedUser = users.value?.find((u: any) => u.id === Number(newUserId))
    const payload: any = { user_id: Number(newUserId) }
    
    // Also update department if user changed
    if (selectedUser?.department?.name) {
      payload.department = selectedUser.department.name
    }

    await $fetch(`${apiBase}/api/tasks/${task.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    refresh()
  } catch (err) {
    console.error('Failed to update assignee:', err)
    alert('Failed to update assignee. You might not have permission.')
  } finally {
    editingAssigneeId.value = null
  }
}

watch(projectOptions, (newOptions) => {
  if (newOptions && newOptions.length > 0 && !form.project) {
    form.project = newOptions[0]?.value as string || ''
  }
}, { immediate: true })

watch(userOptions, (options) => {
  if (options && options.length > 0 && !form.user_id) {
    const firstOption = options[0]
    if (firstOption) {
      form.user_id = firstOption.value
    }
  }
}, { immediate: true })

// Auto-fill department when user changes
watch(() => form.user_id, (newUserId) => {
  if (!newUserId || !users.value) return
  const selectedUser = users.value.find((u: any) => u.id === Number(newUserId))
  if (selectedUser?.department?.name) {
    form.department = selectedUser.department.name
  }
})

const handleCreate = async () => {
  creating.value = true
  createError.value = ''
  // Reset per-field errors
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    await $fetch(`${apiBase}/api/tasks`, {
      method: 'POST',
      body: form,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    showCreateModal.value = false
    refresh()
    // Reset form
    Object.assign(form, {
      level: 1,
      status: 'in progress',
      user_id: userOptions.value[0]?.value || '',
      related_personnel: userNameOptions.value[0]?.value || '',
      project: projectOptions.value[0]?.value || '',
      item: '',
      department: departmentOptions.value[0]?.value || '',
      work: '',
      points: 1.0,
      release_date: '',
      start_date: '',
      expected_finish_date: '',
      actual_finish_date: '',
      output_url: '',
      memo: ''
    })
  } catch (err: any) {
    if (err.status === 422 && err.data?.errors) {
      // Map Laravel validation errors to our local errors object
      Object.keys(err.data.errors).forEach(key => {
        errors[key] = err.data.errors[key][0]
      })
      createError.value = 'Please check the fields for errors.'
    } else {
      createError.value = err.data?.message || 'Failed to create task.'
    }
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
.page {
  padding: 1rem;
  margin: 0 auto;
}

/* PC / Large Screen Styles (> 1024px) */
@media (min-width: 1025px) {
  .mobile-view { display: none; }
  .page { max-width: 1920px; width: 95%; }
}

/* Tablet/Mobile Styles */
@media (max-width: 1024px) {
  .desktop-view { display: none; }
  .page { max-width: 700px; }
}

@media (max-width: 480px) {
  .page { max-width: 100%; }
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-primary {
  background: #764ba2;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.desktop-view {
  overflow-x: auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.task-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
  min-width: 1600px;
}

.task-table th, .task-table td {
  border: 1px solid #e5e7eb;
  padding: 8px;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
}

.task-table th {
  background: #ffff00; /* Yellow header matching screenshot */
  color: #000;
  font-weight: 600;
}

.work-cell {
  text-align: left !important;
  min-width: 300px;
  white-space: normal !important;
  color: #1a0dab;
}

.memo-cell {
  min-width: 300px;
  max-width: 400px;
  text-align: left !important;
  vertical-align: top;
}

.memo-board {
  margin-top: 0.8rem;
  padding: 0.8rem;
  background: #f9fafb;
  border-radius: 8px;
  font-size: 0.85rem;
}

.memo-list {
  max-height: 150px;
  overflow-y: auto;
  margin-bottom: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.memo-item {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  line-height: 1.4;
  text-align: left;
}

.memo-user {
  font-weight: 600;
  color: #4b5563;
  margin-right: 6px;
}

.memo-content {
  color: #1f2937;
}

.memo-add {
  display: flex;
  gap: 4px;
}

.memo-add input {
  flex: 1;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.8rem;
}

.memo-add button {
  background: #764ba2;
  color: white;
  border: none;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
}

.memo-add button:disabled {
  opacity: 0.5;
}

.user-cell.clickable {
  cursor: pointer;
  transition: background-color 0.2s;
}

.user-cell.clickable:hover {
  background-color: #f3f4f6;
}

.edit-select-wrapper {
  padding: 4px;
}

.edit-select-wrapper select {
  width: 100%;
  padding: 4px;
  border-radius: 4px;
  border: 1px solid #d1d5db;
  font-size: 0.8rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.75rem;
  display: inline-block;
  min-width: 80px;
}

/* Matching UI colors from spreadsheet */
.status-badge.working { background: #00ff00; color: #000; }
.status-badge.in-progress { background: #d3d3d3; color: #000; }
.status-badge.idle { background: #4a90e2; color: #fff; }
.status-badge.finished { background: #f5a623; color: #fff; }

.link-btn { text-decoration: none; font-size: 1rem; }

/* Mobile Card Styles */
.card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  border-left: 4px solid #764ba2;
}

.card-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.idx { font-weight: bold; color: #999; }

.status {
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 4px;
}

.status.working { background: #e8f5e9; color: #2e7d32; }

.work-title {
  font-size: 1rem;
  margin: 0.5rem 0;
  color: #1f2937;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: #6b7280;
}

/* Modal styles */
.task-modal :deep(.modal-content) {
  max-width: 800px;
  width: 90%;
}

.modal-form {
  display: flex;
  flex-direction: column;
  width: 100%;
  box-sizing: border-box;
}

.form-section {
  width: 100%;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f3f4f6;
  box-sizing: border-box;
}

.form-section.no-border {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem 2rem;
  width: 100%;
  box-sizing: border-box;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
}

.full-width {
  grid-column: 1 / -1;
  width: 100%;
}

/* Force consistency in BaseInput within this modal */
.modal-form :deep(.form-group) {
  width: 100%;
  margin-bottom: 0;
}

.modal-form :deep(input),
.modal-form :deep(select),
.modal-form :deep(textarea) {
  width: 100%;
  box-sizing: border-box;
  display: block;
}

.error-box {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.8rem;
  border-radius: 6px;
  margin-top: 1rem;
  font-size: 0.85rem;
}
</style>
