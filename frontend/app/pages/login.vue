<template>
  <div class="login-page">
    <div class="login-card">
      <h1>{{ $t('login.title') }}</h1>
      <p class="subtitle">{{ $t('login.subtitle') }}</p>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">{{ $t('login.email') }}</label>
          <input 
            id="email" 
            v-model="email" 
            type="email" 
            placeholder="admin@example.com" 
            required
            :disabled="loading"
          >
        </div>
        
        <div class="form-group">
          <label for="password">{{ $t('login.password') }}</label>
          <input 
            id="password" 
            v-model="password" 
            type="password" 
            placeholder="••••••••" 
            required
            :disabled="loading"
          >
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="login-btn" :disabled="loading">
          {{ loading ? $t('login.loggingIn') : $t('login.signIn') }}
        </button>
      </form>

      <div v-if="allowRegistration" class="register-link">
        <p>{{ $t('login.noAccount') }} <NuxtLink to="/register">{{ $t('login.register') }}</NuxtLink></p>
      </div>
      
      <div class="test-creds">
        <p>{{ $t('login.testCreds') }}</p>
        <code>admin@example.com / password</code>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

const config = useRuntimeConfig()
const allowRegistration = computed(() => config.public.allowRegistration === 'true' || config.public.allowRegistration === true)

const { loginWithEmail, isAuthenticated } = useAuth()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

// Redirect if already logged in
onMounted(() => {
  if (isAuthenticated.value) {
    navigateTo('/')
  }
})

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await loginWithEmail(email.value, password.value)
    navigateTo('/')
  } catch (err: any) {
    error.value = err.data?.message || 'Invalid email or password'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Inter', sans-serif;
}

.login-card {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 400px;
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

.login-form {
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

.login-btn {
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

.login-btn:hover {
  background: #5a67d8;
  transform: translateY(-2px);
}

.login-btn:disabled {
  background: #a0aec0;
  cursor: not-allowed;
  transform: none;
}

.register-link {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #4a5568;
}

.register-link a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.register-link a:hover {
  color: #5a67d8;
  text-decoration: underline;
}

.test-creds {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #edf2f7;
  font-size: 0.8rem;
  color: #a0aec0;
}

code {
  background: #f7fafc;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  color: #4a5568;
}
</style>
