<template>
  <div :class="['skeleton', type]" :style="style"></div>
</template>

<script setup lang="ts">
const props = defineProps<{
  type?: 'text' | 'image' | 'button' | 'table-row' | 'card'
  width?: string
  height?: string
  borderRadius?: string
}>()

const style = computed(() => ({
  width: props.width || '100%',
  height: props.height || (props.type === 'text' ? '1em' : '20px'),
  borderRadius: props.borderRadius || (props.type === 'image' || props.type === 'button' ? '8px' : '4px')
}))
</script>

<style scoped>
.skeleton {
  background: linear-gradient(
    90deg,
    var(--bg-secondary) 25%,
    var(--border-color) 50%,
    var(--bg-secondary) 75%
  );
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

.skeleton.text {
  margin: 0.5em 0;
}

.skeleton.table-row {
  height: 48px;
  margin-bottom: 1px;
}

.skeleton.card {
  height: 200px;
  border-radius: 12px;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Dark mode adjustments */
.dark-mode .skeleton {
  background: linear-gradient(
    90deg,
    #2a2a2a 25%,
    #3a3a3a 50%,
    #2a2a2a 75%
  );
  background-size: 200% 100%;
}
</style>
