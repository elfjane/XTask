<template>
  <span :class="['status-badge', statusClass, { 'clickable': clickable }]" @click="$emit('click')">
    {{ label }}
  </span>
</template>

<script setup lang="ts">
const props = defineProps<{
  status: string
  type: 'task' | 'review' | 'schedule'
  clickable?: boolean
}>()

defineEmits(['click'])

const { t } = useI18n()

const statusClass = computed(() => {
  return props.status.replace(/\s+/g, '-')
})

const label = computed(() => {
  if (props.type === 'review') {
    return t(`tasks.reviewStatus_${props.status}`)
  }
  if (props.type === 'schedule') {
    return t(`schedules.${props.status}`)
  }
  return t(`tasks.${props.status.replace(/\s+/g, '_')}`)
})
</script>

<style scoped>
.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.75rem;
  display: inline-block;
  min-width: 70px;
  text-align: center;
  white-space: nowrap;
}

.status-badge.clickable {
  cursor: pointer;
}

/* Task Statuses */
.status-badge.working { background: #e3f2fd; color: #1e88e5; }
.status-badge.finished { background: #e8f5e9; color: #43a047; }
.status-badge.in-progress { background: #fff3e0; color: #fb8c00; }
.status-badge.idle { background: #f3f4f6; color: #6b7280; }
.status-badge.waiting-qa { background: #f3e5f5; color: #8e24aa; }
.status-badge.miss { background: #ffebee; color: #e53935; }
.status-badge.cancelled { background: #e0e0e0; color: #616161; }
.status-badge.failed { background: #ffebee; color: #e53935; }

/* Review Statuses */
.status-badge.unsubmitted { background: #f3f4f6; color: #6b7280; }
.status-badge.submitted { background: #fff8e1; color: #ff8f00; }
.status-badge.approved { background: #e8f5e9; color: #43a047; }
/* failed is already defined above */

/* Schedule Statuses */
/* (Mostly overlap with tasks) */

.dark-mode .status-badge.working { background: #1e3a8a; color: #93c5fd; }
.dark-mode .status-badge.finished { background: #064e3b; color: #6ee7b7; }
.dark-mode .status-badge.in-progress { background: #7c2d12; color: #fdba74; }
.dark-mode .status-badge.idle { background: #374151; color: #9ca3af; }
.dark-mode .status-badge.waiting-qa { background: #4c1d95; color: #c084fc; }
.dark-mode .status-badge.miss { background: #7f1d1d; color: #fca5a5; }
.dark-mode .status-badge.cancelled { background: #1f2937; color: #6b7280; }
.dark-mode .status-badge.failed { background: #7f1d1d; color: #fca5a5; }

.dark-mode .status-badge.submitted { background: #78350f; color: #fcd34d; }
.dark-mode .status-badge.approved { background: #064e3b; color: #6ee7b7; }
</style>
