<template>
  <div class="admin-layout">
    <header class="admin-header">
      <div class="container">
        <NuxtLink to="/" class="brand">XTask Admin</NuxtLink>
        <div class="actions">
          <NuxtLink to="/" class="back-link">Back to App</NuxtLink>
        </div>
      </div>
    </header>
    
    <div class="admin-body container">
      <aside class="sidebar">
        <nav class="side-nav">
          <NuxtLink v-if="can('view-users')" to="/admin/users" class="nav-item">
            <span class="icon">👥</span> {{ $t('admin.users') }}
          </NuxtLink>
          <NuxtLink v-if="can('view-admin')" to="/admin/projects" class="nav-item">
            <span class="icon">📁</span> {{ $t('admin.projects') }}
          </NuxtLink>
          <NuxtLink v-if="can('view-admin')" to="/admin/departments" class="nav-item">
            <span class="icon">🏢</span> {{ $t('admin.departments') }}
          </NuxtLink>
          <NuxtLink v-if="can('view-admin')" to="/admin/tasks" class="nav-item">
            <span class="icon">📊</span> {{ $t('admin.tasks') }}
          </NuxtLink>
        </nav>
      </aside>

      <main class="content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const { can } = useAuth()
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  background: #f7fafc;
}

.admin-header {
  background: white;
  border-bottom: 1px solid #edf2f7;
  padding: 1rem 0;
}

.container {
  max-width: 1920px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex; /* For header flex behavior, sidebar/main handled by grid below */
}

.admin-header .container {
  justify-content: space-between;
  align-items: center;
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

.back-link {
  color: #718096;
  text-decoration: none;
  font-size: 0.9rem;
}

.admin-body {
  display: flex;
  padding-top: 2rem;
  gap: 2rem;
  align-items: flex-start; /* Important to keep sidebar from stretching weirdly if content is short */
}

.sidebar {
  width: 250px;
  flex-shrink: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  padding: 1rem;
  position: sticky;
  top: 2rem;
}

.side-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: #4a5568;
  border-radius: 8px;
  transition: all 0.2s;
  font-weight: 500;
  gap: 0.75rem;
}

.nav-item:hover {
  background: #edf2f7;
  color: #2d3748;
}

.nav-item.router-link-active {
  background: #ebf4ff;
  color: #667eea;
  font-weight: 600;
}

.content {
  flex: 1;
  background: white;
  min-height: 500px;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  padding: 2rem;
  overflow-x: auto; /* Handle wide tables */
}
</style>
