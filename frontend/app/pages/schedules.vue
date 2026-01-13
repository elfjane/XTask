<template>
  <div class="page">
    <div class="header">
      <h1>{{ $t('schedules.title') }}</h1>
      <button @click="showCreateModal = true" class="btn-primary">{{ $t('schedules.addSchedule') }}</button>
    </div>

    <div v-if="pending" class="loading">{{ $t('common.loggingIn') ? $t('login.loggingIn') : 'Loading...' }}</div>
    <div v-else-if="error" class="error">{{ error.message }}</div>
    
    <div v-else class="content-wrapper">
      <!-- Desktop View (Table) -->
      <div class="desktop-view">
        <table class="schedule-table">
          <thead>
            <tr>
              <th>{{ $t('schedules.idx') }}</th>
              <th>{{ $t('schedules.status') }}</th>
              <th>{{ $t('schedules.confirm') }}</th>
              <th>{{ $t('schedules.deadline') }}</th>
              <th>{{ $t('schedules.week') }}</th>
              <th>{{ $t('schedules.scheduledStart') }}</th>
              <th>{{ $t('schedules.scheduledEnd') }}</th>
              <th>{{ $t('schedules.scheduledDays') }}</th>
              <th>{{ $t('schedules.actualStart') }}</th>
              <th>{{ $t('schedules.actualFinish') }}</th>
              <th>{{ $t('schedules.project') }}</th>
              <th>{{ $t('schedules.scheduleTitle') }}</th>
              <th>{{ $t('schedules.memo') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in schedules" :key="item.id">
              <td>{{ item.id }}</td>
              <td>
                <span :class="['status-badge', item.status]">{{ $t(`schedules.${item.status}`) }}</span>
              </td>
              <td>{{ $t(`schedules.${item.confirm.toLowerCase()}`) }}</td>
              <td>{{ item.deadline }}</td>
              <td>{{ getWeekDay(item.deadline) }}</td>
              <td>{{ item.scheduled_start }}</td>
              <td>{{ item.scheduled_end }}</td>
              <td>{{ calculateDays(item.scheduled_start, item.scheduled_end) }}</td>
              <td>{{ item.actual_start || '-' }}</td>
              <td>{{ item.actual_finish || '-' }}</td>
              <td>{{ item.project }}</td>
              <td class="title-cell clickable" @click="openDetails(item)">{{ item.title }}</td>
              <td class="memo-cell">
                <div class="memo-list">
                  <div v-if="item.memos && item.memos.length > 0" class="memo-item">
                    <span class="memo-user">{{ item.memos[item.memos.length - 1]?.user_name }}:</span>
                    <span class="memo-content">{{ item.memos[item.memos.length - 1]?.content }}</span>
                  </div>
                </div>
                <div class="memo-add">
                  <input 
                    v-model="newMemos[item.id]" 
                    :placeholder="$t('schedules.addMemoPlaceHolder')" 
                    @keyup.enter="handlePostMemo(item.id)"
                  />
                  <button @click="handlePostMemo(item.id)" :disabled="postingMemo === item.id">
                    {{ postingMemo === item.id ? '...' : '➔' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile View (Cards) -->
      <div class="mobile-view">
        <div v-for="item in schedules" :key="item.id" class="card">
          <div class="card-header">
            <span class="idx">#{{ item.id }}</span>
            <span :class="['status', item.status]">{{ $t(`schedules.${item.status}`) }}</span>
          </div>
          <div class="card-body">
            <h2 class="project-title clickable" @click="openDetails(item)">{{ item.project }} - {{ item.title }}</h2>
            <div class="info-grid">
              <div class="info-item"><strong>{{ $t('schedules.confirm') }}:</strong> {{ $t(`schedules.${item.confirm.toLowerCase()}`) }}</div>
              <div class="info-item"><strong>{{ $t('schedules.deadline') }}:</strong> {{ item.deadline }}</div>
              <div class="info-item"><strong>{{ $t('schedules.scheduledStart') }}:</strong> {{ item.scheduled_start }} ~ {{ item.scheduled_end }}</div>
              <div class="info-item"><strong>{{ $t('schedules.actualStart') }}:</strong> {{ item.actual_start || '-' }} ~ {{ item.actual_finish || '-' }}</div>
            </div>
            <div class="memo-board">
              <strong>{{ $t('schedules.memo') }}:</strong>
              <div class="memo-list mobile">
                <div v-if="item.memos && item.memos.length > 0" class="memo-item">
                  <span class="memo-user">{{ item.memos[item.memos.length - 1]?.user_name }}:</span>
                  <span class="memo-content">{{ item.memos[item.memos.length - 1]?.content }}</span>
                </div>
              </div>
              <div class="memo-add mobile">
                <input 
                  v-model="newMemos[item.id]" 
                  :placeholder="$t('schedules.addMemoPlaceHolder')"
                  @keyup.enter="handlePostMemo(item.id)"
                />
                <button @click="handlePostMemo(item.id)" :disabled="postingMemo === item.id">
                  {{ postingMemo === item.id ? '...' : '➔' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="schedules?.length === 0" class="empty">
        No schedules found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" :title="$t('schedules.addSchedule')" class="schedule-modal">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.project" 
              :label="$t('schedules.project')" 
              type="select" 
              :options="projectOptions" 
            />
            <BaseInput v-model="form.title" :label="$t('schedules.scheduleTitle')" placeholder="Schedule title" />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.confirm" 
              :label="$t('schedules.confirm')" 
              type="select" 
              :options="[
                { label: $t('schedules.tentatively'), value: 'Tentatively' },
                { label: $t('schedules.confirmed'), value: 'Confirmed' }
              ]" 
            />
            <BaseInput v-model="form.deadline" :label="$t('schedules.deadline')" type="date" />
            <BaseInput v-model="form.scheduled_start" :label="$t('schedules.scheduledStart')" type="date" />
            <BaseInput v-model="form.scheduled_end" :label="$t('schedules.scheduledEnd')" type="date" />
          </div>
        </div>
        <div class="full-width">
          <BaseInput v-model="form.memo" :label="$t('schedules.memo')" type="textarea" placeholder="Add some remarks..." />
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
        <button @click="handleCreate" :disabled="creating" class="btn-primary">
          {{ creating ? $t('schedules.creating') : $t('schedules.create') }}
        </button>
      </template>
    </BaseModal>

    <!-- Details/Edit Modal -->
    <BaseModal v-model="showDetailsModal" :title="isEditing ? $t('schedules.editSchedule') : $t('schedules.details')" class="schedule-modal">
      <div v-if="!isEditing" class="details-view">
        <div class="detail-item">
          <label>{{ $t('schedules.project') }}</label>
          <div class="value">{{ selectedSchedule?.project }}</div>
        </div>
        <div class="detail-item">
          <label>{{ $t('schedules.scheduleTitle') }}</label>
          <div class="value">{{ selectedSchedule?.title }}</div>
        </div>
        <div class="form-grid">
          <div class="detail-item">
            <label>{{ $t('schedules.status') }}</label>
            <div class="value">
              <span :class="['status-badge', selectedSchedule?.status]">{{ $t(`schedules.${selectedSchedule?.status}`) }}</span>
            </div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.confirm') }}</label>
            <div class="value">{{ selectedSchedule ? $t(`schedules.${selectedSchedule.confirm.toLowerCase()}`) : '' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.deadline') }}</label>
            <div class="value">{{ selectedSchedule?.deadline }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.scheduledStart') }}</label>
            <div class="value">{{ selectedSchedule?.scheduled_start }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.scheduledEnd') }}</label>
            <div class="value">{{ selectedSchedule?.scheduled_end }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.actualStart') }}</label>
            <div class="value">{{ selectedSchedule?.actual_start || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('schedules.actualFinish') }}</label>
            <div class="value">{{ selectedSchedule?.actual_finish || '-' }}</div>
          </div>
        </div>
        <div class="detail-item">
          <label>{{ $t('schedules.memo') }}</label>
          <div class="value memo-content-box">{{ selectedSchedule?.memo || '-' }}</div>
        </div>

        <div class="detail-memos">
          <label>{{ $t('schedules.memo') }} ({{ $t('common.management') }})</label>
          <div class="memo-list modal-memos">
            <div v-for="memo in selectedSchedule?.memos" :key="memo.id" class="memo-item">
              <span class="memo-user">{{ memo.user_name }}:</span>
              <span class="memo-content">{{ memo.content }}</span>
              <span class="memo-time">{{ formatTime(memo.created_at) }}</span>
            </div>
          </div>
          <div class="memo-add">
            <input 
              v-model="newMemos[selectedSchedule!.id]" 
              :placeholder="$t('schedules.addMemoPlaceHolder')" 
              @keyup.enter="handlePostMemo(selectedSchedule!.id)"
            />
            <button @click="handlePostMemo(selectedSchedule!.id)" :disabled="postingMemo === selectedSchedule!.id">
              {{ postingMemo === selectedSchedule!.id ? '...' : '➔' }}
            </button>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="handleUpdate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="editForm.project" 
              :label="$t('schedules.project')" 
              type="select" 
              :options="projectOptions" 
            />
            <BaseInput v-model="editForm.title" :label="$t('schedules.scheduleTitle')" />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="editForm.status" 
              :label="$t('schedules.status')" 
              type="select" 
              :options="[
                { label: $t('schedules.working'), value: 'working' },
                { label: $t('schedules.finish'), value: 'finish' },
                { label: $t('schedules.in_progress'), value: 'in_progress' },
                { label: $t('schedules.fail'), value: 'fail' }
              ]" 
            />
            <BaseInput 
              v-model="editForm.confirm" 
              :label="$t('schedules.confirm')" 
              type="select" 
              :options="[
                { label: $t('schedules.tentatively'), value: 'Tentatively' },
                { label: $t('schedules.confirmed'), value: 'Confirmed' }
              ]" 
            />
            <BaseInput v-model="editForm.deadline" :label="$t('schedules.deadline')" type="date" />
            <BaseInput v-model="editForm.scheduled_start" :label="$t('schedules.scheduledStart')" type="date" />
            <BaseInput v-model="editForm.scheduled_end" :label="$t('schedules.scheduledEnd')" type="date" />
            <BaseInput v-model="editForm.actual_start" :label="$t('schedules.actualStart')" type="date" />
            <BaseInput v-model="editForm.actual_finish" :label="$t('schedules.actualFinish')" type="date" />
          </div>
        </div>
        <div class="full-width">
          <BaseInput v-model="editForm.memo" :label="$t('schedules.memo')" type="textarea" />
        </div>
      </form>

      <template #footer>
        <button v-if="!isEditing" @click="showDetailsModal = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
        <button v-if="!isEditing" @click="startEditing" class="btn-primary">{{ $t('schedules.edit') }}</button>
        
        <button v-if="isEditing" @click="isEditing = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
        <button v-if="isEditing" @click="handleUpdate" :disabled="updating" class="btn-primary">
          {{ updating ? $t('schedules.updating') : $t('schedules.save') }}
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
const showDetailsModal = ref(false)
const isEditing = ref(false)
const creating = ref(false)
const updating = ref(false)
const postingMemo = ref<number | null>(null)
const newMemos = ref<Record<number, string>>({})
const currentScheduleId = ref<number | null>(null)
const selectedSchedule = computed(() => {
  if (!currentScheduleId.value || !schedules.value) return null
  return schedules.value.find(s => s.id === currentScheduleId.value) || null
})

const form = reactive({
  project: '',
  title: '',
  confirm: 'Tentatively',
  deadline: '',
  scheduled_start: '',
  scheduled_end: '',
  memo: ''
})

const editForm = reactive({
  id: 0,
  project: '',
  title: '',
  status: '',
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
  actual_start?: string;
  actual_finish?: string;
  memo: string;
  memos: Memo[];
}

interface Memo {
  id: number;
  user_name: string;
  content: string;
  created_at: string;
}

const { data: schedules, pending, error, refresh } = await useFetch<Schedule[]>(`${apiBase}/api/schedules`, {
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

const projectOptions = computed(() => {
  if (!projectsData.value) return []
  return projectsData.value.map(p => ({ label: p.name, value: p.name }))
})

// Set initial project if list is not empty
watch(projectOptions, (newOptions) => {
  if (newOptions && newOptions.length > 0 && !form.project) {
    form.project = newOptions[0]?.value as string || ''
  }
}, { immediate: true })

const openDetails = (schedule: Schedule) => {
  currentScheduleId.value = schedule.id
  isEditing.value = false
  showDetailsModal.value = true
}

const startEditing = () => {
  if (!selectedSchedule.value) return
  Object.assign(editForm, {
    id: selectedSchedule.value.id,
    project: selectedSchedule.value.project,
    title: selectedSchedule.value.title,
    status: selectedSchedule.value.status,
    confirm: selectedSchedule.value.confirm,
    deadline: selectedSchedule.value.deadline,
    scheduled_start: selectedSchedule.value.scheduled_start,
    scheduled_end: selectedSchedule.value.scheduled_end,
    actual_start: selectedSchedule.value.actual_start || '',
    actual_finish: selectedSchedule.value.actual_finish || '',
    memo: selectedSchedule.value.memo
  })
  isEditing.value = true
}

const handleCreate = async () => {
  creating.value = true
  try {
    await $fetch(`${apiBase}/api/schedules`, {
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
      project: projectOptions.value[0]?.value || '',
      title: '',
      confirm: 'Tentatively',
      deadline: '',
      scheduled_start: '',
      scheduled_end: '',
      memo: ''
    })
  } catch (err) {
    console.error('Failed to create schedule:', err)
  } finally {
    creating.value = false
  }
}

const handleUpdate = async () => {
  if (!editForm.id) return
  updating.value = true
  try {
    await $fetch(`${apiBase}/api/schedules/${editForm.id}`, {
      method: 'PUT',
      body: editForm,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    
    await refresh()
    
    isEditing.value = false
  } catch (err) {
    console.error('Failed to update schedule:', err)
  } finally {
    updating.value = false
  }
}

const handlePostMemo = async (scheduleId: number) => {
  const content = newMemos.value[scheduleId]
  if (!content || !content.trim()) return

  postingMemo.value = scheduleId
  try {
    await $fetch(`${apiBase}/api/schedules/${scheduleId}/memos`, {
      method: 'POST',
      body: { content },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    newMemos.value[scheduleId] = ''
    refresh()
  } catch (err) {
    console.error('Failed to post memo:', err)
  } finally {
    postingMemo.value = null
  }
}

const formatTime = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString([], { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
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

.schedule-table tbody tr {
  transition: background-color 0.2s ease;
}

.schedule-table tbody tr:hover {
  background-color: #f8f6ff; /* Light purple hover effect */
}

.title-cell {
  text-align: left !important;
  min-width: 200px;
  white-space: normal !important;
}

.clickable {
  cursor: pointer;
  color: #764ba2;
  font-weight: 600;
  transition: color 0.2s;
}

.clickable:hover {
  color: #5d3b81;
  text-decoration: underline;
}

.details-view {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item .value {
  font-size: 1rem;
  color: #1f2937;
  padding: 0.5rem 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
  min-height: 1.5rem;
}

.memo-content-box {
  white-space: pre-wrap;
  line-height: 1.6;
}

.detail-memos {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f3f4f6;
}

.detail-memos label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
  display: block;
}

.modal-memos {
  max-height: 300px;
  overflow-y: auto;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.memo-time {
  font-size: 0.7rem;
  color: #9ca3af;
  margin-left: auto;
}

.memo-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 8px;
  padding: 8px;
  background: white;
  border-radius: 6px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
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

.memo-board {
  margin-top: 0.8rem;
  padding: 0.8rem;
  background: #f9fafb;
  border-radius: 8px;
  font-size: 0.85rem;
}

.memo-cell {
  min-width: 300px;
  max-width: 400px;
  text-align: left !important;
  vertical-align: top;
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
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.memo-add button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.mobile-view .memo-list {
  max-height: none;
}

/* Modal Form styles */
.schedule-modal :deep(.modal-content) {
  max-width: 800px;
  width: 90%;
  margin: 2rem auto; /* Ensure it's centered if needed, though BaseModal centers it */
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

.section-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #9ca3af;
  margin-bottom: 1.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0 0.25rem; /* Slight offset for label alignment if needed */
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
  box-sizing: border-box; /* Crucial for alignment */
  display: block;
}
</style>

