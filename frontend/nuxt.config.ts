// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  runtimeConfig: {
    public: {
      // API base URL if needed, but we use relative /api with proxy
    }
  },
  routeRules: {
    '/api/**': { proxy: 'http://127.0.0.1:8000/api/**' }
  }
})
