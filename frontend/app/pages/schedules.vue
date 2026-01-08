<template>
  <div class="page">
    <div class="header">
      <h1>Schedules</h1>
      <button @click="showCreateModal = true" class="btn-primary">Add Schedule</button>
    </div>

    <div v-if="pending" class="loading">Loading schedules...</div>
    <div v-else-if="error" class="error">Failed to load schedules: {{ error.message }}</div>
    
    <div v-else class="content-wrapper">
      <!-- Desktop View (Table) -->
      <div class="desktop-view">
        <table class="schedule-table">
          <thead>
            <tr>
              <th>IDX</th>
              <th>狀態</th>
              <th>Confirm</th>
              <th>Deadline</th>
              <th>Week</th>
              <th>預定開始日期</th>
              <th>預定結束日期</th>
              <th>預定天數</th>
              <th>實際開始日期</th>
              <th>實際完成日期</th>
              <th>實際天數</th>
              <th>Project</th>
              <th>Schedule Title</th>
              <th>Memo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in schedules" :key="item.id">
              <td>{{ item.id }}</td>
              <td>
                <span :class="['status-badge', item.status]">{{ item.status }}</span>
              </td>
              <td>{{ item.confirm }}</td>
              <td>{{ item.deadline }}</td>
              <td>{{ getWeekDay(item.deadline) }}</td>
              <td>{{ item.scheduled_start }}</td>
              <td>{{ item.scheduled_end }}</td>
              <td>{{ calculateDays(item.scheduled_start, item.scheduled_end) }}</td>
              <td>{{ item.actual_start }}</td>
              <td>{{ item.actual_finish }}</td>
              <td>{{ calculateDays(item.actual_start, item.actual_finish) }}</td>
              <td>{{ item.project }}</td>
              <td class="title-cell">{{ item.title }}</td>
              <td>{{ item.memo }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View (Cards) -->
      <div class="mobile-view">
        <div v-for="item in schedules" :key="item.id" class="card">
          <div class="card-header">
            <span class="idx">#{{ item.id }}</span>
            <span :class="['status', item.status]">{{ item.status }}</span>
          </div>
          <div class="card-body">
            <h2 class="project-title">{{ item.project }} - {{ item.title }}</h2>
            <div class="info-grid">
              <div class="info-item"><strong>Confirm:</strong> {{ item.confirm }}</div>
              <div class="info-item"><strong>Deadline:</strong> {{ item.deadline }}</div>
              <div class="info-item"><strong>Target:</strong> {{ item.scheduled_start }} ~ {{ item.scheduled_end }}</div>
              <div class="info-item" v-if="item.actual_start"><strong>Actual:</strong> {{ item.actual_start }} ~ {{ item.actual_finish }}</div>
            </div>
            <div v-if="item.memo" class="memo-box">
              <strong>Memo:</strong> {{ item.memo }}
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="schedules?.length === 0" class="empty">
        No schedules found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" title="Add New Schedule">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-grid">
          <BaseInput v-model="form.project" label="Project" placeholder="E.g. XTask" />
          <BaseInput v-model="form.title" label="Title" placeholder="Schedule title" />
          <BaseInput 
            v-model="form.status" 
            label="Status" 
            type="select" 
            :options="[
              { label: 'Working', value: 'working' },
              { label: 'Finish', value: 'finish' },
              { label: 'In Progress', value: 'in_progress' },
              { label: 'Fail', value: 'fail' }
            ]" 
          />
          <BaseInput 
            v-model="form.confirm" 
            label="Confirmation" 
            type="select" 
            :options="[
              { label: 'Tentatively', value: 'Tentatively' },
              { label: 'Confirmed', value: 'Confirmed' }
            ]" 
          />
          <BaseInput v-model="form.deadline" label="Deadline" type="date" />
          <BaseInput v-model="form.scheduled_start" label="Target Start" type="date" />
          <BaseInput v-model="form.scheduled_end" label="Target End" type="date" />
          <BaseInput v-model="form.actual_start" label="Actual Start" type="date" />
          <BaseInput v-model="form.actual_finish" label="Actual Finish" type="date" />
          <div class="full-width">
            <BaseInput v-model="form.memo" label="Memo" type="textarea" placeholder="Add some remarks..." />
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">Cancel</button>
        <button @click="handleCreate" :disabled="creating" class="btn-primary">
          {{ creating ? 'Creating...' : 'Create Schedule' }}
        </button>
      </template>
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

