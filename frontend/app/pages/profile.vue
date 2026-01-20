<template>
  <div class="profile-page">
    <header class="page-header">
      <h1>{{ $t('profile.title') }}</h1>
    </header>

    <div class="content-grid">
      <!-- Personal Info Card -->
      <div class="card info-card">
        <div class="card-header">
          <h2>{{ $t('profile.personalInfo') }}</h2>
        </div>
        <div class="card-body">
          <div class="avatar-section">
            <div class="avatar-container">
              <img 
                :src="avatarUrl" 
                :alt="form.name" 
                class="avatar-large"
                width="140"
                height="140"
              />
              <div class="avatar-overlay" @click="triggerFileInput">
                <span>{{ $t('profile.uploadAvatar') }}</span>
              </div>
            </div>
            <input 
              ref="fileInput"
              type="file" 
              accept="image/*" 
              style="display: none" 
              @change="onFileChange"
            />
          </div>

          <!-- Cropping Modal -->
          <BaseModal v-model="showCropModal" :title="$t('profile.cropAvatar')" class="crop-modal">
            <div class="cropper-wrapper">
              <Cropper
                ref="cropperRef"
                class="cropper"
                :src="cropImage"
                :stencil-props="{
                  aspectRatio: 1/1
                }"
              />
            </div>
            <template #footer>
              <button @click="showCropModal = false" class="btn-secondary">{{ $t('common.cancel') }}</button>
              <button @click="handleCropAndUpload" class="btn-primary" :disabled="avatarUpdating">
                {{ avatarUpdating ? $t('profile.updating') : $t('profile.updateInfo') }}
              </button>
            </template>
          </BaseModal>

          <form @submit.prevent="handleUpdateInfo" class="profile-form">
            <div class="form-group">
              <label for="name">{{ $t('register.name') }}</label>
              <input 
                id="name"
                v-model="form.name" 
                type="text" 
                class="form-control"
                required
              >
            </div>

            <div class="form-group">
              <label for="email">{{ $t('register.email') }}</label>
              <input 
                id="email"
                v-model="form.email" 
                type="email" 
                class="form-control"
                required
              >
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? $t('profile.updating') : $t('profile.updateInfo') }}
              </button>
            </div>

            <p v-if="successMessage" class="success-msg">{{ successMessage }}</p>
            <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
          </form>
        </div>
      </div>

      <!-- Change Password Card -->
      <div class="card password-card">
        <div class="card-header">
          <h2>{{ $t('profile.changePassword') }}</h2>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleChangePassword" class="profile-form">
            <div class="form-group">
              <label for="current_password">{{ $t('profile.currentPassword') }}</label>
              <input 
                id="current_password"
                v-model="passwordForm.current_password" 
                type="password" 
                class="form-control"
                required
              >
            </div>

            <div class="form-group">
              <label for="new_password">{{ $t('profile.newPassword') }}</label>
              <input 
                id="new_password"
                v-model="passwordForm.password" 
                type="password" 
                class="form-control"
                required
                minlength="8"
              >
            </div>

            <div class="form-group">
              <label for="confirm_new_password">{{ $t('profile.confirmNewPassword') }}</label>
              <input 
                id="confirm_new_password"
                v-model="passwordForm.password_confirmation" 
                type="password" 
                class="form-control"
                required
                minlength="8"
              >
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-outline" :disabled="passwordLoading">
                {{ passwordLoading ? $t('profile.updating') : $t('profile.updatePassword') }}
              </button>
            </div>
             <p v-if="pwdSuccessMessage" class="success-msg">{{ pwdSuccessMessage }}</p>
            <p v-if="pwdErrorMessage" class="error-msg">{{ pwdErrorMessage }}</p>
          </form>
        </div>
      </div>

      <!-- Language Settings Card -->
      <div class="card language-card">
        <div class="card-header">
          <h2>{{ $t('common.language') || 'Language' }}</h2>
        </div>
        <div class="card-body">
          <div class="language-options">
            <button 
              v-for="loc in locales" 
              :key="loc.code" 
              @click="setLocale(loc.code)"
              class="lang-btn"
              :class="{ active: locale === loc.code }"
            >
              <span class="flag">{{ loc.icon }}</span>
              <span class="name">{{ loc.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import 'vue-advanced-cropper/dist/theme.classic.css'

definePageMeta({
  middleware: 'auth'
})

const { t, locale, locales, setLocale } = useI18n()
const { user, fetchMe, token } = useAuth()
const { getAvatarUrl } = useAvatar()

const avatarUrl = computed(() => getAvatarUrl(user.value))

const config = useRuntimeConfig()
const apiBase = (config.public.apiBase as string) || ''


const loading = ref(false)
const passwordLoading = ref(false)
const avatarUpdating = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const pwdSuccessMessage = ref('')
const pwdErrorMessage = ref('')

const fileInput = ref<HTMLInputElement | null>(null)
const cropperRef = ref<any>(null)
const showCropModal = ref(false)
const cropImage = ref<string | null>(null)

const triggerFileInput = () => {
    fileInput.value?.click()
}

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files && target.files[0]) {
        const reader = new FileReader()
        reader.onload = (event) => {
            cropImage.value = event.target?.result as string
            showCropModal.value = true
        }
        reader.readAsDataURL(target.files[0])
    }
}

