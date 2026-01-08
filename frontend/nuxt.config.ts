// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  modules: ['@nuxtjs/i18n'],
  future: {
    compatibilityVersion: 4,
  },
  app: {
    pageTransition: { name: 'page', mode: 'out-in' },
    layoutTransition: { name: 'layout', mode: 'out-in' }
  },
  i18n: {
    locales: [
      { code: 'en', iso: 'en-US', file: 'en.json', name: 'English', icon: '🇺🇸' },
      { code: 'zh', iso: 'zh-TW', file: 'zh-TW.json', name: '繁體中文', icon: '🇹🇼' }
    ],
    defaultLocale: 'zh',
    langDir: 'locales',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      redirectOn: 'root',
    }
  },
  runtimeConfig: {
    public: {
      // API base URL if needed, but we use relative /api with proxy
    }
  },
  routeRules: {
    '/api/**': { proxy: 'http://127.0.0.1:8000/api/**' }
  }
})
