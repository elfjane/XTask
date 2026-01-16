<template>
  <div 
    class="toast-message" 
    :class="type" 
    role="alert"
    @click="$emit('close')"
  >
    <div class="toast-icon">
      <span v-if="type === 'success'">✓</span>
      <span v-else-if="type === 'error'">✕</span>
      <span v-else-if="type === 'warning'">⚠</span>
      <span v-else>ℹ</span>
    </div>
    <div class="toast-content">{{ message }}</div>
    <button class="toast-close" @click.stop="$emit('close')">×</button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  message: string
  type: 'success' | 'error' | 'info' | 'warning'
}>()

defineEmits<{
  (e: 'close'): void
}>()
</script>

<style scoped>
.toast-message {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  margin-bottom: 8px;
  min-width: 300px;
  max-width: 450px;
  pointer-events: auto;
  cursor: pointer;
  animation: slideIn 0.3s ease-out;
  border-left: 4px solid #ccc;
  overflow: hidden;
}

.toast-message.success { border-left-color: #48bb78; }
.toast-message.error { border-left-color: #f56565; }
.toast-message.warning { border-left-color: #ed8936; }
.toast-message.info { border-left-color: #4299e1; }

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-weight: bold;
  color: white;
  flex-shrink: 0;
}

.success .toast-icon { background: #48bb78; }
.error .toast-icon { background: #f56565; }
.warning .toast-icon { background: #ed8936; }
.info .toast-icon { background: #4299e1; }

.toast-content {
  flex: 1;
  font-size: 0.95rem;
  color: #2d3748;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #a0aec0;
  cursor: pointer;
  padding: 0 4px;
  transition: color 0.2s;
}

.toast-close:hover {
  color: #4a5568;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