const form = reactive({
  project: '',
  title: '',
  status: 'working',
  confirm: 'Tentatively',
  deadline: '',
  scheduled_start: '',
  scheduled_end: '',
  actual_start: '',
  actual_finish: '',
  memo: ''
})

interface Schedule {
  id: number;
  project: string;
  title: string;
  status: 'working' | 'finish' | 'in_progress' | 'fail';
  confirm: 'Tentatively' | 'Confirmed';
  deadline: string;
  scheduled_start: string;
  scheduled_end: string;
  actual_start: string;
  actual_finish: string;
  memo: string;
}

const { data: schedules, pending, error, refresh } = await useFetch<Schedule[]>('/api/schedules', {
  async onRequest({ options }) {
    const token = await getToken()
    if (token) {
      options.headers = options.headers || {}
      if (Array.isArray(options.headers)) {
        options.headers.push(['Authorization', `Bearer ${token}`])
      } else if (options.headers instanceof Headers) {
        options.headers.set('Authorization', `Bearer ${token}`)
      } else {
        options.headers.Authorization = `Bearer ${token}`
      }
    }
  }
})

const handleCreate = async () => {
  creating.value = true
  try {
    const token = await getToken()
    await $fetch('/api/schedules', {
      method: 'POST',
      body: form,
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    showCreateModal.value = false
    refresh()
    // Reset form
    Object.assign(form, {
      project: '',
      title: '',
      status: 'working',
      confirm: 'Tentatively',
      deadline: '',
      scheduled_start: '',
      scheduled_end: '',
      actual_start: '',
      actual_finish: '',
      memo: ''
    })
  } catch (err) {
    console.error('Failed to create schedule:', err)
  } finally {
    creating.value = false
  }
}

const getWeekDay = (dateStr: string) => {
  if (!dateStr) return ''
  const days = ['日', '一', '二', '三', '四', '五', '六']
  const date = new Date(dateStr)
  return days[date.getDay()]
}

const calculateDays = (start: string, end: string) => {
  if (!start || !end) return ''
  const s = new Date(start)
  const e = new Date(end)
  const diffTime = Math.abs(e.getTime() - s.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays + 1
}
</script>

<style scoped>
/* Base Page Style */
.page {
  padding: 1rem;
  margin: 0 auto;
  transition: max-width 0.3s ease;
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
  transition: opacity 0.2s;
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

/* Mobile Styles (Phone: < 480px) */
@media (max-width: 480px) {
  .desktop-view { display: none; }
  .page { max-width: 100%; }
}

/* Tablet/Large Phone Styles (481px - 1024px) */
@media (min-width: 481px) and (max-width: 1024px) {
  .desktop-view { display: none; }
  .page { max-width: 700px; }
}

/* PC / Large Screen Styles (> 1024px) */
@media (min-width: 1025px) {
  .mobile-view { display: none; }
  .page { max-width: 1920px; width: 95%; }
}

.desktop-view {
  overflow-x: auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  min-width: 1200px;
}

.schedule-table th, .schedule-table td {
  border: 1px solid #e5e7eb;
  padding: 8px;
  text-align: center;
  white-space: nowrap;
}

.schedule-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #4b5563;
}

.title-cell {
  text-align: left !important;
  min-width: 200px;
  white-space: normal !important;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.75rem;
  display: inline-block;
  min-width: 70px;
}

.status-badge.working { background: #00ff00; color: #000; }
.status-badge.finish { background: #e8f5e9; color: #43a047; }
.status-badge.in_progress { background: #fff3e0; color: #fb8c00; }
.status-badge.fail { background: #ffebee; color: #e53935; }

/* Mobile Styles */
@media (max-width: 768px) {
  .desktop-view { display: none; }
  .page { max-width: 600px; margin: 0 auto; }
}

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
  text-transform: capitalize;
}

.status.working { background: #e3f2fd; color: #1e88e5; }
.status.finish { background: #e8f5e9; color: #43a047; }

.project-title {
  font-size: 1.1rem;
  margin: 0.5rem 0;
  color: #1f2937;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.4rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.memo-box {
  margin-top: 0.8rem;
  padding: 0.5rem;
  background: #f9fafb;
  border-radius: 6px;
  font-size: 0.85rem;
}

/* Modal Form styles */
.modal-form {
  max-width: 600px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
@media (max-width: 480px) {
  .form-grid { grid-template-columns: 1fr; }
}
.full-width {
  grid-column: 1 / -1;
}
</style>

