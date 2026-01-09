<template>
  <div>Redirecting...</div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: ['auth', 'admin']
})

// Simple redirect to users
navigateTo('/admin/users')
</script>
