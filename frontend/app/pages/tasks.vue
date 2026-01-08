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
              <td class="user-cell">
                <img v-if="item.assignee" :src="item.assignee.photo_url || 'https://ui-avatars.com/api/?name=' + item.assignee.name" class="avatar" />
                {{ item.assignee?.name || '-' }}
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
              <td class="memo-cell">{{ item.memo || '-' }}</td>
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
          </div>
        </div>
      </div>
      
      <div v-if="tasks?.length === 0" class="empty">
        No tasks found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" :title="$t('tasks.addTask')">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-grid">
          <BaseInput 
            v-model="form.level" 
            :label="$t('tasks.level')" 
            type="select" 
            :options="[
              { label: $t('tasks.ordinary'), value: 1 },
              { label: $t('tasks.important'), value: 2 },
              { label: $t('tasks.priority'), value: 3 }
            ]" 
          />
          <BaseInput 
            v-model="form.status" 
            :label="$t('tasks.status')" 
            type="select" 
            :options="[
              { label: $t('schedules.working'), value: 'working' },
              { label: $t('schedules.in_progress'), value: 'in progress' },
              { label: $t('tasks.idle'), value: 'idle' },
              { label: $t('tasks.waiting_qa'), value: 'waiting qa' },
              { label: $t('schedules.finish'), value: 'finished' },
              { label: $t('tasks.miss'), value: 'miss' },
              { label: $t('tasks.cancelled'), value: 'cancelled' }
            ]" 
          />
          <BaseInput 
            v-model="form.user_id" 
            :label="$t('tasks.assignee')" 
            type="select" 
            :options="userOptions" 
            required
          />
          <BaseInput v-model="form.related_personnel" :label="$t('tasks.relatedPersonnel')" placeholder="PP, Henry" />
          <BaseInput v-model="form.project" :label="$t('tasks.project')" placeholder="E.g. B2C" required />
          <BaseInput v-model="form.item" :label="$t('tasks.item')" placeholder="E.g. 技術" />
          <BaseInput v-model="form.department" :label="$t('tasks.department')" placeholder="E.g. R&D" required />
          <BaseInput v-model="form.points" :label="$t('tasks.points')" type="number" step="0.5" required />
          <BaseInput v-model="form.release_date" :label="$t('tasks.releaseDate')" type="date" />
          <BaseInput v-model="form.start_date" :label="$t('tasks.startDate')" type="date" />
          <BaseInput v-model="form.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
          <BaseInput v-model="form.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          <div class="full-width">
            <BaseInput v-model="form.work" :label="$t('tasks.work')" placeholder="Task description..." required />
          </div>
          <BaseInput v-model="form.output_url" :label="$t('tasks.outputUrl')" placeholder="https://..." />
          <div class="full-width">
            <BaseInput v-model="form.memo" :label="$t('tasks.memo')" type="textarea" placeholder="Remarks..." />
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
const showCreateModal = ref(false)
const creating = ref(false)
const createError = ref('')

const form = reactive({
  level: 1,
  status: 'working',
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
  assignee?: {
    id: number;
    name: string;
    photo_url?: string;
  };
}

const { data: tasks, pending, error, refresh } = await useFetch<Task[]>('/api/tasks', {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: users } = await useFetch<any[]>('/api/users', {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const userOptions = computed(() => {
  if (!users.value) return []
  return users.value.map((u: any) => ({ label: u.name, value: u.id }))
})

watch(userOptions, (options) => {
  if (options && options.length > 0 && !form.user_id) {
    const firstOption = options[0]
    if (firstOption) {
      form.user_id = firstOption.value
    }
  }
}, { immediate: true })

const handleCreate = async () => {
  creating.value = true
  createError.value = ''
  try {
    await $fetch('/api/tasks', {
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
      status: 'working',
      user_id: userOptions.value[0]?.value || '',
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
  } catch (err: any) {
    createError.value = err.data?.message || 'Failed to create task.'
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
  text-align: left !important;
  min-width: 200px;
  white-space: normal !important;
}

.user-cell {
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
.modal-form { max-width: 800px; }
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
@media (max-width: 600px) {
  .form-grid { grid-template-columns: 1fr; }
}
.full-width { grid-column: 1 / -1; }

.error-box {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.8rem;
  border-radius: 6px;
  margin-top: 1rem;
  font-size: 0.85rem;
}
</style>
