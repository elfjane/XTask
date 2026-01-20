<template>
  <aside :class="['sidebar', { open: isOpen }]">
    <!-- Mobile Close Button -->
    <button v-if="isMobile" @click="closeSidebar" class="close-btn">✕</button>
    
    <nav class="sidebar-nav">
      <!-- Schedules Section -->
      <div class="menu-section">
        <div class="section-title">
          <span class="section-icon">📅</span>
          {{ $t('sidebar.schedules') }}
        </div>
        <NuxtLink 
          to="/schedules" 
          class="menu-item"
          @click="handleNavigation"
        >
          <span class="menu-item-icon">📋</span>
          {{ $t('sidebar.schedules') }}
        </NuxtLink>
      </div>

      <!-- Tasks Section -->
      <div class="menu-section">
        <div class="section-title">
          <span class="section-icon">✓</span>
          {{ $t('sidebar.tasks') }}
        </div>
        <NuxtLink 
          to="/tasks" 
          class="menu-item"
          @click="handleNavigation"
        >
          <span class="menu-item-icon">📝</span>
          {{ $t('sidebar.taskList') }}
        </NuxtLink>
        <NuxtLink 
          v-if="can('review-tasks')"
          to="/tasks?mode=review" 
          class="menu-item"
          @click="handleNavigation"
        >
          <span class="menu-item-icon">👁️</span>
          {{ $t('sidebar.tasksUnderReview') }}
        </NuxtLink>
        <NuxtLink 
          to="/tasks?mode=completed" 
          class="menu-item"
          @click="handleNavigation"
        >
          <span class="menu-item-icon">✔️</span>
          {{ $t('sidebar.completedTasks') }}
        </NuxtLink>
        <NuxtLink 
          v-if="user?.role === 'admin'"
          to="/stats" 
          class="menu-item"
          @click="handleNavigation"
        >
          <span class="menu-item-icon">📊</span>
          {{ $t('sidebar.taskStatistics') }}
        </NuxtLink>
      </div>
    </nav>
  </aside>
</template>

<script setup lang="ts">
const { user, can } = useAuth()
const route = useRoute()

const props = defineProps<{
  isOpen?: boolean
  isMobile?: boolean
}>()

const emit = defineEmits<{
  close: []
}>()

const closeSidebar = () => {
  emit('close')
}

const handleNavigation = () => {
  if (props.isMobile) {
    closeSidebar()
  }
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: var(--surface-primary);
  border-right: 1px solid var(--border-color);
  overflow-y: auto;
  position: sticky;
  top: 60px;
  height: calc(100vh - 60px);
  flex-shrink: 0;
}

.close-btn {
  display: none;
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--text-secondary);
  padding: 0.25rem 0.5rem;
  line-height: 1;
  z-index: 10;
}

.close-btn:hover {
  color: var(--text-primary);
}

.sidebar-nav {
  padding: 1rem 0;
}

.menu-section {
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border-color);
}

.menu-section:last-child {
  border-bottom: none;
}

.section-title {
  padding: 0.75rem 1.5rem;
  font-weight: 700;
  color: var(--text-muted);
  font-size: 0.75rem;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  letter-spacing: 0.05em;
}

.section-icon {
  font-size: 1rem;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  font-weight: 500;
  position: relative;
}

.menu-item:hover {
  background: var(--bg-primary);
  color: var(--brand-primary);
}

.menu-item.router-link-active {
  background: var(--bg-secondary);
  color: var(--brand-primary);
  border-left-color: var(--brand-primary);
  font-weight: 600;
}

.menu-item-icon {
  width: 20px;
  margin-right: 0.75rem;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
  .sidebar {
    position: fixed;
    left: -260px;
    transition: left 0.3s ease;
    z-index: 1000;
    top: 0;
    height: 100vh;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  }
  
  .sidebar.open {
    left: 0;
  }

  .close-btn {
    display: block;
  }

  .sidebar-nav {
    padding-top: 3rem;
  }
}

/* Dark mode adjustments */
.dark-mode .sidebar {
  background: var(--surface-primary);
}

.dark-mode .close-btn {
  color: var(--text-secondary);
}

.dark-mode .close-btn:hover {
  color: var(--text-primary);
}
</style>
