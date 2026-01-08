<template>
  <div class="form-group">
    <label v-if="label">{{ label }}</label>
    <div class="input-wrapper">
      <input
        v-if="type !== 'textarea' && type !== 'select'"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        :placeholder="placeholder"
        :class="{ error: error }"
      />
      <textarea
        v-else-if="type === 'textarea'"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        :placeholder="placeholder"
        :class="{ error: error }"
      ></textarea>
      <select
        v-else-if="type === 'select'"
        :value="modelValue"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        :class="{ error: error }"
      >
        <option v-for="opt in options" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </div>
    <span v-if="error" class="error-msg">{{ error }}</span>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string | number
  label?: string
  type?: string
  placeholder?: string
  error?: string
  options?: { label: string; value: string | number }[]
}>()

defineEmits(['update:modelValue'])
</script>

<style scoped>
.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.input-wrapper {
  position: relative;
}

input, textarea, select {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
  background: #fff;
}

input:focus, textarea:focus, select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

input.error, textarea.error, select.error {
  border-color: #ef4444;
}

.error-msg {
  display: block;
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

textarea {
  min-height: 100px;
  resize: vertical;
}
</style>
