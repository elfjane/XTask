<template>
  <div class="admin-page-container">
    <div class="page-header">
      <h1 class="page-title">{{ $t('admin.departments') }}</h1>
      <button v-if="can('manage-departments')" @click="openModal()" class="btn btn-primary">{{ $t('admin.add') }}</button>
    </div>

    <div class="card">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th v-if="can('manage-departments')">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td>
            <td v-if="can('manage-departments')" class="actions-cell">
              <button @click="openModal(item)" class="btn-icon">✏️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <h2>{{ editingItem ? $t('admin.edit') : $t('admin.add') }}</h2>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" type="text" required class="form-control" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
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

const items = ref<any[]>([])
const showModal = ref(false)
const editingItem = ref<any>(null)
const form = reactive({ name: '' })

onMounted(() => fetchData())

async function fetchData() {
  try {
    const apiBase = (config.public.apiBase as string) || ''
    const data = await $fetch(`${apiBase}/api/admin/departments`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    items.value = data as any[]
  } catch (e) {
    console.error(e)
  }
}

function openModal(item: any = null) {
  editingItem.value = item
  form.name = item ? item.name : ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingItem.value = null
  form.name = ''
}

async function submitForm() {
  try {
    const apiBase = (config.public.apiBase as string) || ''
    const url = editingItem.value
      ? `${apiBase}/api/admin/departments/${editingItem.value.id}`
      : `${apiBase}/api/admin/departments`
    
    const method = editingItem.value ? 'PUT' : 'POST'

    await $fetch(url, {
      method,
      headers: { Authorization: `Bearer ${token.value}` },
      body: form
    })
    
    closeModal()
    fetchData()
  } catch (e) {
    alert('Failed to save')
  }
}
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-title { font-size: 1.8rem; font-weight: 700; color: #2d3748; }
.card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #edf2f7; }
.data-table th { background: #f7fafc; font-weight: 600; color: #4a5568; font-size: 0.9rem; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; }
.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #edf2f7; color: #4a5568; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1.2rem; }

/* Modal */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 2rem; border-radius: 8px; width: 400px; max-width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.modal h2 { margin-top: 0; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-control { width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 1rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; }
</style>
