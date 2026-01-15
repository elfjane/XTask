<template>
  <div class="toast-container">
    <TransitionGroup name="toast">
      <ToastMessage
        v-for="toast in toasts"
        :key="toast.id"
        :message="toast.message"
        :type="toast.type"
        @close="remove(toast.id)"
      />
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
const { toasts, remove } = useToast()
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 80px; /* Below navbar */
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  pointer-events: none; /* Allow clicking through container */
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
