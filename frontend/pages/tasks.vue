<template>
  <div class="page">
    <div class="header">
      <h1>Tasks</h1>
      <button @click="showCreateModal = true" class="btn-primary">Add Task</button>
    </div>

    <div v-if="pending" class="loading">Loading tasks...</div>
    <div v-else-if="error" class="error">Failed to load tasks: {{ error.message }}</div>
    
    <div v-else class="task-list">
      <div v-for="item in tasks" :key="item.id" class="card">
        <div class="card-header">
          <div class="left">
            <span :class="['level', `level-${item.level}`]">Level {{ item.level }}</span>
            <span class="project">{{ item.project }}</span>
          </div>
          <span :class="['status', item.status.replace(' ', '-')]">{{ item.status }}</span>
        </div>
        
        <h2>{{ item.work }}</h2>
        
        <div class="meta">
          <div class="info-group">
            <label>Points:</label>
            <span>{{ item.points }}</span>
          </div>
          <div class="info-group">
            <label>Dept:</label>
            <span>{{ item.department }}</span>
          </div>
          <div class="info-group">
            <label>Assignee:</label>
            <span>{{ item.assignee?.name || 'Unassigned' }}</span>
          </div>
        </div>

        <div class="dates">
          <span>Start: {{ item.start_date || 'N/A' }}</span>
          <span>Target: {{ item.expected_finish_date || 'N/A' }}</span>
        </div>

        <div v-if="item.remarks && item.remarks.length > 0" class="remarks">
          <h4>Remarks:</h4>
          <div v-for="remark in item.remarks" :key="remark.id" class="remark">
            <strong>{{ remark.user_name }}:</strong> {{ remark.content }}
          </div>
        </div>
      </div>

      <div v-if="tasks?.length === 0" class="empty">
        No tasks found.
      </div>
    </div>

    <BaseModal v-model="showCreateModal" title="Add New Task">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-grid">
          <BaseInput v-model="form.project" label="Project" placeholder="XTask" required />
          <BaseInput v-model="form.department" label="Department" placeholder="R&D" required />
        </div>
        <BaseInput v-model="form.work" label="Task Description" placeholder="Detailed task description" required />
        <div class="form-grid">
          <BaseInput 
            v-model="form.level" 
            label="Priority Level" 
            type="select" 
            :options="[
              { label: 'Ordinary (1)', value: 1 },
              { label: 'Important (2)', value: 2 },
              { label: 'Priority (3)', value: 3 }
            ]" 
          />
          <BaseInput v-model="form.points" label="Points" type="number" step="0.5" placeholder="1.0" required />
        </div>
        <div class="form-grid">
          <BaseInput 
            v-model="form.status" 
            label="Status" 
            type="select" 
            :options="[
              { label: 'Working', value: 'working' },
              { label: 'In Progress', value: 'in progress' },
              { label: 'Idle', value: 'idle' },
              { label: 'Waiting QA', value: 'waiting qa' },
              { label: 'Finished', value: 'finished' }
            ]" 
          />
          <BaseInput 
            v-model="form.user_id" 
            label="Assignee" 
            type="select" 
            :options="userOptions" 
            required
          />
        </div>
        <div class="form-grid">
          <BaseInput v-model="form.start_date" label="Start Date" type="date" />
          <BaseInput v-model="form.expected_finish_date" label="Expected Finish" type="date" />
        </div>

        <div v-if="createError" class="error-box">
          <strong>Error:</strong> {{ createError }}
        </div>

        <div class="modal-actions">
          <button type="button" @click="showCreateModal = false" class="btn-secondary">Cancel</button>
          <button type="submit" :disabled="creating" class="btn-primary">
            {{ creating ? 'Creating...' : 'Create Task' }}
          </button>
        </div>
      </form>
      <!-- Remove footer template slot since buttons are now in form -->
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { getToken } = useAuth()
const showCreateModal = ref(false)
const creating = ref(false)
const createError = ref('')

