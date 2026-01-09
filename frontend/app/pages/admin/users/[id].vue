<template>
  <div class="admin-page-container">
    <div class="page-header">
      <h1 class="page-title">{{ $t('admin.editUser') }}</h1>
      <NuxtLink to="/admin/users" class="btn btn-secondary">Back</NuxtLink>
    </div>

    <div v-if="loadingInitial" class="loading">Loading...</div>

    <div v-else class="card form-card">
      <form @submit.prevent="submitForm">
        <!-- Name -->
        <div class="form-group">
          <label>Name</label>
          <input v-model="form.name" type="text" required class="form-control" />
        </div>

        <!-- Email -->
        <div class="form-group">
          <label>Account (Email)</label>
          <input v-model="form.email" type="email" required class="form-control" />
        </div>

        <!-- Password -->
        <div class="form-group">
          <label>Password (Leave blank to keep unchanged)</label>
          <input v-model="form.password" type="password" class="form-control" minlength="8" />
        </div>

        <!-- Employee ID -->
        <div class="form-group">
          <label>Employee ID</label>
          <input v-model="form.employee_id" type="text" class="form-control" />
        </div>

        <!-- Photo URL -->
        <div class="form-group">
          <label>Photo URL</label>
          <input v-model="form.photo_url" type="text" class="form-control" />
        </div>

        <!-- Role -->
        <div class="form-group">
          <label>Role</label>
          <select v-model="form.role" required class="form-control">
            <option value="admin">{{ $t('common.roles.admin') }}</option>
            <option value="manager">{{ $t('common.roles.manager') }}</option>
            <option value="task_user">{{ $t('common.roles.task_user') }}</option>
            <option value="executor">{{ $t('common.roles.executor') }}</option>
            <option value="auditor">{{ $t('common.roles.auditor') }}</option>
          </select>
        </div>

        <!-- Department -->
        <div class="form-group">
          <label>Department</label>
          <select v-model="form.department_id" required class="form-control">
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Projects -->
        <div class="form-group">
          <label>Projects</label>
          <div class="checkbox-group">
            <label v-for="proj in projects" :key="proj.id" class="checkbox-label">
              <input type="checkbox" :value="proj.id" v-model="form.projects" />
              {{ proj.name }}
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button 
            type="button" 
            @click="handleFreezeToggle" 
            :class="['btn', form.is_active ? 'btn-danger' : 'btn-success']" 
            style="margin-right: auto;"
          >
            {{ form.is_active ? $t('admin.freeze') : $t('admin.unfreeze') }}
          </button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
      
      <div v-if="error" class="error-msg">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin']
})

const { token } = useAuth()
const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()
const userId = route.params.id

const departments = ref<any[]>([])
const projects = ref<any[]>([])
const loadingInitial = ref(true)
const submitting = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  employee_id: '',
  photo_url: '',
  role: 'executor',
  department_id: '',
  projects: [] as number[],
  is_active: true
})

onMounted(async () => {
  try {
    const apiBase = (config.public.apiBase as string) || ''
    const [userRes, deptRes, projRes] = await Promise.all([
      $fetch<any>(`${apiBase}/api/admin/users/${userId}`, { headers: { Authorization: `Bearer ${token.value}` } }),
      $fetch(`${apiBase}/api/admin/departments`, { headers: { Authorization: `Bearer ${token.value}` } }),
      $fetch(`${apiBase}/api/admin/projects`, { headers: { Authorization: `Bearer ${token.value}` } })
    ])
    
    departments.value = deptRes as any[]
    projects.value = projRes as any[]
    
    // Populate form
    form.name = userRes.name
    form.email = userRes.email
    form.employee_id = userRes.employee_id || ''
    form.photo_url = userRes.photo_url || ''
    form.role = userRes.role
    form.department_id = userRes.department_id
    form.projects = userRes.projects.map((p: any) => p.id)
    form.is_active = userRes.is_active
    
  } catch (e) {
    console.error('Failed to load data', e)
    error.value = 'Failed to load user data'
  } finally {
    loadingInitial.value = false
  }
})

async function submitForm() {
  submitting.value = true
  error.value = ''
  
  const payload: any = { ...form }
  if (!payload.password) delete payload.password // Don't send empty password
  
  try {
    const apiBase = (config.public.apiBase as string) || ''
    await $fetch(`${apiBase}/api/admin/users/${userId}`, {
      method: 'PUT',
      headers: { Authorization: `Bearer ${token.value}` },
      body: payload
    })
    router.push('/admin/users')
  } catch (e: any) {
    error.value = e.data?.message || 'Failed to update user'
  } finally {
    submitting.value = false
  }
}

async function handleFreezeToggle() {
  const isFreezing = form.is_active
  const confirmMsg = isFreezing 
    ? $t('admin.freezeConfirm')
    : 'Are you sure you want to unfreeze this account?'
    
  if (confirm(confirmMsg)) {
    try {
      const apiBase = (config.public.apiBase as string) || ''
      if (isFreezing) {
        // Use DELETE to freeze (as implemented in backend)
        await $fetch(`${apiBase}/api/admin/users/${userId}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${token.value}` }
        })
        form.is_active = false
      } else {
        // Use PUT to unfreeze
        await $fetch(`${apiBase}/api/admin/users/${userId}`, {
          method: 'PUT',
          headers: { Authorization: `Bearer ${token.value}` },
          body: { is_active: true }
        })
        form.is_active = true
      }
    } catch (e) {
      alert('Action failed')
    }
  }
}
</script>

<style scoped>
/* Reuse styles from create.vue or common styles */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-title { font-size: 1.8rem; font-weight: 700; color: #2d3748; }
.form-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 800px; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4a5568; }
.form-control { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 1rem; }
.checkbox-group { display: flex; flex-wrap: wrap; gap: 1rem; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; }
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
.form-actions { margin-top: 2rem; display: flex; justify-content: flex-end; }
.btn { padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #edf2f7; color: #4a5568; }
.btn-danger { background: #feb2b2; color: #c53030; }
.btn-danger:hover { background: #fc8181; }
.btn-success { background: #68d391; color: white; }
.btn-success:hover { background: #48bb78; }
.error-msg { margin-top: 1rem; color: #e53e3e; background: #fff5f5; padding: 1rem; border-radius: 6px; }
.loading { font-size: 1.2rem; color: #666; }
</style>
