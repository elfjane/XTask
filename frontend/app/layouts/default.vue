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
          <template v-if="user">
            <!-- User Menu -->
            <div class="user-menu-root" v-click-outside="closeDropdown">
              <button @click="toggleDropdown" class="avatar-trigger">
                <img :src="getAvatarUrl(user)" :alt="user.name" class="avatar-small" width="40" height="40" />
              </button>

              <Transition name="dropdown">
                <div v-if="isDropdownOpen" class="dropdown-menu">
                  <div class="dropdown-header">
                    <img :src="getAvatarUrl(user)" :alt="user.name" class="avatar-large" width="72" height="72" />
                    <div class="user-info">
                      <span class="name">{{ user.name }}</span>
                      <span class="role-title">{{ $t('common.roles.' + user.role) }}</span>
                    </div>
                  </div>
                  
                  <div class="dropdown-divider"></div>
                  
                  <!-- Preference Section -->
                  <div class="dropdown-items section-title">{{ $t('common.preferences') }}</div>
                  <div class="dropdown-items">
                    <!-- Theme Toggle as Dropdown Item -->
                    <button @click="toggleTheme" class="dropdown-item">
                      <span class="icon">{{ isDark ? '☀️' : '🌙' }}</span>
                      {{ isDark ? $t('common.lightMode') : $t('common.darkMode') }}
                    </button>
                    
                    <!-- Language Selector in Dropdown -->
                    <div class="dropdown-sub-items">
                      <div class="dropdown-item disabled">
                        <span class="icon">🌍</span> {{ $t('common.language') }} ➔
                      </div>
                      <button 
                        v-for="loc in locales" 
                        :key="loc.code"
                        @click="setLocale(loc.code)"
                        :class="['dropdown-item', 'sub', { active: locale === loc.code }]"
                      >
                        <span class="icon">{{ loc.icon }}</span>
                        {{ loc.name }}
                      </button>
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
            <div class="navbar-guest-actions">
              <!-- Quick Language for Guest -->
              <div class="mini-lang-switcher">
                <button 
                  v-for="loc in locales" 
                  :key="loc.code"
                  @click="setLocale(loc.code)"
                  :class="['mini-lang-btn', { active: locale === loc.code }]"
                >
                  {{ loc.icon }}
                </button>
              </div>
              <NuxtLink to="/login" class="login-link">{{ $t('common.login') }}</NuxtLink>
            </div>
          </template>
        </div>
      </div>
    </nav>
    
    <main class="container main-content">
      <slot />
    </main>
    
    <ClientOnly>
      <ToastContainer />
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
const { user, logout, can } = useAuth()
const { locale, locales, setLocale } = useI18n()
const { getAvatarUrl } = useAvatar()
const { isDark, toggleTheme } = useTheme()

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
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-color);
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
  background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: none;
  letter-spacing: -0.025em;
}

.nav-link {
  text-decoration: none;
  color: var(--text-secondary);
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
  background: var(--brand-primary);
  transition: width 0.3s;
}

.nav-link:hover, .nav-link.router-link-active {
  color: var(--brand-primary);
}

.nav-link.router-link-active:after {
  width: 100%;
}


.login-link {
  background: var(--brand-primary);
  color: white !important;
  padding: 8px 20px;
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
}

/* Language Switcher */
.navbar-guest-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.mini-lang-switcher {
  display: flex;
  gap: 0.5rem;
  background: var(--bg-secondary);
  padding: 4px;
  border-radius: 10px;
}

.mini-lang-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s;
  font-size: 1.1rem;
}

.mini-lang-btn.active {
  background: var(--surface-primary);
  box-shadow: var(--shadow-sm);
}

.mini-lang-btn:hover:not(.active) {
  background: rgba(0,0,0,0.05);
}

@media (max-width: 640px) {
  .navbar-center {
    display: none;
  }
}

.section-title {
  font-size: 0.7rem;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.75rem 1rem 0.25rem 1.25rem !important;
}

.dropdown-sub-items {
  padding-left: 0.5rem;
  display: flex;
  flex-direction: column;
}

.dropdown-item.sub {
  padding-left: 2.5rem;
  font-size: 0.85rem;
}

.dropdown-item.disabled {
  cursor: default;
  color: var(--text-muted);
  font-weight: 700;
  pointer-events: none;
}

.dropdown-item.active {
  border: 1px solid var(--brand-primary);
  background: var(--bg-primary);
  color: var(--brand-primary);
  box-shadow: var(--shadow-sm);
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
  border-color: var(--brand-primary);
}

.avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: var(--shadow-sm);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 260px;
  background: var(--surface-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: linear-gradient(to bottom, var(--bg-primary), white);
}

.avatar-large {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1rem;
  box-shadow: var(--shadow-md);
  border: 3px solid white;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-info .name {
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--text-primary);
}

.user-info .role-title {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-top: 2px;
}

.dropdown-divider {
  height: 1px;
  background: var(--border-color);
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
  border-radius: 10px;
  color: var(--text-secondary);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  text-decoration: none;
  gap: 12px;
}

.dropdown-item:hover {
  background: var(--bg-primary);
  color: var(--brand-primary);
}

.dropdown-item.logout:hover {
  background: #fef2f2;
  color: var(--accent-red);
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
