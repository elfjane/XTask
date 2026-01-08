<template>
  <div class="page">
    <div class="header">
      <h1>Schedules</h1>
      <button @click="showCreateModal = true" class="btn-primary">Add Schedule</button>
    </div>

    <div v-if="pending" class="loading">Loading schedules...</div>
    <div v-else-if="error" class="error">Failed to load schedules: {{ error.message }}</div>
    
    <div v-else class="schedule-list">
      <div v-for="item in schedules" :key="item.id" class="card">
        <div class="card-header">
          <span class="project">{{ item.project }}</span>
          <span :class="['status', item.status]">{{ item.status }}</span>
        </div>
        <h2>{{ item.title }}</h2>
        <div class="details">
          <span v-if="item.confirm">Confirm: {{ item.confirm }}</span>
          <span v-if="item.deadline"> | Deadline: {{ item.deadline }}</span>
        </div>
        <div v-if="item.memos && item.memos.length > 0" class="memos">
          <h4>Memos:</h4>
          <ul>
            <li v-for="memo in item.memos" :key="memo.id">
              <strong>{{ memo.user_name }}:</strong> {{ memo.content }}
            </li>
          </ul>
        </div>
      </div>
      
      <div v-if="schedules?.length === 0" class="empty">
        No schedules found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" title="Add New Schedule">
      <form @submit.prevent="handleCreate">
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
  deadline: ''
})

const { data: schedules, pending, error, refresh } = await useFetch('/api/schedules', {
  async onRequest({ options }) {
    const token = await getToken()
    console.log('Schedules onRequest: token present?', !!token)
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
      deadline: ''
    })
  } catch (err) {
    console.error('Failed to create schedule:', err)
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
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
  transition: opacity 0.2s;
}

.btn-primary:hover {
  opacity: 0.9;
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

.card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  margin-bottom: 1.5rem;
  border-left: 4px solid #764ba2;
}

.card-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.project {
  font-weight: bold;
  color: #666;
  text-transform: uppercase;
  font-size: 0.8rem;
}

.status {
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 10px;
  text-transform: capitalize;
}

.status.working { background: #e3f2fd; color: #1e88e5; }
.status.finish { background: #e8f5e9; color: #43a047; }
.status.in_progress { background: #fff3e0; color: #fb8c00; }
.status.fail { background: #ffebee; color: #e53935; }

h2 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  color: #333;
}

.details {
  font-size: 0.9rem;
  color: #666;
}

.memos {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.memos h4 {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  color: #333;
}

ul {
  padding-left: 1.25rem;
  margin: 0;
  font-size: 0.85rem;
  color: #555;
}
</style>