const handleCropAndUpload = async () => {
    const { canvas } = cropperRef.value.getResult()
    if (!canvas) return

    avatarUpdating.value = true
    try {
        // Resize to 512x512
        const resizedCanvas = document.createElement('canvas')
        resizedCanvas.width = 512
        resizedCanvas.height = 512
        const ctx = resizedCanvas.getContext('2d')
        if (ctx) {
            ctx.drawImage(canvas, 0, 0, 512, 512)
        }

        const blob = await new Promise<Blob | null>(resolve => resizedCanvas.toBlob(resolve, 'image/jpeg', 0.9))
        if (!blob) throw new Error('Failed to create blob')

        const formData = new FormData()
        formData.append('avatar', blob, 'avatar.jpg')

        await $fetch(`${apiBase}/api/profile/avatar`, {
            method: 'POST',
            body: formData,
            headers: { Authorization: `Bearer ${token.value}` }
        })

        await fetchMe() // Refresh user data to show new avatar
        showCropModal.value = false
        successMessage.value = t('profile.avatarSuccess') || 'Avatar updated successfully'
    } catch (err: any) {
        errorMessage.value = err.data?.message || 'Avatar upload failed'
    } finally {
        avatarUpdating.value = false
    }
}

const form = reactive({
  name: user.value?.name || '',
  email: user.value?.email || ''
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Watch user changes to update form if it loads late
watch(user, (newUser) => {
  if (newUser) {
    if (!form.name) form.name = newUser.name
    if (!form.email) form.email = newUser.email
  }
})

const handleUpdateInfo = async () => {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''
    try {
        await $fetch(`${apiBase}/api/profile`, {
            method: 'PUT',
            body: {
                name: form.name,
                email: form.email
            },
            headers: { Authorization: `Bearer ${token.value}` }
        })
        await fetchMe() // Refresh user data
        successMessage.value = t('profile.successMessage')
    } catch (err: any) {
        if (err.data?.errors) {
            const errors = err.data.errors
            errorMessage.value = Object.values(errors).flat().join(', ')
        } else {
            errorMessage.value = err.data?.message || 'Update failed'
        }
    } finally {
        loading.value = false
    }
}

const handleChangePassword = async () => {
    if (passwordForm.password !== passwordForm.password_confirmation) {
        pwdErrorMessage.value = 'Passwords do not match'
        return
    }

    passwordLoading.value = true
    pwdSuccessMessage.value = ''
    pwdErrorMessage.value = ''
    
    try {
        await $fetch(`${apiBase}/api/profile`, {
            method: 'PUT',
            body: {
                current_password: passwordForm.current_password,
                password: passwordForm.password,
                password_confirmation: passwordForm.password_confirmation
            },
            headers: { Authorization: `Bearer ${token.value}` }
        })
        
        // Clear password fields
        passwordForm.current_password = ''
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        
        pwdSuccessMessage.value = t('profile.successMessage')
    } catch (err: any) {
        if (err.data?.errors) {
            const errors = err.data.errors
            pwdErrorMessage.value = Object.values(errors).flat().join(', ')
        } else {
            pwdErrorMessage.value = err.data?.message || 'Update failed'
        }
    } finally {
        passwordLoading.value = false
    }
}
</script>

<style scoped>
.page-container {
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2.5rem;
  text-align: center;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  letter-spacing: -0.025em;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2rem;
}

.card {
  background: var(--surface-primary);
  border-radius: 24px;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(to right, var(--bg-primary), white);
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border-color);
}

.card-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--text-primary);
  font-weight: 700;
}

.card-body {
  padding: 2rem;
}

.avatar-section {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.avatar-container {
  position: relative;
  width: 140px;
  height: 140px;
}

.avatar-large {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid white;
  box-shadow: var(--shadow-md);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 700;
  text-align: center;
  padding: 20px;
}

.avatar-container:hover .avatar-overlay {
  opacity: 1;
}

.avatar-container:hover .avatar-large {
  transform: scale(1.05);
}

.cropper-wrapper {
  width: 100%;
  height: 400px;
  background: #0f172a;
  border-radius: 12px;
  overflow: hidden;
}

.btn-secondary {
  padding: 0.8rem 1.5rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  color: var(--text-secondary);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: white;
  border-color: var(--brand-primary);
  color: var(--brand-primary);
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #4a5568;
  font-weight: 600;
  font-size: 0.9rem;
}

.form-actions {
  margin-top: 2rem;
}

.btn-primary {
  width: 100%;
  padding: 1rem;
  background: var(--brand-primary);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
}

.btn-outline {
  width: 100%;
  padding: 0.8rem;
  background: transparent;
  border: 2px solid var(--brand-primary);
  color: var(--brand-primary);
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: var(--bg-secondary);
}

.btn-primary:disabled, .btn-outline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.success-msg {
  color: var(--accent-green);
  background: #f0fdf4;
  padding: 0.75rem;
  border-radius: 10px;
  border: 1px solid #dcfce7;
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.error-msg {
  color: var(--accent-red);
  background: #fef2f2;
  padding: 0.75rem;
  border-radius: 10px;
  border: 1px solid #fee2e2;
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.language-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.lang-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border: 2px solid var(--border-color);
  border-radius: 16px;
  background: var(--surface-primary);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: left;
}

.lang-btn:hover {
  border-color: var(--brand-primary);
  background: var(--bg-primary);
  transform: translateX(4px);
}

.lang-btn.active {
  border-color: var(--brand-primary);
  background: var(--bg-secondary);
  box-shadow: var(--shadow-md);
}

.lang-btn .flag {
  font-size: 1.75rem;
}

.lang-btn .name {
  font-weight: 700;
  color: var(--text-primary);
  font-size: 1rem;
}

.lang-btn.active .name {
  color: var(--brand-primary);
}

/* Custom form-control for cases where BaseInput isn't used */
.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--surface-primary);
  transition: all 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
</style>
