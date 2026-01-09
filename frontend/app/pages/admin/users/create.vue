<template>
  <div class="page-header">
    <h1 class="page-title">{{ $t('admin.addUser') }}</h1>
    <NuxtLink to="/admin/users" class="btn btn-secondary">Back</NuxtLink>
  </div>

  <div class="card form-card">
    <form @submit.prevent="submitForm">
      <!-- Name -->
      <div class="form-group">
        <label>Name <span class="required">*</span></label>
        <input v-model="form.name" type="text" required class="form-control" />
      </div>

      <!-- Email -->
      <div class="form-group">
        <label>Account (Email) <span class="required">*</span></label>
        <input v-model="form.email" type="email" required class="form-control" />
      </div>

      <!-- Password -->
      <div class="form-group">
        <label>Password <span class="required">*</span></label>
        <input v-model="form.password" type="password" required class="form-control" minlength="8" />
      </div>

      <!-- Employee ID -->
      <div class="form-group">
        <label>Employee ID</label>
        <input v-model="form.employee_id" type="text" class="form-control" />
      </div>

      <!-- Photo URL -->
      <div class="form-group">
        <label>Photo URL</label>
        <input v-model="form.photo_url" type="text" class="form-control" placeholder="https://..." />
      </div>

      <!-- Role -->
      <div class="form-group">
        <label>Role <span class="required">*</span></label>
        <select v-model="form.role" required class="form-control">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <!-- Department -->
      <div class="form-group">
        <label>Department <span class="required">*</span></label>
        <select v-model="form.department_id" required class="form-control">
          <option value="" disabled>Select Department</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
      </div>

      <!-- Projects -->
      <div class="form-group">
        <label>Projects <span class="required">*</span></label>
        <div class="checkbox-group">
          <label v-for="proj in projects" :key="proj.id" class="checkbox-label">
            <input type="checkbox" :value="proj.id" v-model="form.projects" />
            {{ proj.name }}
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Saving...' : 'Create Account' }}
        </button>
      </div>
    </form>
    
    <div v-if="error" class="error-msg">
      {{ error }}
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
const router = useRouter()

const departments = ref<any[]>([])
const projects = ref<any[]>([])
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  employee_id: '',
  photo_url: '',
  role: 'user',
  department_id: '',
  projects: []
})

onMounted(async () => {
  try {
    const apiBase = (config.public.apiBase as string) || ''
    const [deptRes, projRes] = await Promise.all([
      $fetch(`${apiBase}/api/admin/departments`, { headers: { Authorization: `Bearer ${token.value}` } }),
      $fetch(`${apiBase}/api/admin/projects`, { headers: { Authorization: `Bearer ${token.value}` } })
    ])
    departments.value = deptRes as any[]
    projects.value = projRes as any[]
  } catch (e) {
    console.error('Failed to load options', e)
  }
})

async function submitForm() {
  loading.value = true
  error.value = ''
  
  try {
    const apiBase = (config.public.apiBase as string) || ''
    await $fetch(`${apiBase}/api/admin/users`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: form
    })
    router.push('/admin/users')
  } catch (e: any) {
    error.value = e.data?.message || 'Failed to create user'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2d3748;
}

.form-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  max-width: 800px;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #4a5568;
}

.required {
  color: #e53e3e;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-control:focus {
  border-color: #667eea;
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.form-actions {
  margin-top: 2rem;
  display: flex;
  justify-content: flex-end;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  text-decoration: none;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
}

.btn-secondary {
  background: #edf2f7;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.error-msg {
  margin-top: 1rem;
  color: #e53e3e;
  background: #fff5f5;
  padding: 1rem;
  border-radius: 6px;
}
</style>
