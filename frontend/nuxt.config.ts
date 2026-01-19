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
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '',
      allowRegistration: process.env.NUXT_PUBLIC_ALLOW_REGISTRATION || 'false'
    }
  },
  routeRules: {
    '/api/**': { proxy: (process.env.NUXT_API_PROXY_TARGET || 'http://127.0.0.1:8111') + '/api/**' },
    '/storage/**': { proxy: (process.env.NUXT_API_PROXY_TARGET || 'http://127.0.0.1:8111') + '/storage/**' }
  },
  vite: {
    server: {
      allowedHosts: ['xtask.elfjane.com']
    }
  }
})
