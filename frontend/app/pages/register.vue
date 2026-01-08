<template>
  <div class="register-card">
    <h1>{{ $t('register.title') }}</h1>
    <p class="subtitle">{{ $t('register.subtitle') }}</p>
    
    <div v-if="!allowRegistration" class="warning-message">
      {{ $t('register.registrationDisabled') }}
    </div>

    <form v-else @submit.prevent="handleRegister" class="register-form">
      <div class="form-group">
        <label for="name">{{ $t('register.name') }}</label>
        <input 
          id="name" 
          v-model="name" 
          type="text" 
          :placeholder="$t('register.name')"
          required
          :disabled="loading"
        >
      </div>

      <div class="form-group">
        <label for="email">{{ $t('register.email') }}</label>
        <input 
          id="email" 
          v-model="email" 
          type="email" 
          placeholder="your@email.com" 
          required
          :disabled="loading"
        >
      </div>
      
      <div class="form-group">
        <label for="password">{{ $t('register.password') }}</label>
        <input 
          id="password" 
          v-model="password" 
          type="password" 
          placeholder="••••••••" 
          required
          minlength="8"
          :disabled="loading"
        >
      </div>

      <div class="form-group">
        <label for="password_confirmation">{{ $t('register.confirmPassword') }}</label>
        <input 
          id="password_confirmation" 
          v-model="passwordConfirmation" 
          type="password" 
          placeholder="••••••••" 
          required
          minlength="8"
          :disabled="loading"
        >
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <button type="submit" class="register-btn" :disabled="loading">
        {{ loading ? $t('register.registering') : $t('register.signUp') }}
      </button>
    </form>

    <div class="login-link">
      <p>{{ $t('register.haveAccount') }} <NuxtLink to="/login">{{ $t('register.login') }}</NuxtLink></p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth'
})

const config = useRuntimeConfig()
const allowRegistration = computed(() => String(config.public.allowRegistration) === 'true')

const { isAuthenticated, register } = useAuth()
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref('')

// Redirect if already logged in
onMounted(() => {
  if (isAuthenticated.value) {
    navigateTo('/')
  }
})

const handleRegister = async () => {
  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true
  error.value = ''
  
  try {
    await register(name.value, email.value, password.value, passwordConfirmation.value)
    navigateTo('/')
  } catch (err: any) {
    if (err.status === 403) {
      error.value = err.data?.message || $t('login.registrationDisabled')
    } else if (err.data?.errors) {
      // Handle validation errors
      const errors = err.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else {
      error.value = err.data?.message || 'Registration failed'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-card {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 450px;
  text-align: center;
}

h1 {
  margin: 0 0 0.5rem 0;
  color: #1a202c;
  font-size: 2rem;
  font-weight: 800;
}

.subtitle {
  color: #718096;
  margin-bottom: 2rem;
  font-size: 0.95rem;
}

.register-form {
  text-align: left;
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

input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #667eea;
}

.error-message {
  background: #fff5f5;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  font-size: 0.85rem;
  text-align: center;
}

.warning-message {
  background: #fffbeb;
  color: #b45309;
  padding: 1rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  text-align: center;
  border: 2px solid #fcd34d;
}

.register-btn {
  width: 100%;
  padding: 1rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s, background 0.2s;
}

.register-btn:hover {
  background: #5a67d8;
  transform: translateY(-2px);
}

.register-btn:disabled {
  background: #a0aec0;
  cursor: not-allowed;
  transform: none;
}

.login-link {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #4a5568;
}

.login-link a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.login-link a:hover {
  color: #5a67d8;
  text-decoration: underline;
}
</style>
