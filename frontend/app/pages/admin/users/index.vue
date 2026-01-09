<template>
  <div class="admin-page-container">
    <div class="page-header">
      <h1 class="page-title">{{ $t('admin.users') }}</h1>
      <NuxtLink v-if="can('manage-users')" to="/admin/users/create" class="btn btn-primary">{{ $t('admin.addUser') }}</NuxtLink>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading users...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="fetchUsers" class="btn btn-secondary">Retry</button>
    </div>

    <div v-else class="card">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>{{ $t('common.name') }}</th>
            <th>Employee ID</th>
            <th>Photo</th>
            <th>Account</th>
            <th>Role</th>
            <th>Projects</th>
            <th>Department</th>
            <th v-if="can('manage-users')">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" :class="{ 'inactive-row': !user.is_active }">
            <td>{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.employee_id || '-' }}</td>
            <td>
              <img :src="user.photo_url || 'https://ui-avatars.com/api/?name=' + user.name" class="avatar-cell" />
            </td>
            <td>{{ user.email }}</td>
            <td>
              <span class="badge" :class="user.role === 'admin' ? 'badge-purple' : 'badge-gray'">
                {{ $t('common.roles.' + user.role) }}
              </span>
            </td>
            <td>
              <div class="tags">
                <span v-for="p in user.projects" :key="p.id" class="tag">{{ p.name }}</span>
              </div>
            </td>
            <td>{{ user.department?.name || '-' }}</td>
            <td v-if="can('manage-users')" class="actions-cell">
              <NuxtLink :to="'/admin/users/' + user.id" class="btn-icon" :title="$t('admin.edit')">✏️</NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin']
})

const { token, can } = useAuth()
const config = useRuntimeConfig()
const users = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  await fetchUsers()
})

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const apiBase = (config.public.apiBase as string) || ''
    const data = await $fetch(`${apiBase}/api/admin/users`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    users.value = data as any[]
  } catch (e: any) {
    console.error('Failed to fetch users', e)
    error.value = e.data?.message || 'Failed to fetch users'
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

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th, .data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #edf2f7;
}

.data-table th {
  background: #f7fafc;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.9rem;
}

.avatar-cell {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-purple {
  background: #e9d8fd;
  color: #6b46c1;
}

.badge-gray {
  background: #edf2f7;
  color: #4a5568;
}

.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.tag {
  background: #ebf8ff;
  color: #3182ce;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-size: 0.8rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.25rem;
}

.text-red {
  color: #e53e3e;
}

.inactive-row {
  opacity: 0.6;
  background: #f9f9f9;
}

.text-small {
  font-size: 0.85rem;
}

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state p {
  color: #e53e3e;
  margin-bottom: 1rem;
}
</style>
