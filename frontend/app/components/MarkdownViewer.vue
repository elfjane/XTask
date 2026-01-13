<template>
  <div class="markdown-content" v-html="parsedContent"></div>
</template>

<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';

const props = defineProps<{
  content: string | null | undefined;
}>();

const parsedContent = computed(() => {
  if (!props.content) return '';
  // Force links to open in new tab
  const renderer = new marked.Renderer();
  renderer.link = ({ href, title, text }) => {
    return `<a href="${href}" title="${title || ''}" target="_blank" rel="noopener noreferrer">${text}</a>`;
  };
  
  return marked.parse(props.content, { renderer, async: false });
});
</script>

<style scoped>
.markdown-content :deep(p) {
  margin: 0 0 0.5rem 0;
}
.markdown-content :deep(p:last-child) {
  margin-bottom: 0;
}
.markdown-content :deep(a) {
  color: #764ba2;
  text-decoration: underline;
}
.markdown-content :deep(ul), .markdown-content :deep(ol) {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}
.markdown-content :deep(code) {
  background: #f3f4f6;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  font-family: monospace;
}
</style>