const form = reactive({
  project: '',
  department: '',
  work: '',
  level: 1,
  points: 1.0,
  status: 'working',
  user_id: '',
  start_date: '',
  expected_finish_date: ''
})

// Fetch Tasks
const { data: tasks, pending, error, refresh } = await useFetch('/api/tasks', {
  async onRequest({ options }) {
    const token = await getToken()
    if (token) {
      options.headers = (options.headers || {}) as any
      if (options.headers instanceof Headers) {
        options.headers.set('Authorization', `Bearer ${token}`)
      } else {
        options.headers.Authorization = `Bearer ${token}`
      }
    }
  }
})

// Fetch Users for assignment
const { data: users } = await useFetch('/api/users', {
  async onRequest({ options }) {
    const token = await getToken()
    if (token) {
      options.headers = (options.headers || {}) as any
      if (options.headers instanceof Headers) {
        options.headers.set('Authorization', `Bearer ${token}`)
      } else {
        options.headers.Authorization = `Bearer ${token}`
      }
    }
  }
})

const userOptions = computed(() => {
  return (users.value || []).map((u: any) => ({ label: u.name, value: u.id }))
})

// Default assignee to first user if available
watch(userOptions, (options) => {
  if (options.length > 0 && !form.user_id) {
    form.user_id = options[0].value
  }
}, { immediate: true })

const handleCreate = async () => {
  creating.value = true
  createError.value = ''
  try {
    const token = await getToken()
    await $fetch('/api/tasks', {
      method: 'POST',
      body: form,
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    showCreateModal.value = false
    refresh()
    // Reset form
    Object.assign(form, {
      project: '',
      department: '',
      work: '',
      level: 1,
      points: 1.0,
      status: 'working',
      user_id: userOptions.value[0]?.value || '',
      start_date: '',
      expected_finish_date: ''
    })
  } catch (err: any) {
    console.error('Failed to create task:', err)
    createError.value = err.data?.message || 'Failed to create task. Please check your inputs.'
    if (err.data?.errors) {
      const firstError = Object.values(err.data.errors)[0]
      if (Array.isArray(firstError)) createError.value = firstError[0]
    }
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
/* ... existing styles ... */
.error-box {
  background: #fff5f5;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 6px;
  margin-top: 1rem;
  font-size: 0.85rem;
  border: 1px solid #feb2b2;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f0f0f0;
}
.page {
  max-width: 900px;
  margin: 0 auto;
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

.card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  margin-bottom: 1.5rem;
  border-left: 4px solid #ff9800;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.left {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.level {
  font-size: 0.7rem;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 4px;
}

.level-1 { background: #f5f5f5; color: #666; }
.level-2 { background: #fff3e0; color: #ef6c00; }
.level-3 { background: #ffebee; color: #c62828; }

.project {
  font-size: 0.8rem;
  color: #999;
  font-weight: bold;
}

.status {
  font-size: 0.75rem;
  padding: 2px 10px;
  border-radius: 20px;
  background: #f0f0f0;
  color: #666;
}

.status.finished { background: #e8f5e9; color: #2e7d32; }
.status.working { background: #e3f2fd; color: #1565c0; }

h2 {
  margin: 0 0 1rem 0;
  font-size: 1.15rem;
  color: #333;
}

.meta {
  display: flex;
  gap: 2rem;
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.info-group label {
  color: #999;
  margin-right: 0.5rem;
}

.dates {
  display: flex;
  gap: 2rem;
  font-size: 0.8rem;
  color: #666;
  background: #fcfcfc;
  padding: 0.5rem;
  border-radius: 4px;
}

.remarks {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

.remarks h4 {
  font-size: 0.85rem;
  margin: 0 0 0.5rem 0;
}

.remark {
  font-size: 0.8rem;
  color: #555;
  margin-bottom: 0.25rem;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
</style>
