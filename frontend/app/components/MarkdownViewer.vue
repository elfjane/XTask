<template>
  <div class="markdown-content" v-html="parsedContent"></div>
</template>

<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';

const props = defineProps<{
  content: string | null | undefined;
}>();

const getIconForUrl = (url: string) => {
  if (!url) return null;
  const decodeUrl = url.toLowerCase();
  
  // PChomePay GitLab
  if (decodeUrl.includes('gitlab.pchomepay.com.tw')) {
    return 'https://www.google.com/s2/favicons?domain=gitlab.com&sz=32';
  }
  // Asana
  if (decodeUrl.includes('app.asana.com')) {
    return 'https://www.google.com/s2/favicons?domain=asana.com&sz=32';
  }
  // Google Sheets
  if (decodeUrl.includes('docs.google.com/spreadsheets')) {
    return 'https://ssl.gstatic.com/docs/spreadsheets/favicon3.ico';
  }
  // Google Slides
  if (decodeUrl.includes('docs.google.com/presentation')) {
    return 'https://ssl.gstatic.com/docs/presentations/images/favicon5.ico';
  }
  // Google Docs
  if (decodeUrl.includes('docs.google.com/document')) {
    return 'https://ssl.gstatic.com/docs/documents/images/kix-favicon7.ico';
  }
  // Slack
  if (decodeUrl.includes('slack.com')) {
    return 'https://www.google.com/s2/favicons?domain=slack.com&sz=32';
  }
  // GitHub
  if (decodeUrl.includes('github.com')) {
    return 'https://www.google.com/s2/favicons?domain=github.com&sz=32';
  }
  // YouTube
  if (decodeUrl.includes('youtube.com') || decodeUrl.includes('youtu.be')) {
    return 'https://www.google.com/s2/favicons?domain=youtube.com&sz=32';
  }

  // Fallback to general favicon for other links
  try {
    const domain = new URL(url).hostname;
    return `https://www.google.com/s2/favicons?domain=${domain}&sz=32`;
  } catch (e) {
    return null;
  }
};

const parsedContent = computed(() => {
  if (!props.content) return '';
  // Force links to open in new tab and add icons
  const renderer = new marked.Renderer();
  renderer.link = ({ href, title, text }) => {
    const iconUrl = getIconForUrl(href);
    const iconHtml = iconUrl ? `<img src="${iconUrl}" class="link-icon" aria-hidden="true" />` : '';
    return `<a href="${href}" title="${title || ''}" target="_blank" rel="noopener noreferrer" class="markdown-link">${iconHtml}<span class="link-text">${text}</span></a>`;
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
.markdown-content :deep(.markdown-link) {
  color: #764ba2;
  text-decoration: underline;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  vertical-align: middle;
}
.markdown-content :deep(.link-icon) {
  width: 16px;
  height: 16px;
  object-fit: contain;
  flex-shrink: 0;
}
.markdown-content :deep(.link-text) {
  line-height: 1.2;
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
