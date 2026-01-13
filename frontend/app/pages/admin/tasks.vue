<template>
  <div class="admin-page-container">
    <div class="page-header">
      <h1 class="page-title">{{ $t('admin.tasks') }}</h1>
    </div>

    <div class="import-section card">
      <div class="import-content">
        <div class="icon-wrapper">
          <span class="excel-icon">📁</span>
        </div>
        <h2>{{ $t('tasks.importExcel') }}</h2>
        <p class="description">
          批次匯入任務資料。請上傳符合格式的 Excel 檔案 (.xlsx, .xls)。
        </p>

        <div class="upload-area" :class="{ 'dragging': isDragging, 'loading': uploading }" 
             @dragover.prevent="isDragging = true" 
             @dragleave.prevent="isDragging = false" 
             @drop.prevent="handleDrop">
          
          <input type="file" ref="fileInput" class="hidden-input" accept=".xlsx, .xls" @change="handleFileChange" />
          
          <div v-if="!uploading" class="upload-prompt" @click="fileInput?.click()">
            <span class="upload-icon">📤</span>
            <span class="upload-text">{{ selectedFile ? selectedFile.name : $t('tasks.selectFile') }}</span>
          </div>
          
          <div v-else class="uploading-state">
            <div class="spinner"></div>
            <span>{{ $t('tasks.uploading') }}</span>
          </div>
        </div>

        <div v-if="selectedFile && !uploading" class="actions">
          <button @click="startImport" class="btn btn-primary btn-lg">
            {{ $t('tasks.import') }}
          </button>
          <button @click="selectedFile = null" class="btn btn-secondary">
            {{ $t('tasks.cancel') }}
          </button>
        </div>

        <div v-if="message" :class="['message-alert', messageType]">
          {{ message }}
        </div>
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
const i18n = useI18n()

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isDragging = ref(false)
const uploading = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0] as File
    message.value = ''
  }
}

function handleDrop(e: DragEvent) {
  isDragging.value = false
  if (e.dataTransfer && e.dataTransfer.files.length > 0) {
    selectedFile.value = e.dataTransfer.files[0] as File
    message.value = ''
  }
}

async function startImport() {
  if (!selectedFile.value) return

  uploading.value = true
  message.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const apiBase = (config.public.apiBase as string) || ''
    const response = await $fetch(`${apiBase}/api/tasks/import`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: formData
    })

    message.value = i18n.t('tasks.importSuccess', { count: (response as any).count })
    messageType.value = 'success'
    selectedFile.value = null
  } catch (e: any) {
    console.error(e)
    message.value = i18n.t('tasks.importFailed', { message: e.data?.message || e.message })
    messageType.value = 'error'
  } finally {
    uploading.value = false
  }
}
</script>

<style scoped>
.admin-page-container {
  padding-bottom: 3rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2d3748;
}

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 3rem;
  max-width: 600px;
  margin: 0 auto;
}

.import-content {
  text-align: center;
}

.icon-wrapper {
  font-size: 4rem;
  margin-bottom: 1rem;
}

h2 {
  font-size: 1.5rem;
  color: #1a202c;
  margin-bottom: 1rem;
}

.description {
  color: #718096;
  margin-bottom: 2rem;
}

.upload-area {
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  padding: 3rem 1rem;
  transition: all 0.3s;
  cursor: pointer;
  background: #f8fafc;
  margin-bottom: 2rem;
  position: relative;
}

.upload-area.dragging {
  border-color: #667eea;
  background: #ebf4ff;
}

.upload-area.loading {
  cursor: wait;
  opacity: 0.7;
}

.hidden-input {
  display: none;
}

.upload-prompt {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.upload-icon {
  font-size: 2.5rem;
}

.upload-text {
  font-weight: 500;
  color: #4a5568;
  word-break: break-all;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: transform 0.1s;
}

.btn:active {
  transform: scale(0.98);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-secondary {
  background: #edf2f7;
  color: #4a5568;
}

.btn-lg {
  padding: 0.75rem 2.5rem;
}

.message-alert {
  margin-top: 2rem;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
}

.message-alert.success {
  background: #f0fff4;
  color: #276749;
  border: 1px solid #c6f6d5;
}

.message-alert.error {
  background: #fff5f5;
  color: #9b2c2c;
  border: 1px solid #fed7d7;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
