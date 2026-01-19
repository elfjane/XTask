<template>
  <div class="form-group" :class="{ 'is-required': required }">
    <label v-if="label">
      {{ label }}
      <span v-if="markdownHint" class="markdown-badge">Markdown</span>
      <span v-if="required" class="required-badge">{{ $t('validation.required_badge') || '必填' }}</span>
    </label>
    <div class="input-wrapper">
      <input
        v-if="type !== 'textarea' && type !== 'select'"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        :placeholder="placeholder"
        :class="{ error: error }"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
      />
      <textarea
        v-else-if="type === 'textarea'"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        :placeholder="placeholder"
        :class="{ error: error }"
        :required="required"
      ></textarea>
      <select
        v-else-if="type === 'select'"
        :value="modelValue"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        :class="{ error: error }"
        :required="required"
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
  modelValue: string | number | undefined | null
  label?: string
  type?: string
  placeholder?: string
  error?: string
  required?: boolean
  markdownHint?: boolean
  options?: { label: string; value: string | number }[]
  min?: string | number
  max?: string | number
  step?: string | number
}>()

defineEmits(['update:modelValue'])
</script>

<style scoped>
.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.required-badge {
  font-size: 0.7rem;
  background-color: #fef2f2;
  color: var(--accent-red);
  padding: 1px 6px;
  border-radius: 4px;
  font-weight: 600;
  border: 1px solid #fee2e2;
}

.markdown-badge {
  font-size: 0.7rem;
  background-color: #eef2ff;
  color: var(--brand-primary);
  padding: 1px 6px;
  border-radius: 4px;
  font-weight: 600;
  border: 1px solid #e0e7ff;
}

.input-wrapper {
  position: relative;
}

input, textarea, select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
  background: var(--surface-primary);
  color: var(--text-primary);
}

input::placeholder, textarea::placeholder {
  color: var(--text-muted);
}

input:focus, textarea:focus, select:focus {
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  background: white;
}

.is-required input:not(:focus):placeholder-shown,
.is-required textarea:not(:focus):placeholder-shown,
.is-required select:not(:focus):invalid {
  background-color: #fcfdfe;
}

input.error, textarea.error, select.error {
  border-color: var(--accent-red);
  background-color: #fef2f2;
}

.error-msg {
  display: block;
  font-size: 0.75rem;
  color: var(--accent-red);
  margin-top: 0.25rem;
  font-weight: 500;
}

textarea {
  min-height: 120px;
  resize: vertical;
  line-height: 1.6;
}
</style>
