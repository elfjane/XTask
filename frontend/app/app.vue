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

// Fetch user data if token exists (e.g. on page refresh)
if (token.value) {
  await fetchMe()
}
</script>

<style>
body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  background-color: #f8f9fa;
  color: #212529;
}

/* Page Transitions */
.page-enter-active,
.page-leave-active {
  transition: all 0.3s ease-in-out;
}
.page-enter-from,
.page-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Layout Transitions */
.layout-enter-active,
.layout-leave-active {
  transition: all 0.3s;
}
.layout-enter-from,
.layout-leave-to {
  opacity: 0;
  filter: blur(5px);
}
</style>
