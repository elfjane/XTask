<template>
  <div>
    <NuxtLoadingIndicator color="repeating-linear-gradient(to right,#764ba2 0%,#667eea 100%)" :height="3" />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Main entry point
const { fetchMe, token } = useAuth()
const { initTheme } = useTheme()

// Fetch user data if token exists (e.g. on page refresh)
if (token.value) {
  await fetchMe()
}

onMounted(() => {
  initTheme()
})
</script>

<style>
:root {
  --bg-primary: #f8fafc;
  --bg-secondary: #f1f5f9;
  --surface-primary: #ffffff;
  --text-primary: #1e293b;
  --text-secondary: #475569;
  --text-muted: #94a3b8;
  --brand-primary: #6366f1;
  --brand-secondary: #818cf8;
  --accent-blue: #3b82f6;
  --accent-green: #10b981;
  --accent-yellow: #f59e0b;
  --accent-red: #ef4444;
  --border-color: #e2e8f0;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --radius-lg: 1rem;
  --nav-bg: rgba(255, 255, 255, 0.7);
}

.dark-mode {
  --bg-primary: #0f172a;
  --bg-secondary: #1e293b;
  --surface-primary: #1e293b;
  --text-primary: #f8fafc;
  --text-secondary: #cbd5e1;
  --text-muted: #64748b;
  --brand-primary: #818cf8;
  --brand-secondary: #6366f1;
  --border-color: #334155;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.5);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.5), 0 2px 4px -2px rgb(0 0 0 / 0.5);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.5), 0 4px 6px -4px rgb(0 0 0 / 0.5);
  --nav-bg: rgba(15, 23, 42, 0.7);
}

* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  background-color: var(--bg-primary);
  color: var(--text-primary);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

/* Global Avatar Styles to prevent flash on refresh */
.avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-large {
  /* This covers both the dropdown (72px) and profile (140px) 
     as they will be overridden by scoped styles, but provides a sensible default */
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-cell {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: var(--bg-primary);
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Transitions */
.page-enter-active,
.page-leave-active {
  transition: all 0.2s ease-in-out;
}
.page-enter-from,
.page-leave-to {
  opacity: 0;
  transform: translateY(5px);
}

.layout-enter-active,
.layout-leave-active {
  transition: all 0.3s;
}
.layout-enter-from,
.layout-leave-to {
  opacity: 0;
  filter: blur(4px);
}
</style>
