<template>
  <div class="layout">
    <nav class="navbar">
      <div class="container">
        <div class="navbar-left">
          <NuxtLink to="/" class="brand">XTask</NuxtLink>
        </div>

        <div class="navbar-center">
          <template v-if="user">
            <NuxtLink to="/schedules" class="nav-link">{{ $t('common.schedules') }}</NuxtLink>
            <NuxtLink to="/tasks" class="nav-link">{{ $t('common.tasks') }}</NuxtLink>
            <NuxtLink v-if="user.role === 'admin'" to="/stats" class="nav-link">{{ $t('tasks.statistics') }}</NuxtLink>
          </template>
        </div>

        <div class="navbar-right">
          <!-- Language Switcher -->
          <div class="language-switcher">
            <span class="lang-label">{{ $t('common.language') }}:</span>
            <button 
              v-for="loc in locales" 
              :key="loc.code"
              @click="setLocale(loc.code)"
              :class="['lang-btn', { active: locale === loc.code }]"
              :title="loc.name"
            >
              <span class="lang-icon">{{ loc.icon }}</span>
              <span class="lang-name">{{ loc.name }}</span>
            </button>
          </div>

          <template v-if="user">
            <!-- User Menu -->
            <div class="user-menu-root" v-click-outside="closeDropdown">
              <button @click="toggleDropdown" class="avatar-trigger">
                <img :src="user.photo_url || 'https://ui-avatars.com/api/?name=' + user.name" :alt="user.name" class="avatar-small" />
              </button>

              <Transition name="dropdown">
                <div v-if="isDropdownOpen" class="dropdown-menu">
                  <div class="dropdown-header">
                    <img :src="user.photo_url || 'https://ui-avatars.com/api/?name=' + user.name" :alt="user.name" class="avatar-large" />
                    <div class="user-info">
                      <span class="name">{{ user.name }}</span>
                      <span class="role-title">{{ $t('common.roles.' + user.role) }}</span>
                    </div>
                  </div>
                  <div class="dropdown-divider"></div>
                  <div class="dropdown-items">
                    <NuxtLink to="/profile" class="dropdown-item" @click="closeDropdown">
                      <span class="icon">👤</span> {{ $t('common.profile') }}
                    </NuxtLink>                    
                    <NuxtLink v-if="can('view-admin')" to="/admin" class="dropdown-item" @click="closeDropdown">
                      <span class="icon">⚙️</span> {{ $t('common.management') }}
                    </NuxtLink>
                    <button @click="handleLogout" class="dropdown-item logout">
                      <span class="icon">🚪</span> {{ $t('common.logout') }}
                    </button>
                  </div>
                </div>
              </Transition>
            </div>
          </template>
          <template v-else>
            <NuxtLink to="/login" class="login-link">{{ $t('common.login') }}</NuxtLink>
          </template>
        </div>
      </div>
    </nav>
    
    <main class="container main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user, logout, can } = useAuth()
const { locale, locales, setLocale } = useI18n()

// User Dropdown
const isDropdownOpen = ref(false)
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}
const closeDropdown = () => {
  isDropdownOpen.value = false
}


const handleLogout = async () => {
  closeDropdown()
  await logout()
}

// Simple click-outside directive
const vClickOutside = {
  mounted(el: any, binding: any) {
    el.clickOutsideEvent = (event: Event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el: any) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>

<style scoped>
.navbar {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 0.75rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
}

.container {
  max-width: 1920px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.navbar .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-left, .navbar-right {
  flex: 1;
}

.navbar-left {
  display: flex;
  align-items: center;
}

.navbar-center {
  display: flex;
  align-items: center;
  gap: 2.5rem;
}

.navbar-right {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 1.5rem;
}

.brand {
  font-size: 1.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: none;
}

.nav-link {
  text-decoration: none;
  color: #666;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.2s;
  padding: 0.5rem 0;
  position: relative;
}

.nav-link:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: #764ba2;
  transition: width 0.3s;
}

.nav-link:hover, .nav-link.router-link-active {
  color: #764ba2;
}

.nav-link.router-link-active:after {
  width: 100%;
}


.login-link {
  background: #764ba2;
  color: white !important;
  padding: 8px 20px;
  border-radius: 8px;
  font-weight: 600;
}

/* Language Switcher */
.language-switcher {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-right: 1.5rem;
  background: #f8faff;
  padding: 4px;
  border-radius: 10px;
}

.lang-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  margin: 0 0.5rem;
  text-transform: uppercase;
}

.lang-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  transition: all 0.2s;
}

.lang-btn:hover {
  background: rgba(255, 255, 255, 0.8);
  color: #764ba2;
}

.lang-btn.active {
  background: white;
  color: #764ba2;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.lang-icon {
  font-size: 1.1rem;
}

@media (max-width: 640px) {
  .lang-name, .lang-label {
    display: none;
  }
  .language-switcher {
    margin-right: 0.5rem;
  }
}


/* User Menu */
.user-menu-root {
  position: relative;
  display: flex;
  align-items: center;
}

.avatar-trigger {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  border-radius: 50%;
  transition: transform 0.2s;
  border: 2px solid transparent;
}

.avatar-trigger:hover {
  transform: scale(1.05);
  border-color: #764ba2;
}

.avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 260px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  border: 1px solid rgba(0,0,0,0.05);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: linear-gradient(to bottom, #f8f9ff, white);
}

.avatar-large {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border: 3px solid white;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-info .name {
  font-weight: 700;
  font-size: 1.1rem;
  color: #111;
}

.user-info .title {
  font-size: 0.85rem;
  color: #666;
  margin-top: 2px;
}

.dropdown-divider {
  height: 1px;
  background: #eee;
}

.dropdown-items {
  padding: 0.5rem;
}

.dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 1rem;
  border: none;
  background: none;
  border-radius: 8px;
  color: #444;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  text-decoration: none;
  gap: 12px;
}

.dropdown-item:hover {
  background: #f5f7ff;
  color: #764ba2;
}

.dropdown-item.logout:hover {
  background: #fff5f5;
  color: #ff4d4f;
}

.icon {
  font-size: 1.2rem;
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease-out;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.main-content {
  padding-top: 2rem;
  padding-bottom: 4rem;
}
</style>
