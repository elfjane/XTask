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
            <img 
              :src="avatarUrl" 
              :alt="form.name" 
              class="avatar-large" 
            />
          </div>

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
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'

definePageMeta({
  middleware: 'auth'
})

const { t } = useI18n()
const { user, fetchMe, token } = useAuth()

const loading = ref(false)
const passwordLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const pwdSuccessMessage = ref('')
const pwdErrorMessage = ref('')

const form = reactive({
  name: user.value?.name || '',
  email: user.value?.email || ''
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const avatarUrl = computed(() => {
  return user.value?.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(form.name)}&background=random`
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
        await $fetch('/api/profile', {
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
        await $fetch('/api/profile', {
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
.profile-page {
  padding: 1rem 0;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #1a202c;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  background: #f8fafc;
  padding: 1.5rem;
  border-bottom: 1px solid #edf2f7;
}

.card-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #2d3748;
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

.avatar-large {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
}

.form-actions {
  margin-top: 2rem;
}

.btn-primary {
  width: 100%;
  padding: 0.8rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #5a67d8;
}

.btn-outline {
  width: 100%;
  padding: 0.8rem;
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #ebf4ff;
}

.btn-primary:disabled, .btn-outline:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.success-msg {
  color: #38a169;
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
}

.error-msg {
  color: #e53e3e;
  text-align: center;
  margin-top: 1rem;
  font-size: 0.9rem;
}
</style>
