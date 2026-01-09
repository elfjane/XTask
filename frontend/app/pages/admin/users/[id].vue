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
            <option value="user">User</option>
            <option value="admin">Admin</option>
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
          <button type="button" @click="handleDelete" class="btn btn-danger" style="margin-right: auto;">
            Delete (Freeze)
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
  role: 'user',
  department_id: '',
  projects: [] as number[]
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

async function handleDelete() {
  if (confirm('Are you sure you want to freeze this account? They will no longer be able to login.')) {
    try {
      const apiBase = (config.public.apiBase as string) || ''
      await $fetch(`${apiBase}/api/admin/users/${userId}`, {
        method: 'DELETE',
        headers: { Authorization: `Bearer ${token.value}` }
      })
      router.push('/admin/users')
    } catch (e) {
      alert('Failed to delete user')
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
.error-msg { margin-top: 1rem; color: #e53e3e; background: #fff5f5; padding: 1rem; border-radius: 6px; }
.loading { font-size: 1.2rem; color: #666; }
</style>
